<?php
namespace App\Controllers;

use App\Config\Database;

class FinanceController extends Controller {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    private function getCurrentSession() {
        $stmt = $this->db->query("SELECT id FROM sessions WHERE is_current = TRUE LIMIT 1");
        return $stmt->fetchColumn();
    }

    private function getCurrentTerm() {
        $stmt = $this->db->query("SELECT id FROM terms WHERE is_current = TRUE LIMIT 1");
        return $stmt->fetchColumn();
    }

    private function classesHaveLevelColumn() {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'classes' AND column_name = 'level'
            LIMIT 1
        ");
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    // GET /api/finance/fees
    public function apiGetFees() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $sessionId = $this->getCurrentSession();
        $termId = $this->getCurrentTerm();
        if (!$sessionId || !$termId) {
            http_response_code(400);
            echo json_encode(['error' => 'No active session or term found']);
            return;
        }

        // Get all classes and their fee amount for current session and term
        $levelSelect = $this->classesHaveLevelColumn() ? 'c.level' : "'' AS level";
        $levelOrder = $this->classesHaveLevelColumn() ? 'c.level, c.name' : 'c.name';
        $stmt = $this->db->prepare("
            SELECT c.id as class_id, c.name as class_name, {$levelSelect},
                   COALESCE(f.amount, 0) as amount
            FROM classes c
            LEFT JOIN fee_structures f ON c.id = f.class_id AND f.session_id = ? AND f.term_id = ?
            ORDER BY {$levelOrder}
        ");
        $stmt->execute([$sessionId, $termId]);
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // POST /api/finance/fees
    public function apiSaveFee() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $classId = $data['class_id'] ?? null;
        $amount = $data['amount'] ?? 0;

        $sessionId = $this->getCurrentSession();
        $termId = $this->getCurrentTerm();

        if (!$classId || !is_numeric($amount) || (float)$amount < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid fee data']);
            return;
        }

        if (!$sessionId || !$termId) {
            http_response_code(400);
            echo json_encode(['error' => 'No active session or term found']);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM fee_structures WHERE session_id = ? AND term_id = ? AND class_id = ?");
        $stmt->execute([$sessionId, $termId, $classId]);
        $existingId = $stmt->fetchColumn();
        if ($existingId !== false) {
            $update = $this->db->prepare("UPDATE fee_structures SET amount = ? WHERE id = ?");
            $update->execute([(float)$amount, $existingId]);
        } else {
            $insert = $this->db->prepare("INSERT INTO fee_structures (session_id, term_id, class_id, amount) VALUES (?, ?, ?, ?)");
            $insert->execute([$sessionId, $termId, $classId, (float)$amount]);
        }
        
        echo json_encode(['success' => true]);
    }

    // GET /api/finance/pending-approvals
    public function apiGetPendingApprovals() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $stmt = $this->db->query("
            SELECT fp.*, s.first_name, s.surname, s.registration_number, c.name as class_name
            FROM fee_payments fp
            JOIN students s ON fp.student_id = s.id
            JOIN classes c ON s.current_class_id = c.id
            WHERE fp.status = 'pending'
            ORDER BY fp.payment_date ASC, fp.id ASC
        ");
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    // POST /api/finance/approvals/{id}
    public function apiProcessApproval($id) {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? ''; // 'approve' or 'reject'

        if (!in_array($action, ['approve', 'reject'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM fee_payments WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$payment) {
            http_response_code(404);
            echo json_encode(['error' => 'Payment not found or already processed']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            
            // Update the fee payment record
            $update = $this->db->prepare("UPDATE fee_payments SET status = ?, recorded_by = ? WHERE id = ?");
            $update->execute([$status, $_SESSION['user_id'], $id]);

            if ($action === 'approve') {
                // Update student_fees
                $feeStmt = $this->db->prepare("SELECT total_amount, paid_amount FROM student_fees WHERE student_id = ? AND session_id = ? AND term_id = ?");
                $feeStmt->execute([$payment['student_id'], $payment['session_id'], $payment['term_id']]);
                $fee = $feeStmt->fetch(\PDO::FETCH_ASSOC);

                if ($fee) {
                    $newPaid = $fee['paid_amount'] + $payment['amount'];
                    $feeStatus = 'unpaid';
                    if ($newPaid >= $fee['total_amount']) {
                        $feeStatus = 'paid';
                    } elseif ($newPaid > 0) {
                        $feeStatus = 'partial';
                    }

                    $updateFee = $this->db->prepare("UPDATE student_fees SET paid_amount = ?, status = ? WHERE student_id = ? AND session_id = ? AND term_id = ?");
                    $updateFee->execute([$newPaid, $feeStatus, $payment['student_id'], $payment['session_id'], $payment['term_id']]);
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
    }

    // GET /api/finance/bills
    public function apiGetBills() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $sessionId = $this->getCurrentSession();
        $termId = $this->getCurrentTerm();
        
        // Return students with their fee status
        $stmt = $this->db->prepare("
            SELECT s.id as student_id, s.first_name, s.surname, s.registration_number, c.name as class_name,
                   sf.id as fee_id, sf.total_amount, sf.paid_amount, sf.status
            FROM students s
            JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.session_id = ? AND sf.term_id = ?
            WHERE s.status = 'active'
            ORDER BY c.name, s.surname
        ");
        $stmt->execute([$sessionId, $termId]);
        
        // Get totals for dashboard
        $statsStmt = $this->db->prepare("
            SELECT 
                SUM(total_amount) as expected,
                SUM(paid_amount) as collected
            FROM student_fees WHERE session_id = ? AND term_id = ?
        ");
        $statsStmt->execute([$sessionId, $termId]);
        $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);
        
        echo json_encode([
            'students' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'stats' => [
                'expected' => $stats['expected'] ?? 0,
                'collected' => $stats['collected'] ?? 0
            ]
        ]);
    }

    // POST /api/finance/bills/generate
    public function apiGenerateBills() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }
        $sessionId = $this->getCurrentSession();
        $termId = $this->getCurrentTerm();
        
        // 1. Find all active students and their class tuition
        $stmt = $this->db->prepare("
            SELECT s.id as student_id, f.amount 
            FROM students s
            JOIN fee_structures f ON s.current_class_id = f.class_id 
            WHERE s.status = 'active' AND f.session_id = ? AND f.term_id = ?
        ");
        $stmt->execute([$sessionId, $termId]);
        $students = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $inserted = 0;
        foreach ($students as $st) {
            $existingStmt = $this->db->prepare("
                SELECT id
                FROM student_fees
                WHERE student_id = ? AND session_id = ? AND term_id = ?
            ");
            $existingStmt->execute([$st['student_id'], $sessionId, $termId]);
            $existingId = $existingStmt->fetchColumn();

            if ($existingId !== false) {
                $update = $this->db->prepare("
                    UPDATE student_fees
                    SET total_amount = ?,
                        status = CASE
                            WHEN paid_amount >= ? THEN 'paid'
                            WHEN paid_amount > 0 THEN 'partial'
                            ELSE 'unpaid'
                        END
                    WHERE id = ?
                ");
                $update->execute([$st['amount'], $st['amount'], $existingId]);
            } else {
                $insert = $this->db->prepare("
                    INSERT INTO student_fees (student_id, session_id, term_id, total_amount, paid_amount, status)
                    VALUES (?, ?, ?, ?, 0, 'unpaid')
                ");
                $insert->execute([$st['student_id'], $sessionId, $termId, $st['amount']]);
            }

            $inserted++;
        }
        
        echo json_encode(['success' => true, 'generated' => $inserted]);
    }

    // POST /api/finance/pay
    public function apiRecordPayment() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $feeId = $data['student_fee_id'] ?? null;
        $amount = (float)($data['amount'] ?? 0);
        $method = $data['payment_method'] ?? ''; // e.g., Cash, Bank Transfer
        $ref = $data['reference_number'] ?? ('MANUAL_' . time() . '_' . $feeId);
        $userId = $_SESSION['user_id'];

        if (!$feeId || $amount <= 0 || !$method) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $feeStmt = $this->db->prepare("
                SELECT student_id, session_id, term_id
                FROM student_fees
                WHERE id = ?
                FOR UPDATE
            ");
            $feeStmt->execute([$feeId]);
            $fee = $feeStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$fee) {
                $this->db->rollBack();
                http_response_code(404);
                echo json_encode(['error' => 'Student fee not found']);
                return;
            }

            // Insert Payment
            $stmt = $this->db->prepare("
                INSERT INTO fee_payments (student_id, session_id, term_id, amount, payment_date, payment_method, reference, status, recorded_by)
                VALUES (?, ?, ?, ?, CURRENT_DATE, ?, ?, 'approved', ?)
            ");
            $stmt->execute([$fee['student_id'], $fee['session_id'], $fee['term_id'], $amount, $method, $ref, $userId]);

            // Update student_fees
            $update = $this->db->prepare("
                UPDATE student_fees 
                SET paid_amount = paid_amount + ?,
                    status = CASE 
                        WHEN paid_amount + ? >= total_amount THEN 'paid'
                        ELSE 'partial'
                    END
                WHERE id = ?
            ");
            $update->execute([$amount, $amount, $feeId]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Payment failed']);
        }
    }
    
    // GET /api/finance/student/{id}
    public function apiGetStudentFinance($studentId) {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }
        $sessionId = $this->getCurrentSession();
        $termId = $this->getCurrentTerm();
        
        // Get Fee info
        $stmt = $this->db->prepare("
            SELECT f.*, s.first_name, s.surname, s.registration_number, c.name as class_name
            FROM student_fees f
            JOIN students s ON f.student_id = s.id
            JOIN classes c ON s.current_class_id = c.id
            WHERE f.student_id = ? AND f.session_id = ? AND f.term_id = ?
        ");
        $stmt->execute([$studentId, $sessionId, $termId]);
        $fee = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$fee) {
            http_response_code(404);
            echo json_encode(['error' => 'No bill generated for this session']);
            return;
        }
        
        // Get payments history
        $payStmt = $this->db->prepare("
            SELECT p.*, u.username as recorded_by_name
            FROM fee_payments p
            LEFT JOIN users u ON p.recorded_by = u.id
            WHERE p.student_id = ? AND p.session_id = ? AND p.term_id = ?
            ORDER BY p.payment_date DESC
        ");
        $payStmt->execute([$fee['student_id'], $fee['session_id'], $fee['term_id']]);
        $payments = $payStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo json_encode([
            'fee' => $fee,
            'payments' => $payments
        ]);
    }
}




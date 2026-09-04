<?php
namespace App\Controllers;

use App\Config\Database;

class StudentPortalController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    private function checkStudentAuth()
    {
        if (!isset($_SESSION['student_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public function apiGetDashboard()
    {
        $this->checkStudentAuth();
        $studentId = $_SESSION['student_id'];

        // Get student basic details and class
        $stmt = $this->db->prepare("
            SELECT s.first_name, s.surname, s.registration_number, c.name as class_name 
            FROM students s 
            LEFT JOIN classes c ON s.current_class_id = c.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch();

        // Get current active session
        $sessStmt = $this->db->query("SELECT id, name FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessStmt->fetch();
        $sessionId = $currentSession ? $currentSession->id : null;

        $termStmt = $this->db->query("SELECT id, name FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();
        $termId = $currentTerm ? $currentTerm->id : null;

        // Get fee info for current session
        $fee = null;
        if ($sessionId && $termId) {
            $feeStmt = $this->db->prepare("
                SELECT total_amount, paid_amount, status 
                FROM student_fees 
                WHERE student_id = ? AND session_id = ? AND term_id = ?
            ");
            $feeStmt->execute([$studentId, $sessionId, $termId]);
            $fee = $feeStmt->fetch();
        }



        $resStmt = $this->db->prepare("
            SELECT r.id, s.name as session_name, t.name as term_name, c.name as class_name, r.status
            FROM results r
            JOIN sessions s ON r.session_id = s.id
            JOIN terms t ON r.term_id = t.id
            JOIN classes c ON r.class_id = c.id
            WHERE r.student_id = ?
            ORDER BY s.id DESC, t.id DESC
        ");
        $resStmt->execute([$studentId]);
        $results = $resStmt->fetchAll(\PDO::FETCH_ASSOC);

        $schoolStmt = $this->db->query("SELECT bank_name, account_name, account_number FROM school_information LIMIT 1");
        $schoolInfo = $schoolStmt->fetch(\PDO::FETCH_ASSOC);

        $this->jsonResponse([
            'profile' => $profile,
            'current_session' => $currentSession,
            'fee' => $fee,
            'results' => $results,
            'school_info' => $schoolInfo
        ]);
    }

    public function apiGetResultDetails($resultId)
    {
        $this->checkStudentAuth();
        $studentId = $_SESSION['student_id'];

        // Ensure result belongs to this student
        $stmt = $this->db->prepare("
            SELECT r.*, s.name as session_name, t.name as term_name, c.name as class_name 
            FROM results r
            JOIN sessions s ON r.session_id = s.id
            JOIN terms t ON r.term_id = t.id
            JOIN classes c ON r.class_id = c.id
            WHERE r.id = ? AND r.student_id = ?
        ");
        $stmt->execute([$resultId, $studentId]);
        $result = $stmt->fetch();

        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'Result not found']);
            return;
        }

        // Get items
        $itemStmt = $this->db->prepare("
            SELECT ri.*, sub.name as subject_name 
            FROM result_items ri
            JOIN subjects sub ON ri.subject_id = sub.id
            WHERE ri.result_id = ?
        ");
        $itemStmt->execute([$resultId]);
        $items = $itemStmt->fetchAll();

        // Get class total students for position ranking info
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM results WHERE class_id = ? AND session_id = ? AND term_id = ?");
        $countStmt->execute([$result->class_id, $result->session_id, $result->term_id]);
        $totalStudents = $countStmt->fetchColumn();

        echo json_encode([
            'result' => $result,
            'items' => $items,
            'total_students' => $totalStudents
        ]);
    }

    public function apiSubmitPaymentReceipt()
    {
        $this->checkStudentAuth();
        $studentId = $_SESSION['student_id'];
        
        $sessStmt = $this->db->query("SELECT id FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessStmt->fetch(\PDO::FETCH_ASSOC);
        
        $termStmt = $this->db->query("SELECT id FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$currentSession || !$currentTerm) {
            http_response_code(400);
            echo json_encode(['error' => 'No active session or term found']);
            return;
        }
        
        $sessionId = $currentSession['id'];
        $termId = $currentTerm['id'];
        
        if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Please upload a valid receipt image']);
            return;
        }
        
        $uploadDir = __DIR__ . '/../../frontend/public/uploads/receipts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($fileExt, $allowedExts)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.']);
            return;
        }
        
        $fileName = 'receipt_' . $studentId . '_' . time() . '.' . $fileExt;
        $destPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $destPath)) {
            $receiptUrl = '/uploads/receipts/' . $fileName;
            $reference = 'MANUAL_' . time() . '_' . $studentId;
            $amount = $_POST['amount'] ?? 0;
            
            $stmt = $this->db->prepare("
                INSERT INTO fee_payments (student_id, session_id, term_id, amount, payment_date, payment_method, reference, status, receipt_url)
                VALUES (?, ?, ?, ?, CURDATE(), 'Bank Transfer', ?, 'pending', ?)
            ");
            $stmt->execute([$studentId, $sessionId, $termId, $amount, $reference, $receiptUrl]);
            
            echo json_encode(['success' => true, 'message' => 'Receipt submitted successfully. Awaiting admin approval.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload receipt.']);
        }
    }
}

<?php
namespace App\Controllers;

use App\Config\Database;

class AdmissionController extends Controller {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Public method: get classes
    public function apiClasses() {
        $this->restoreSessionFromToken();
        $stmt = $this->db->query("SELECT id, name, level FROM classes ORDER BY level, name");
        echo json_encode($stmt->fetchAll());
    }

    // Public method: Submit application
    public function apiApply() {
        $this->restoreSessionFromToken();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $req = ['first_name', 'surname', 'gender', 'desired_class_id', 'guardian_name', 'guardian_phone'];
        foreach ($req as $r) {
            if (empty($data[$r])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing field: $r"]);
                return;
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO admission_applications 
            (first_name, surname, middle_name, gender, date_of_birth, desired_class_id, guardian_name, guardian_phone, guardian_email, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['surname'],
            $data['middle_name'] ?? null,
            $data['gender'],
            $data['date_of_birth'] ?? null,
            $data['desired_class_id'],
            $data['guardian_name'],
            $data['guardian_phone'],
            $data['guardian_email'] ?? null,
            $data['address'] ?? null
        ]);

        echo json_encode(['success' => true]);
    }

    // Admin: Get applications
    public function apiIndex() {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $stmt = $this->db->query("
            SELECT a.*, c.name as desired_class_name 
            FROM admission_applications a
            LEFT JOIN classes c ON a.desired_class_id = c.id
            WHERE a.status = 'pending'
            ORDER BY a.application_date DESC
        ");
        echo json_encode($stmt->fetchAll());
    }

    // Admin: Approve
    public function apiApprove($id) {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        // User requested: include a class selection among the existing class for a student
        $classId = $data['class_id'] ?? null;
        
        if (!$classId) {
            http_response_code(400);
            echo json_encode(['error' => 'Class ID is required to approve']);
            return;
        }

        $this->db->beginTransaction();
        try {
            // Fetch application
            $stmt = $this->db->prepare("SELECT * FROM admission_applications WHERE id = ?");
            $stmt->execute([$id]);
            $app = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$app || $app['status'] !== 'pending') {
                throw new \Exception("Invalid or already processed application");
            }

            // Generate Sequential Reg Number (format: IAMS/year/0001)
            $year = date('Y');
            $prefix = "IAMS/$year/";
            $stmtLastReg = $this->db->prepare("SELECT registration_number FROM students WHERE registration_number LIKE :prefix ORDER BY registration_number DESC LIMIT 1");
            $stmtLastReg->execute([':prefix' => $prefix . '%']);
            $lastReg = $stmtLastReg->fetchColumn();
            
            if ($lastReg) {
                $parts = explode('/', $lastReg);
                $lastNum = intval(end($parts));
                $nextNum = $lastNum + 1;
            } else {
                $nextNum = 1;
            }
            $regNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Insert into students
            $insert = $this->db->prepare("
                INSERT INTO students (first_name, surname, middle_name, gender, dob, parent_name, phone, address, registration_number, current_class_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $insert->execute([
                $app['first_name'],
                $app['surname'],
                $app['middle_name'],
                $app['gender'],
                $app['date_of_birth'],
                $app['guardian_name'],
                $app['guardian_phone'],
                $app['address'],
                $regNo,
                $classId
            ]);

            // Update app status
            $update = $this->db->prepare("UPDATE admission_applications SET status = 'approved' WHERE id = ?");
            $update->execute([$id]);

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Admin: Reject
    public function apiReject($id) {
        $this->restoreSessionFromToken();
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $stmt = $this->db->prepare("UPDATE admission_applications SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
}



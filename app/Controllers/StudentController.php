<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class StudentController extends Controller
{
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head Teacher') {
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $class_id = $_GET['class_id'] ?? '';
        
        $query = "SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.current_class_id = c.id";
        $params = [];
        
        if (!empty($class_id)) {
            $query .= " WHERE s.current_class_id = :class_id";
            $params[':class_id'] = $class_id;
        }
        
        $query .= " ORDER BY c.name ASC, s.id DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        $classes = $db->query("SELECT id, name FROM classes ORDER BY name ASC")->fetchAll();

        $this->view('students/index', [
            'title' => 'Manage Students',
            'activeMenu' => 'students',
            'students' => $students,
            'classes' => $classes,
            'selected_class' => $class_id
        ]);
    }

    public function create()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $classes = $db->query("SELECT id, name FROM classes")->fetchAll();

        $this->view('students/create', [
            'title' => 'Register Student',
            'activeMenu' => 'students',
            'classes' => $classes
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        // Handle photo upload later or basic
        $registration_number = $_POST['registration_number'] ?? '';
        $surname = $_POST['surname'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $gender = $_POST['gender'] ?? 'Male';
        $dob = $_POST['dob'] ?? '';
        $current_class_id = $_POST['current_class_id'] ?? '';
        
        if (empty($surname) || empty($first_name) || empty($current_class_id)) {
            $this->redirect('/students/create');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO students (registration_number, surname, first_name, middle_name, gender, dob, parent_name, phone, address, current_class_id) VALUES (:registration_number, :surname, :first_name, :middle_name, :gender, :dob, :parent_name, :phone, :address, :current_class_id)");
            
            $stmt->execute([
                ':registration_number' => $registration_number,
                ':surname' => $surname,
                ':first_name' => $first_name,
                ':middle_name' => $_POST['middle_name'] ?? null,
                ':gender' => $gender,
                ':dob' => $dob,
                ':parent_name' => $_POST['parent_name'] ?? null,
                ':phone' => $_POST['phone'] ?? null,
                ':address' => $_POST['address'] ?? null,
                ':current_class_id' => $current_class_id
            ]);
            $this->redirect('/students');
        } catch (\Exception $e) {
            $this->redirect('/students/create');
        }
    }

    public function edit($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            $this->redirect('/students');
        }

        $classes = $db->query("SELECT id, name FROM classes")->fetchAll();

        $this->view('students/edit', [
            'title' => 'Edit Student',
            'activeMenu' => 'students',
            'student' => $student,
            'classes' => $classes
        ]);
    }

    public function update($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("UPDATE students SET registration_number = :reg, surname = :sur, first_name = :first, middle_name = :mid, gender = :gen, dob = :dob, parent_name = :parent, phone = :phone, address = :addr, current_class_id = :cls, status = :sts WHERE id = :id");
            
            $stmt->execute([
                ':reg' => $_POST['registration_number'] ?? null,
                ':sur' => $_POST['surname'] ?? '',
                ':first' => $_POST['first_name'] ?? '',
                ':mid' => $_POST['middle_name'] ?? null,
                ':gen' => $_POST['gender'] ?? 'Male',
                ':dob' => $_POST['dob'] ?? '',
                ':parent' => $_POST['parent_name'] ?? null,
                ':phone' => $_POST['phone'] ?? null,
                ':addr' => $_POST['address'] ?? null,
                ':cls' => $_POST['current_class_id'] ?? '',
                ':sts' => $_POST['status'] ?? 'active',
                ':id' => $id
            ]);
            $this->redirect('/students');
        } catch (\Exception $e) {
            $this->redirect('/students/edit/' . $id);
        }
    }

    public function delete($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            // First check if student exists
            $stmt = $db->prepare("SELECT id FROM students WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->fetch()) {
                // To avoid FK constraints, delete results first if any
                $resStmt = $db->prepare("SELECT id FROM results WHERE student_id = ?");
                $resStmt->execute([$id]);
                $results = $resStmt->fetchAll();
                
                foreach ($results as $res) {
                    $db->prepare("DELETE FROM result_items WHERE result_id = ?")->execute([$res->id]);
                }
                $db->prepare("DELETE FROM results WHERE student_id = ?")->execute([$id]);
                
                // Now delete student
                $del = $db->prepare("DELETE FROM students WHERE id = ?");
                $del->execute([$id]);
                $_SESSION['success_msg'] = "Student and their records have been deleted successfully.";
            } else {
                $_SESSION['error_msg'] = "Student not found.";
            }
        } catch (\Exception $e) {
            $_SESSION['error_msg'] = "Error deleting student.";
        }
        
        $this->redirect('/students');
    }

    // --- API ENDPOINTS ---

    private function apiCheckAuth()
    {
        $this->restoreSessionFromToken();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
        }
    }

    public function apiIndex()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $class_id = $_GET['class_id'] ?? '';
        
        $query = "SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.current_class_id = c.id";
        $params = [];
        
        if (!empty($class_id)) {
            $query .= " WHERE s.current_class_id = :class_id";
            $params[':class_id'] = $class_id;
        }
        
        $query .= " ORDER BY c.name ASC, s.id DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        $classes = $db->query("SELECT id, name FROM classes ORDER BY name ASC")->fetchAll();

        $this->jsonResponse(['students' => $students, 'classes' => $classes]);
    }

    public function apiStore()
    {
        $this->apiCheckAuth();
        if ($_SESSION['role'] !== 'Head Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }
        $db = (new Database())->getConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['surname']) || empty($data['first_name']) || empty($data['current_class_id'])) {
            $this->jsonResponse(['error' => 'Surname, first name, and class are required'], 400);
        }

        $regNo = $data['registration_number'] ?? '';
        if (empty(trim($regNo))) {
            $year = date('Y');
            $prefix = "IAMS/$year/";
            $stmtLastReg = $db->prepare("SELECT registration_number FROM students WHERE registration_number LIKE :prefix ORDER BY registration_number DESC LIMIT 1");
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
        }

        try {
            $stmt = $db->prepare("INSERT INTO students (registration_number, surname, first_name, middle_name, gender, dob, parent_name, phone, address, current_class_id) VALUES (:registration_number, :surname, :first_name, :middle_name, :gender, :dob, :parent_name, :phone, :address, :current_class_id)");
            
            $stmt->execute([
                ':registration_number' => $regNo,
                ':surname' => $data['surname'],
                ':first_name' => $data['first_name'],
                ':middle_name' => $data['middle_name'] ?? null,
                ':gender' => $data['gender'] ?? 'Male',
                ':dob' => $data['dob'] ?? null,
                ':parent_name' => $data['parent_name'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':address' => $data['address'] ?? null,
                ':current_class_id' => $data['current_class_id']
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to add student'], 500);
        }
    }

    public function apiUpdate($id)
    {
        $this->apiCheckAuth();
        if ($_SESSION['role'] !== 'Head Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }
        $db = (new Database())->getConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $stmt = $db->prepare("UPDATE students SET registration_number = :reg, surname = :sur, first_name = :first, middle_name = :mid, gender = :gen, dob = :dob, parent_name = :parent, phone = :phone, address = :addr, current_class_id = :cls, status = :sts WHERE id = :id");
            
            $stmt->execute([
                ':reg' => $data['registration_number'] ?? null,
                ':sur' => $data['surname'] ?? '',
                ':first' => $data['first_name'] ?? '',
                ':mid' => $data['middle_name'] ?? null,
                ':gen' => $data['gender'] ?? 'Male',
                ':dob' => $data['dob'] ?? null,
                ':parent' => $data['parent_name'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':addr' => $data['address'] ?? null,
                ':cls' => $data['current_class_id'] ?? '',
                ':sts' => $data['status'] ?? 'active',
                ':id' => $id
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to update student'], 500);
        }
    }

    public function apiDelete($id)
    {
        $this->apiCheckAuth();
        if ($_SESSION['role'] !== 'Head Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("SELECT id FROM students WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->fetch()) {
                $resStmt = $db->prepare("SELECT id FROM results WHERE student_id = ?");
                $resStmt->execute([$id]);
                $results = $resStmt->fetchAll();
                
                foreach ($results as $res) {
                    $db->prepare("DELETE FROM result_items WHERE result_id = ?")->execute([$res->id]);
                }
                $db->prepare("DELETE FROM results WHERE student_id = ?")->execute([$id]);
                
                $del = $db->prepare("DELETE FROM students WHERE id = ?");
                $del->execute([$id]);
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Student not found'], 404);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Error deleting student'], 500);
        }
    }
    public function apiBulkImport()
    {
        if ($_SESSION['role'] !== 'Head Teacher') {
            http_response_code(403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $classId = $data['class_id'] ?? null;
        $students = $data['students'] ?? [];

        if (!$classId || !is_array($students) || count($students) === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data provided']);
            return;
        }

        $db = (new Database())->getConnection();
        $db->beginTransaction();

        try {
            $insert = $db->prepare("
                INSERT INTO students (first_name, surname, gender, registration_number, current_class_id, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");

            // Efficiently pre-fetch the last Reg No for sequential assignment
            $year = date('Y');
            $prefix = "IAMS/$year/";
            $stmtLastReg = $db->prepare("SELECT registration_number FROM students WHERE registration_number LIKE :prefix ORDER BY registration_number DESC LIMIT 1");
            $stmtLastReg->execute([':prefix' => $prefix . '%']);
            $lastReg = $stmtLastReg->fetchColumn();
            
            if ($lastReg) {
                $parts = explode('/', $lastReg);
                $nextNum = intval(end($parts)) + 1;
            } else {
                $nextNum = 1;
            }

            $imported = 0;
            foreach ($students as $s) {
                // Generate sequential Reg No
                $regNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                $nextNum++; // Increment for the next student in the bulk
                
                $insert->execute([
                    trim($s['first_name']),
                    trim($s['surname']),
                    $s['gender'] ?? 'Male',
                    $regNo,
                    $classId
                ]);
                $imported++;
            }

            $db->commit();
            echo json_encode(['success' => true, 'imported' => $imported]);
        } catch (\Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to import students: ' . $e->getMessage()]);
        }
    }
}


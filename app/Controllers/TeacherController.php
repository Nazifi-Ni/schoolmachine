<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class TeacherController extends Controller
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
        
        $stmt = $db->query("SELECT t.*, u.username, u.status as user_status FROM teachers t JOIN users u ON t.user_id = u.id");
        $teachers = $stmt->fetchAll();

        $this->view('teachers/index', [
            'title' => 'Manage Teachers',
            'activeMenu' => 'teachers',
            'teachers' => $teachers
        ]);
    }

    public function create()
    {
        $this->checkAuth();
        $this->view('teachers/create', [
            'title' => 'Add Teacher',
            'activeMenu' => 'teachers'
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if(empty($firstName) || empty($lastName) || empty($username) || empty($password)) {
            // Error handling
            $this->redirect('/teachers/create');
        }

        try {
            $db->beginTransaction();

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, role_id, status) VALUES (:username, :password, 2, 'active')"); // role_id 2 is Class Teacher
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
            $userId = $db->lastInsertId();

            // Create teacher
            $stmt = $db->prepare("INSERT INTO teachers (user_id, first_name, last_name, email, phone) VALUES (:user_id, :first_name, :last_name, :email, :phone)");
            $stmt->execute([
                ':user_id' => $userId,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone
            ]);

            $db->commit();
            $this->redirect('/teachers');
        } catch (\Exception $e) {
            $db->rollBack();
            // Handle error (e.g., duplicate username/email)
            $this->redirect('/teachers/create');
        }
    }

    public function edit($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT t.*, u.username, u.status as user_status FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = :id");
        $stmt->execute([':id' => $id]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            $this->redirect('/teachers');
        }

        $this->view('teachers/edit', [
            'title' => 'Edit Teacher',
            'activeMenu' => 'teachers',
            'teacher' => $teacher
        ]);
    }

    public function update($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $status = $_POST['status'] ?? 'active';

        try {
            $db->beginTransaction();

            // Update teacher info
            $stmt = $db->prepare("UPDATE teachers SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE id = :id");
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':id' => $id
            ]);

            // Get user_id for the teacher
            $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $teacher = $stmt->fetch();

            // Update user status
            $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :user_id");
            $stmt->execute([
                ':status' => $status,
                ':user_id' => $teacher->user_id
            ]);

            // Password update if provided
            if (!empty($_POST['password'])) {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                $stmt->execute([
                    ':password' => $hashedPassword,
                    ':user_id' => $teacher->user_id
                ]);
            }

            $db->commit();
            $this->redirect('/teachers');
        } catch (\Exception $e) {
            $db->rollBack();
            $this->redirect('/teachers/edit/' . $id);
        }
    }

    public function delete($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
                $stmt->execute([':user_id' => $teacher->user_id]);
            }
        } catch (\Exception $e) {
            // handle error
        }
        
        $this->redirect('/teachers');
    }

    // --- API ENDPOINTS ---

    private function apiCheckAuth()
    {
        $this->restoreSessionFromToken();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
        }
    }

    public function apiIndex()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->query("SELECT t.*, u.username, u.status as user_status FROM teachers t JOIN users u ON t.user_id = u.id");
        $teachers = $stmt->fetchAll();

        $this->jsonResponse(['teachers' => $teachers]);
    }

    public function apiStore()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);

        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if(empty($firstName) || empty($lastName) || empty($username) || empty($password)) {
            $this->jsonResponse(['error' => 'Please fill in all required fields'], 400);
        }

        try {
            $db->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, role_id, status) VALUES (:username, :password, 2, 'active')");
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
            $userId = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO teachers (user_id, first_name, last_name, email, phone) VALUES (:user_id, :first_name, :last_name, :email, :phone)");
            $stmt->execute([
                ':user_id' => $userId,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone
            ]);

            $db->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Teacher created successfully']);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['error' => 'Failed to create teacher. Username or email might already exist.'], 500);
        }
    }

    public function apiShow($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT t.*, u.username, u.status as user_status FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = :id");
        $stmt->execute([':id' => $id]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            $this->jsonResponse(['error' => 'Teacher not found'], 404);
        }

        $this->jsonResponse(['teacher' => $teacher]);
    }

    public function apiUpdate($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);

        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $status = $data['status'] ?? 'active';

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE teachers SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE id = :id");
            $stmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':id' => $id
            ]);

            $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $teacher = $stmt->fetch();

            if ($teacher) {
                $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :user_id");
                $stmt->execute([
                    ':status' => $status,
                    ':user_id' => $teacher->user_id
                ]);

                if (!empty($data['password'])) {
                    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                    $stmt->execute([
                        ':password' => $hashedPassword,
                        ':user_id' => $teacher->user_id
                    ]);
                }
            }

            $db->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Teacher updated successfully']);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['error' => 'Failed to update teacher'], 500);
        }
    }

    public function apiDelete($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
                $stmt->execute([':user_id' => $teacher->user_id]);
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Teacher not found'], 404);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to delete teacher'], 500);
        }
    }
}


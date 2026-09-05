<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class AuthController extends Controller
{
    public function loginForm()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/login');
    }

    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->view('auth/login', ['error' => 'Please fill in all fields']);
            return;
        }

        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = :username AND u.status = 'active'");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            // Valid login
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role_name;

            // Optional: Store teacher ID if they are a class teacher
            if ($user->role_name === 'Class Teacher') {
                $teacherStmt = $db->prepare("SELECT id FROM teachers WHERE user_id = :user_id");
                $teacherStmt->bindParam(':user_id', $user->id);
                $teacherStmt->execute();
                $teacher = $teacherStmt->fetch();
                if ($teacher) {
                    $_SESSION['teacher_id'] = $teacher->id;
                }
            }

            $this->redirect('/dashboard');
        } else {
            // Invalid login
            $this->view('auth/login', ['error' => 'Invalid username or password, or account inactive.']);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }

    // --- API ENDPOINTS ---

    public function apiLogin()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->jsonResponse(['error' => 'Please fill in all fields'], 400);
            return;
        }

        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = :username AND u.status = 'active'");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            session_unset();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role_name;

            $teacher_id = null;
            if ($user->role_name === 'Class Teacher') {
                $teacherStmt = $db->prepare("SELECT id FROM teachers WHERE user_id = :user_id");
                $teacherStmt->bindParam(':user_id', $user->id);
                $teacherStmt->execute();
                $teacher = $teacherStmt->fetch();
                if ($teacher) {
                    $_SESSION['teacher_id'] = $teacher->id;
                    $teacher_id = $teacher->id;
                }
            }

            // Generate a stateless token for cross-domain use
            $secret = getenv('AUTH_SECRET') ?: 'iams_arms_secret_key_2025';
            $token_data = $user->id . '|' . $user->role_name . '|' . ($teacher_id ?? '') . '|' . time();
            $token = base64_encode($token_data . '|' . hash_hmac('sha256', $token_data, $secret));

            $this->jsonResponse([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role_name,
                    'teacher_id' => $teacher_id
                ]
            ]);
        } else {
            $this->jsonResponse(['error' => 'Invalid username or password'], 401);
        }
    }

    public function apiMe()
    {
        $secret = getenv('AUTH_SECRET') ?: 'iams_arms_secret_key_2025';

        // Check Authorization Bearer token (cross-domain React frontend)
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
            $decoded = base64_decode($token);
            $last_pipe = strrpos($decoded, '|');
            if ($last_pipe !== false) {
                $token_data = substr($decoded, 0, $last_pipe);
                $provided_hmac = substr($decoded, $last_pipe + 1);
                $expected_hmac = hash_hmac('sha256', $token_data, $secret);
                if (hash_equals($expected_hmac, $provided_hmac)) {
                    $parts = explode('|', $token_data);
                    if (count($parts) === 4) {
                        $teacher_id = $parts[2] !== '' ? (int)$parts[2] : null;
                        $this->jsonResponse([
                            'user' => [
                                'id' => (int)$parts[0],
                                'username' => '',
                                'role' => $parts[1],
                                'teacher_id' => $teacher_id
                            ]
                        ]);
                        return;
                    }
                }
            }
        }

        // Fallback: check student session
        if (isset($_SESSION['student_id'])) {
            $this->jsonResponse([
                'user' => [
                    'id' => $_SESSION['student_id'],
                    'first_name' => $_SESSION['student_first_name'],
                    'surname' => $_SESSION['student_surname'],
                    'registration_number' => $_SESSION['student_reg'],
                    'role' => 'Student'
                ]
            ]);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Not authenticated'], 401);
            return;
        }

        $this->jsonResponse([
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'teacher_id' => $_SESSION['teacher_id'] ?? null
            ]
        ]);
    }

    public function apiLogout()
    {
        session_unset();
        session_destroy();
        $this->jsonResponse(['success' => true]);
    }

    public function apiStudentLogin()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $regNumber = $data['registration_number'] ?? '';
        $pin = $data['pin'] ?? '';

        if (empty($regNumber) || empty($pin)) {
            $this->jsonResponse(['error' => 'Registration number and PIN are required'], 400);
            return;
        }

        // Verify PIN is the last 4 characters of the registration number
        $expectedPin = substr(trim($regNumber), -4);
        
        if (trim($pin) !== $expectedPin) {
             $this->jsonResponse(['error' => 'Invalid PIN. Your PIN is the last 4 digits of your Registration Number.'], 401);
             return;
        }

        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT id, first_name, surname, registration_number, current_class_id FROM students WHERE registration_number = :reg AND status = 'active'");
        $stmt->bindParam(':reg', $regNumber);
        $stmt->execute();
        $student = $stmt->fetch();

        if ($student) {
            // Clear any existing session (like admin sessions)
            session_unset();
            
            $_SESSION['student_id'] = $student->id;
            $_SESSION['student_first_name'] = $student->first_name;
            $_SESSION['student_surname'] = $student->surname;
            $_SESSION['student_reg'] = $student->registration_number;
            $_SESSION['role'] = 'Student';

            $this->jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'surname' => $student->surname,
                    'registration_number' => $student->registration_number,
                    'role' => 'Student'
                ]
            ]);
        } else {
            $this->jsonResponse(['error' => 'Student not found or inactive.'], 404);
        }
    }

    public function apiChangePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Not authenticated'], 401);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $this->jsonResponse(['error' => 'All fields are required'], 400);
            return;
        }

        $userId = $_SESSION['user_id'];
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user->password)) {
            $this->jsonResponse(['error' => 'Incorrect current password'], 401);
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $updateStmt->bindParam(':pass', $hashedPassword);
        $updateStmt->bindParam(':id', $userId);
        
        if ($updateStmt->execute()) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Failed to update password'], 500);
        }
    }
}

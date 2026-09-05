<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class SubjectController extends Controller
{
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head Teacher') {
            $this->redirect('/login');
        }
    }

    public function index($classId)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->execute([':id' => $classId]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->redirect('/classes');
        }

        $stmt = $db->prepare("SELECT * FROM subjects WHERE class_id = :id");
        $stmt->execute([':id' => $classId]);
        $subjects = $stmt->fetchAll();

        $this->view('subjects/index', [
            'title' => 'Manage Subjects: ' . $class->name,
            'activeMenu' => 'classes',
            'class' => $class,
            'subjects' => $subjects
        ]);
    }

    public function store($classId)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $name = $_POST['name'] ?? '';

        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT IGNORE INTO subjects (name, class_id) VALUES (:name, :class_id)");
                $stmt->execute([':name' => $name, ':class_id' => $classId]);
            } catch (\Exception $e) {}
        }

        $this->redirect('/classes/' . $classId . '/subjects');
    }

    public function delete($classId, $subjectId)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id AND class_id = :class_id");
            $stmt->execute([':id' => $subjectId, ':class_id' => $classId]);
        } catch (\Exception $e) {}

        $this->redirect('/classes/' . $classId . '/subjects');
    }

    // --- API ENDPOINTS ---

    private function apiCheckAuth($classId = null)
    {
        $this->restoreSessionFromToken();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        if ($_SESSION['role'] === 'Head Teacher') return;
        
        if ($_SESSION['role'] === 'Class Teacher' && $classId) {
            $db = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
            $stmt->execute([$classId]);
            $teacherId = $stmt->fetchColumn();
            
            if ($teacherId == $_SESSION['teacher_id']) return;
        }

        $this->jsonResponse(['error' => 'Forbidden'], 403);
    }

    public function apiIndex($id)
    {
        $this->apiCheckAuth($id);
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->jsonResponse(['error' => 'Class not found'], 404);
        }

        $sessionStmt = $db->query("SELECT * FROM sessions WHERE is_current = TRUE LIMIT 1");
        $session = $sessionStmt->fetch();
        $sessionId = $session ? $session->id : null;
        
        $termStmt = $db->query("SELECT * FROM terms WHERE is_current = TRUE LIMIT 1");
        $term = $termStmt->fetch();
        $termId = $term ? $term->id : null;

        $stmt = $db->prepare("SELECT * FROM subjects WHERE class_id = :id AND session_id = :session_id AND term_id = :term_id ORDER BY name ASC");
        $stmt->execute([
            ':id' => $id,
            ':session_id' => $sessionId,
            ':term_id' => $termId
        ]);
        $subjects = $stmt->fetchAll();

        $this->jsonResponse([
            'class' => $class, 
            'subjects' => $subjects,
            'session' => $session,
            'term' => $term
        ]);
    }

    public function apiStore($id)
    {
        $this->apiCheckAuth($id);
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';

        if (empty($name)) {
            $this->jsonResponse(['error' => 'Subject name is required'], 400);
        }

        try {
            $sessionStmt = $db->query("SELECT id FROM sessions WHERE is_current = TRUE LIMIT 1");
            $sessionId = $sessionStmt->fetchColumn();
            
            $termStmt = $db->query("SELECT id FROM terms WHERE is_current = TRUE LIMIT 1");
            $termId = $termStmt->fetchColumn();

            $stmt = $db->prepare("INSERT INTO subjects (name, class_id, session_id, term_id) VALUES (:name, :class_id, :session_id, :term_id)");
            $stmt->execute([
                ':name' => $name, 
                ':class_id' => $id,
                ':session_id' => $sessionId ?: null,
                ':term_id' => $termId ?: null
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->jsonResponse(['error' => 'This subject already exists for this class.'], 400);
            } else {
                $this->jsonResponse(['error' => 'Failed to add subject: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function apiDelete($classId, $subjectId)
    {
        $this->apiCheckAuth($classId);
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id AND class_id = :class_id");
            $stmt->execute([':id' => $subjectId, ':class_id' => $classId]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to delete subject (may have existing results)'], 500);
        }
    }
}


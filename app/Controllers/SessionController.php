<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class SessionController extends Controller
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
        
        $sessions = $db->query("SELECT * FROM sessions ORDER BY id DESC")->fetchAll();
        $terms = $db->query("SELECT t.*, s.name as session_name FROM terms t JOIN sessions s ON t.session_id = s.id WHERE s.is_current = TRUE ORDER BY t.id DESC")->fetchAll();

        $this->view('sessions/index', [
            'title' => 'Manage Sessions & Terms',
            'activeMenu' => 'sessions',
            'sessions' => $sessions,
            'terms' => $terms
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $name = $_POST['name'] ?? '';
        if (empty($name)) {
            $this->redirect('/sessions');
            return;
        }

        try {
            // First session created should be current if none exists
            $count = $db->query("SELECT COUNT(*) as count FROM sessions WHERE is_current = TRUE")->fetch()->count;
            $isCurrent = ($count == 0) ? 1 : 0;

            $stmt = $db->prepare("INSERT INTO sessions (name, is_current) VALUES (:name, :is_current)");
            $stmt->execute([
                ':name' => $name,
                ':is_current' => $isCurrent
            ]);
        } catch (\Exception $e) {}

        $this->redirect('/sessions');
    }

    public function setCurrent($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            $db->beginTransaction();
            $db->query("UPDATE sessions SET is_current = FALSE");
            $stmt = $db->prepare("UPDATE sessions SET is_current = TRUE WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }

        $this->redirect('/sessions');
    }

    public function delete($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            // Option to delete related terms, or just delete session (let DB handle cascading or we can manually delete terms)
            $db->beginTransaction();
            $stmt = $db->prepare("DELETE FROM terms WHERE session_id = :id");
            $stmt->execute([':id' => $id]);
            
            $stmt = $db->prepare("DELETE FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
            $_SESSION['success_msg'] = "Session deleted successfully.";
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error_msg'] = "Error deleting session. It might be in use.";
        }

        $this->redirect('/sessions');
    }

    public function storeTerm()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $name = $_POST['name'] ?? '';
        $sessionId = $_POST['session_id'] ?? '';
        
        if (empty($name) || empty($sessionId)) {
            $this->redirect('/sessions');
            return;
        }

        try {
            $count = $db->query("SELECT COUNT(*) as count FROM terms WHERE is_current = TRUE")->fetch()->count;
            $isCurrent = ($count == 0) ? 1 : 0;

            $stmt = $db->prepare("INSERT INTO terms (name, session_id, is_current) VALUES (:name, :session_id, :is_current)");
            $stmt->execute([
                ':name' => $name,
                ':session_id' => $sessionId,
                ':is_current' => $isCurrent
            ]);
        } catch (\Exception $e) {}

        $this->redirect('/sessions');
    }

    public function setCurrentTerm($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            $db->beginTransaction();
            $db->query("UPDATE terms SET is_current = FALSE");
            $stmt = $db->prepare("UPDATE terms SET is_current = TRUE WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }

        $this->redirect('/sessions');
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
        
        $sessions = $db->query("SELECT * FROM sessions ORDER BY id DESC")->fetchAll();
        // Fetch all terms to allow managing terms for any session, not just the current one
        $terms = $db->query("SELECT t.*, s.name as session_name FROM terms t JOIN sessions s ON t.session_id = s.id ORDER BY t.id DESC")->fetchAll();

        $this->jsonResponse(['sessions' => $sessions, 'terms' => $terms]);
    }

    public function apiStoreSession()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';

        if (empty($name)) {
            $this->jsonResponse(['error' => 'Session name is required'], 400);
        }

        try {
            $count = $db->query("SELECT COUNT(*) as count FROM sessions WHERE is_current = TRUE")->fetch()->count;
            $isCurrent = ($count == 0) ? 1 : 0;

            $stmt = $db->prepare("INSERT INTO sessions (name, is_current) VALUES (:name, :is_current)");
            $stmt->execute([
                ':name' => $name,
                ':is_current' => $isCurrent
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to create session'], 500);
        }
    }

    public function apiSetCurrentSession($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();

        try {
            $db->beginTransaction();
            $db->query("UPDATE sessions SET is_current = FALSE");
            $stmt = $db->prepare("UPDATE sessions SET is_current = TRUE WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['error' => 'Failed to set current session'], 500);
        }
    }

    public function apiDeleteSession($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("DELETE FROM terms WHERE session_id = :id");
            $stmt->execute([':id' => $id]);
            
            $stmt = $db->prepare("DELETE FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['error' => 'Error deleting session. It might be in use.'], 500);
        }
    }

    public function apiStoreTerm()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();

        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $sessionId = $data['session_id'] ?? '';
        
        if (empty($name) || empty($sessionId)) {
            $this->jsonResponse(['error' => 'Name and session are required'], 400);
        }

        try {
            $count = $db->query("SELECT COUNT(*) as count FROM terms WHERE is_current = TRUE")->fetch()->count;
            $isCurrent = ($count == 0) ? 1 : 0;

            $stmt = $db->prepare("INSERT INTO terms (name, session_id, is_current) VALUES (:name, :session_id, :is_current)");
            $stmt->execute([
                ':name' => $name,
                ':session_id' => $sessionId,
                ':is_current' => $isCurrent
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to create term'], 500);
        }
    }

    public function apiSetCurrentTerm($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();

        try {
            $db->beginTransaction();
            $db->query("UPDATE terms SET is_current = FALSE");
            $stmt = $db->prepare("UPDATE terms SET is_current = TRUE WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $db->commit();
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['error' => 'Failed to set current term'], 500);
        }
    }

    public function apiDeleteTerm($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM terms WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to delete term'], 500);
        }
    }
}


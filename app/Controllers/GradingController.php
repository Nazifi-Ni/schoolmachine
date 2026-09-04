<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class GradingController extends Controller
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
        
        $grades = $db->query("SELECT * FROM grading_system ORDER BY min_score DESC")->fetchAll();

        $this->view('grading/index', [
            'title' => 'Configure Grading System',
            'activeMenu' => 'grading',
            'grades' => $grades
        ]);
    }

    public function create()
    {
        $this->checkAuth();
        $this->view('grading/create', [
            'title' => 'Add Grade Level',
            'activeMenu' => 'grading'
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $min_score = $_POST['min_score'] ?? '';
        $max_score = $_POST['max_score'] ?? '';
        $grade = $_POST['grade'] ?? '';
        $remark = $_POST['remark'] ?? '';
        
        if ($min_score === '' || $max_score === '' || empty($grade)) {
            $this->redirect('/grading/create');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO grading_system (min_score, max_score, grade, remark) VALUES (:min_score, :max_score, :grade, :remark)");
            $stmt->execute([
                ':min_score' => $min_score,
                ':max_score' => $max_score,
                ':grade' => $grade,
                ':remark' => $remark
            ]);
            $this->redirect('/grading');
        } catch (\Exception $e) {
            $this->redirect('/grading/create');
        }
    }

    public function edit($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM grading_system WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $grade = $stmt->fetch();

        if (!$grade) {
            $this->redirect('/grading');
        }

        $this->view('grading/edit', [
            'title' => 'Edit Grade Level',
            'activeMenu' => 'grading',
            'grade' => $grade
        ]);
    }

    public function update($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        try {
            $stmt = $db->prepare("UPDATE grading_system SET min_score = :min_score, max_score = :max_score, grade = :grade, remark = :remark WHERE id = :id");
            
            $stmt->execute([
                ':min_score' => $_POST['min_score'] ?? '',
                ':max_score' => $_POST['max_score'] ?? '',
                ':grade' => $_POST['grade'] ?? '',
                ':remark' => $_POST['remark'] ?? '',
                ':id' => $id
            ]);
            $this->redirect('/grading');
        } catch (\Exception $e) {
            $this->redirect('/grading/edit/' . $id);
        }
    }

    public function delete($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("DELETE FROM grading_system WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {}
        
        $this->redirect('/grading');
    }

    // --- API ENDPOINTS ---

    private function apiCheckAuth()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Head Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
        }
    }

    public function apiIndex()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $grades = $db->query("SELECT * FROM grading_system ORDER BY min_score DESC")->fetchAll();
        $this->jsonResponse(['grades' => $grades]);
    }

    public function apiStore()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['min_score']) || !isset($data['max_score']) || empty($data['grade'])) {
            $this->jsonResponse(['error' => 'Required fields missing'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO grading_system (min_score, max_score, grade, remark) VALUES (:min_score, :max_score, :grade, :remark)");
            $stmt->execute([
                ':min_score' => $data['min_score'],
                ':max_score' => $data['max_score'],
                ':grade' => $data['grade'],
                ':remark' => $data['remark'] ?? ''
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to add grade'], 500);
        }
    }

    public function apiUpdate($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $stmt = $db->prepare("UPDATE grading_system SET min_score = :min_score, max_score = :max_score, grade = :grade, remark = :remark WHERE id = :id");
            $stmt->execute([
                ':min_score' => $data['min_score'] ?? '',
                ':max_score' => $data['max_score'] ?? '',
                ':grade' => $data['grade'] ?? '',
                ':remark' => $data['remark'] ?? '',
                ':id' => $id
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to update grade'], 500);
        }
    }

    public function apiDelete($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("DELETE FROM grading_system WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to delete grade'], 500);
        }
    }
}

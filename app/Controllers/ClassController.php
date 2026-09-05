<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class ClassController extends Controller
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
        
        $stmt = $db->query("SELECT c.*, t.first_name, t.last_name FROM classes c LEFT JOIN teachers t ON c.teacher_id = t.id");
        $classes = $stmt->fetchAll();

        $this->view('classes/index', [
            'title' => 'Manage Classes',
            'activeMenu' => 'classes',
            'classes' => $classes
        ]);
    }

    public function create()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        $stmt = $db->query("SELECT id, first_name, last_name FROM teachers");
        $teachers = $stmt->fetchAll();

        $this->view('classes/create', [
            'title' => 'Add Class',
            'activeMenu' => 'classes',
            'teachers' => $teachers
        ]);
    }

    public function store()
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $name = $_POST['name'] ?? '';
        $level = $_POST['level'] ?? '';
        $teacherId = $_POST['teacher_id'] ?? null;
        if(empty($teacherId)) $teacherId = null;

        if (empty($name) || empty($level)) {
            $this->redirect('/classes/create');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO classes (name, level, teacher_id) VALUES (:name, :level, :teacher_id)");
            $stmt->execute([
                ':name' => $name,
                ':level' => $level,
                ':teacher_id' => $teacherId
            ]);
            $this->redirect('/classes');
        } catch (\Exception $e) {
            $this->redirect('/classes/create');
        }
    }

    public function edit($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->redirect('/classes');
        }

        $stmt = $db->query("SELECT id, first_name, last_name FROM teachers");
        $teachers = $stmt->fetchAll();

        $this->view('classes/edit', [
            'title' => 'Edit Class',
            'activeMenu' => 'classes',
            'class' => $class,
            'teachers' => $teachers
        ]);
    }

    public function update($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();

        $name = $_POST['name'] ?? '';
        $level = $_POST['level'] ?? '';
        $teacherId = $_POST['teacher_id'] ?? null;
        if(empty($teacherId)) $teacherId = null;

        try {
            $stmt = $db->prepare("UPDATE classes SET name = :name, level = :level, teacher_id = :teacher_id WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':level' => $level,
                ':teacher_id' => $teacherId,
                ':id' => $id
            ]);

            $this->redirect('/classes');
        } catch (\Exception $e) {
            $this->redirect('/classes/edit/' . $id);
        }
    }

    public function delete($id)
    {
        $this->checkAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("DELETE FROM classes WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            // handle error
        }
        
        $this->redirect('/classes');
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
        
        $stmt = $db->query("SELECT c.*, t.first_name, t.last_name FROM classes c LEFT JOIN teachers t ON c.teacher_id = t.id");
        $classes = $stmt->fetchAll();

        $stmt = $db->query("SELECT id, first_name, last_name FROM teachers");
        $teachers = $stmt->fetchAll();

        $this->jsonResponse(['classes' => $classes, 'teachers' => $teachers]);
    }

    public function apiStore()
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $level = $data['level'] ?? '';
        $teacherId = $data['teacher_id'] ?? null;
        if(empty($teacherId)) $teacherId = null;

        if (empty($name) || empty($level)) {
            $this->jsonResponse(['error' => 'Please provide a name and level'], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO classes (name, level, teacher_id) VALUES (:name, :level, :teacher_id)");
            $stmt->execute([
                ':name' => $name,
                ':level' => $level,
                ':teacher_id' => $teacherId
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to create class'], 500);
        }
    }

    public function apiUpdate($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $level = $data['level'] ?? '';
        $teacherId = $data['teacher_id'] ?? null;
        if(empty($teacherId)) $teacherId = null;

        if (empty($name) || empty($level)) {
            $this->jsonResponse(['error' => 'Please provide a name and level'], 400);
        }

        try {
            $stmt = $db->prepare("UPDATE classes SET name = :name, level = :level, teacher_id = :teacher_id WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':level' => $level,
                ':teacher_id' => $teacherId,
                ':id' => $id
            ]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to update class'], 500);
        }
    }

    public function apiDelete($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        try {
            $stmt = $db->prepare("DELETE FROM classes WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to delete class (might have associated students)'], 500);
        }
    }
    public function apiPromote($id)
    {
        $this->apiCheckAuth();
        $db = (new Database())->getConnection();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $targetClassId = $data['target_class_id'] ?? null;
        
        if (empty($targetClassId)) {
            $this->jsonResponse(['error' => 'Please provide a target class'], 400);
            return;
        }

        try {
            // Update all active students in the current class ($id) to the target class
            $stmt = $db->prepare("UPDATE students SET current_class_id = :target_id WHERE current_class_id = :current_id AND status = 'active'");
            $stmt->execute([
                ':target_id' => $targetClassId,
                ':current_id' => $id
            ]);
            $count = $stmt->rowCount();
            
            $this->jsonResponse(['success' => true, 'promoted' => $count]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to promote students'], 500);
        }
    }
}


<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class DashboardController extends Controller
{
    public function index()
    {
        // Must be logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $db = (new Database())->getConnection();
        $role = $_SESSION['role'];
        $data = [
            'title' => 'Dashboard',
            'activeMenu' => 'dashboard'
        ];

        if ($role === 'Head Teacher') {
            // Fetch stats for Head Teacher
            $stats = [];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM students WHERE status = 'active'");
            $stats['total_students'] = $stmt->fetch()->total;

            $stmt = $db->query("SELECT COUNT(*) as total FROM teachers");
            $stats['total_teachers'] = $stmt->fetch()->total;

            $stmt = $db->query("SELECT COUNT(*) as total FROM classes");
            $stats['total_classes'] = $stmt->fetch()->total;

            // Get current session
            $stmt = $db->query("SELECT name FROM sessions WHERE is_current = TRUE");
            $session = $stmt->fetch();
            $stats['current_session'] = $session ? $session->name : 'N/A';

            // Get current term
            $stmt = $db->query("SELECT name FROM terms WHERE is_current = TRUE");
            $term = $stmt->fetch();
            $stats['current_term'] = $term ? $term->name : 'N/A';
            
            $data['stats'] = $stats;
            $this->view('dashboard/head_teacher', $data);

        } elseif ($role === 'Class Teacher') {
            // Fetch stats for Class Teacher
            $teacher_id = $_SESSION['teacher_id'] ?? null;
            $stats = [
                'total_students' => 0,
                'class_name' => 'N/A',
                'pending_results' => 0
            ];

            if ($teacher_id) {
                // Get assigned class
                $stmt = $db->prepare("SELECT id, name FROM classes WHERE teacher_id = :teacher_id");
                $stmt->bindParam(':teacher_id', $teacher_id);
                $stmt->execute();
                $class = $stmt->fetch();

                if ($class) {
                    $stats['class_name'] = $class->name;
                    
                    // Get total students in this class
                    $stmt = $db->prepare("SELECT COUNT(*) as total FROM students WHERE current_class_id = :class_id AND status = 'active'");
                    $stmt->bindParam(':class_id', $class->id);
                    $stmt->execute();
                    $stats['total_students'] = $stmt->fetch()->total;

                    // Calculate pending results
                    $sessionStmt = $db->query("SELECT id FROM sessions WHERE is_current = TRUE LIMIT 1");
                    $currentSession = $sessionStmt->fetchColumn();
                    $termStmt = $db->query("SELECT id FROM terms WHERE is_current = TRUE LIMIT 1");
                    $currentTerm = $termStmt->fetchColumn();

                    if ($currentSession && $currentTerm) {
                        // Get total subjects for this class
                        $subStmt = $db->prepare("SELECT COUNT(*) FROM subjects WHERE class_id = ? AND session_id = ? AND term_id = ?");
                        $subStmt->execute([$class->id, $currentSession, $currentTerm]);
                        $totalSubjects = $subStmt->fetchColumn();

                        $stmt = $db->prepare("SELECT id FROM students WHERE current_class_id = :class_id AND status = 'active'");
                        $stmt->execute([':class_id' => $class->id]);
                        $students = $stmt->fetchAll();
                        
                        $pendingCount = 0;
                        foreach ($students as $student) {
                            $resStmt = $db->prepare("SELECT id, status FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
                            $resStmt->execute([$student->id, $currentSession, $currentTerm]);
                            $resultRecord = $resStmt->fetch();

                            $is_pending = true;
                            if ($resultRecord) {
                                $itemStmt = $db->prepare("SELECT COUNT(*) FROM result_items WHERE result_id = ?");
                                $itemStmt->execute([$resultRecord->id]);
                                $itemCount = $itemStmt->fetchColumn();
                                if ($totalSubjects > 0 && $itemCount >= $totalSubjects) {
                                    $is_pending = false;
                                } elseif ($totalSubjects == 0 && $itemCount > 0) {
                                    $is_pending = false;
                                }
                            }
                            if ($is_pending) {
                                $pendingCount++;
                            }
                        }
                        $stats['pending_results'] = $pendingCount;
                    }
                }
            }

            $data['stats'] = $stats;
            $this->view('dashboard/class_teacher', $data);
        } else {
            die("Unknown role.");
        }
    }
    public function apiIndex()
    {
        $this->restoreSessionFromToken();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Not authenticated'], 401);
            return;
        }

        $db = (new Database())->getConnection();
        $role = $_SESSION['role'];

        if ($role === 'Head Teacher') {
            $stats = [];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM students WHERE status = 'active'");
            $stats['total_students'] = $stmt->fetch()->total;

            $stmt = $db->query("SELECT COUNT(*) as total FROM teachers");
            $stats['total_teachers'] = $stmt->fetch()->total;

            $stmt = $db->query("SELECT COUNT(*) as total FROM classes");
            $stats['total_classes'] = $stmt->fetch()->total;

            $stmt = $db->query("SELECT name FROM sessions WHERE is_current = TRUE");
            $session = $stmt->fetch();
            $stats['current_session'] = $session ? $session->name : 'N/A';

            $stmt = $db->query("SELECT name FROM terms WHERE is_current = TRUE");
            $term = $stmt->fetch();
            $stats['current_term'] = $term ? $term->name : 'N/A';
            // Recent Activities for Head Teacher
            $recent = $db->query("SELECT id, CONCAT(first_name, ' ', surname) as title, 'New student registered' as description, COALESCE(admission_date, DATE(created_at)) as date, 'student' as type FROM students ORDER BY id DESC LIMIT 4")->fetchAll();

            $this->jsonResponse(['stats' => $stats, 'role' => $role, 'recentActivities' => $recent]);

        } elseif ($role === 'Class Teacher') {
            $teacher_id = $_SESSION['teacher_id'] ?? null;
            $stats = [
                'total_students' => 0,
                'class_name' => 'N/A',
                'pending_results' => 0
            ];
            $recent = [];

            if ($teacher_id) {
                $stmt = $db->prepare("SELECT id, name FROM classes WHERE teacher_id = :teacher_id");
                $stmt->bindParam(':teacher_id', $teacher_id);
                $stmt->execute();
                $class = $stmt->fetch();

                if ($class) {
                    $stats['class_name'] = $class->name;
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as total FROM students WHERE current_class_id = :class_id AND status = 'active'");
                    $stmt->bindParam(':class_id', $class->id);
                    $stmt->execute();
                    $stats['total_students'] = $stmt->fetch()->total;

                    $sessionStmt = $db->query("SELECT id FROM sessions WHERE is_current = TRUE LIMIT 1");
                    $currentSession = $sessionStmt->fetchColumn();
                    $termStmt = $db->query("SELECT id FROM terms WHERE is_current = TRUE LIMIT 1");
                    $currentTerm = $termStmt->fetchColumn();

                    if ($currentSession && $currentTerm) {
                        $subStmt = $db->prepare("SELECT COUNT(*) FROM subjects WHERE class_id = ?");
                        $subStmt->execute([$class->id]);
                        $totalSubjects = $subStmt->fetchColumn();

                        $stmt = $db->prepare("SELECT id FROM students WHERE current_class_id = :class_id AND status = 'active'");
                        $stmt->execute([':class_id' => $class->id]);
                        $students = $stmt->fetchAll();
                        
                        $pendingCount = 0;
                        foreach ($students as $student) {
                            $resStmt = $db->prepare("SELECT id, status FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
                            $resStmt->execute([$student->id, $currentSession, $currentTerm]);
                            $resultRecord = $resStmt->fetch();

                            $is_pending = true;
                            if ($resultRecord) {
                                $itemStmt = $db->prepare("SELECT COUNT(*) FROM result_items WHERE result_id = ?");
                                $itemStmt->execute([$resultRecord->id]);
                                $itemCount = $itemStmt->fetchColumn();
                                if ($totalSubjects > 0 && $itemCount >= $totalSubjects) {
                                    $is_pending = false;
                                } elseif ($totalSubjects == 0 && $itemCount > 0) {
                                    $is_pending = false;
                                }
                            }
                            if ($is_pending) {
                                $pendingCount++;
                            }
                        }
                        $stats['pending_results'] = $pendingCount;
                    }
                    
                    // Recent students added to this class
                    $stmtRecent = $db->prepare("SELECT id, CONCAT(first_name, ' ', surname) as title, 'Enrolled in your class' as description, admission_date as date, 'student' as type FROM students WHERE current_class_id = :class_id ORDER BY id DESC LIMIT 4");
                    $stmtRecent->execute([':class_id' => $class->id]);
                    $recent = $stmtRecent->fetchAll();
                }
            }

            $this->jsonResponse(['stats' => $stats, 'role' => $role, 'recentActivities' => $recent]);
        } else {
            $this->jsonResponse(['error' => 'Unknown role.'], 403);
        }
    }
}

<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class ResultController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    // View My Class (Students)
    public function myClass()
    {
        $this->requireRole('Class Teacher');

        $teacher_id = $_SESSION['teacher_id'] ?? null;
        if (!$teacher_id) {
            die("Teacher profile not found.");
        }

        // Find assigned class
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->view('results/no_class_assigned', ['title' => 'My Class']);
            return;
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        // Get students
        $studentStmt = $this->db->prepare("SELECT * FROM students WHERE current_class_id = :class_id AND status = 'active' ORDER BY surname, first_name ASC");
        $studentStmt->execute([':class_id' => $class->id]);
        $all_students = $studentStmt->fetchAll();

        $students = [];
        $pending_count = 0;
        $filter_pending = isset($_GET['filter']) && $_GET['filter'] === 'pending';

        if ($currentSession && $currentTerm) {
            // Get total subjects for this class
            $subStmt = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE class_id = ? AND session_id = ? AND term_id = ?");
            $subStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
            $totalSubjects = $subStmt->fetchColumn();

            foreach ($all_students as $student) {
                // Check if result exists and has items
                $resStmt = $this->db->prepare("SELECT id, status FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
                $resStmt->execute([$student->id, $currentSession->id, $currentTerm->id]);
                $resultRecord = $resStmt->fetch();

                $is_pending = true;
                if ($resultRecord) {
                    $itemStmt = $this->db->prepare("SELECT COUNT(*) FROM result_items WHERE result_id = ?");
                    $itemStmt->execute([$resultRecord->id]);
                    $itemCount = $itemStmt->fetchColumn();
                    if ($totalSubjects > 0 && $itemCount >= $totalSubjects) {
                        $is_pending = false;
                    } elseif ($totalSubjects == 0 && $itemCount > 0) {
                        $is_pending = false;
                    }
                }

                $student->is_pending = $is_pending;
                if ($is_pending) {
                    $pending_count++;
                }

                if (!$filter_pending || ($filter_pending && $is_pending)) {
                    $students[] = $student;
                }
            }
        } else {
            $students = $all_students;
        }

        $this->view('results/my_class', [
            'title' => 'My Class: ' . $class->name,
            'activeMenu' => 'my-class',
            'class' => $class,
            'students' => $students,
            'pending_count' => $pending_count ?? 0,
            'filter_pending' => $filter_pending ?? false
        ]);
    }

    // Manage Subjects for the Class
    public function manageSubjects()
    {
        $this->requireRole('Class Teacher');

        $teacher_id = $_SESSION['teacher_id'] ?? null;
        
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->redirect('/my-class');
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        if (!$currentSession || !$currentTerm) {
            die("Active session or term is not set.");
        }

        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE class_id = :class_id AND session_id = :session_id AND term_id = :term_id ORDER BY name ASC");
        $stmt->execute([
            ':class_id' => $class->id,
            ':session_id' => $currentSession->id,
            ':term_id' => $currentTerm->id
        ]);
        $subjects = $stmt->fetchAll();

        $this->view('results/manage_subjects', [
            'title' => 'Manage Subjects',
            'activeMenu' => 'my-class',
            'class' => $class,
            'subjects' => $subjects,
            'currentSession' => $currentSession,
            'currentTerm' => $currentTerm
        ]);
    }

    public function storeSubject()
    {
        $this->requireRole('Class Teacher');
        $teacher_id = $_SESSION['teacher_id'] ?? null;
        
        $stmt = $this->db->prepare("SELECT id FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class_id = $stmt->fetchColumn();

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        if ($class_id && $currentSession && $currentTerm && isset($_POST['name']) && !empty(trim($_POST['name']))) {
            $name = trim($_POST['name']);
            $insert = $this->db->prepare("INSERT INTO subjects (class_id, session_id, term_id, name) VALUES (?, ?, ?, ?)");
            $insert->execute([$class_id, $currentSession->id, $currentTerm->id, $name]);
            $_SESSION['success_msg'] = "Subject added successfully.";
        }
        $this->redirect('/results/subjects');
    }

    public function deleteSubject($id)
    {
        $this->requireRole('Class Teacher');
        $teacher_id = $_SESSION['teacher_id'] ?? null;
        
        $stmt = $this->db->prepare("SELECT id FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class_id = $stmt->fetchColumn();

        if ($class_id) {
            // Verify subject belongs to this class
            $chk = $this->db->prepare("SELECT id FROM subjects WHERE id = ? AND class_id = ?");
            $chk->execute([$id, $class_id]);
            if ($chk->fetch()) {
                $del = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
                $del->execute([$id]);
                $_SESSION['success_msg'] = "Subject removed.";
            }
        }
        $this->redirect('/results/subjects');
    }

    // Enter Results for a specific student
    public function studentResults($id)
    {
        $this->requireRole('Class Teacher');

        $teacher_id = $_SESSION['teacher_id'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->redirect('/my-class');
        }

        // Get student
        $stuStmt = $this->db->prepare("SELECT * FROM students WHERE id = ? AND current_class_id = ?");
        $stuStmt->execute([$id, $class->id]);
        $student = $stuStmt->fetch();

        if (!$student) {
            die("Student not found or not in your class.");
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        if (!$currentSession || !$currentTerm) {
            die("Active session or term is not set.");
        }

        // Get subjects for this class/session/term
        $subStmt = $this->db->prepare("SELECT * FROM subjects WHERE class_id = ? AND session_id = ? AND term_id = ? ORDER BY name ASC");
        $subStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
        $subjects = $subStmt->fetchAll();

        // Get result parent record
        $resStmt = $this->db->prepare("SELECT * FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
        $resStmt->execute([$id, $currentSession->id, $currentTerm->id]);
        $resultRecord = $resStmt->fetch();

        $resultItems = [];
        if ($resultRecord) {
            $itemStmt = $this->db->prepare("SELECT * FROM result_items WHERE result_id = ?");
            $itemStmt->execute([$resultRecord->id]);
            $items = $itemStmt->fetchAll();
            foreach ($items as $it) {
                $resultItems[$it->subject_id] = $it;
            }
        }

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        $this->view('results/student_results', [
            'title' => 'Enter Results: ' . $student->first_name,
            'activeMenu' => 'my-class',
            'student' => $student,
            'class' => $class,
            'subjects' => $subjects,
            'resultRecord' => $resultRecord,
            'resultItems' => $resultItems,
            'currentSession' => $currentSession,
            'currentTerm' => $currentTerm,
            'gradingSystem' => $gradingSystem
        ]);
    }

    // Save Student Results
    public function saveStudentResults($id)
    {
        $this->requireRole('Class Teacher');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/my-class');
        }

        $teacher_id = $_SESSION['teacher_id'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->redirect('/my-class');
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $scores = $_POST['scores'] ?? [];
        $remark = $_POST['class_teacher_remark'] ?? null;
        $ht_remark = $_POST['head_teacher_remark'] ?? null;
        $ht_name = $_POST['head_teacher_name'] ?? null;
        $attendance = $_POST['attendance'] ?? null;
        $resumption_date = $_POST['resumption_date'] ?? null;

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        $this->db->beginTransaction();
        try {
            // Find or create result parent
            $resStmt = $this->db->prepare("SELECT id FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $resStmt->execute([$id, $currentSession->id, $currentTerm->id]);
            $result_id = $resStmt->fetchColumn();

            if (!$result_id) {
                $insertRes = $this->db->prepare("INSERT INTO results (student_id, class_id, session_id, term_id, status, class_teacher_remark, head_teacher_remark, head_teacher_name, attendance, resumption_date) VALUES (?, ?, ?, ?, 'OPEN', ?, ?, ?, ?, ?)");
                $insertRes->execute([$id, $class->id, $currentSession->id, $currentTerm->id, $remark, $ht_remark, $ht_name, $attendance, $resumption_date]);
                $result_id = $this->db->lastInsertId();
            } else {
                $updateRes = $this->db->prepare("UPDATE results SET class_teacher_remark = ?, head_teacher_remark = ?, head_teacher_name = ?, attendance = ?, resumption_date = ? WHERE id = ?");
                $updateRes->execute([$remark, $ht_remark, $ht_name, $attendance, $resumption_date, $result_id]);
            }

            foreach ($scores as $subject_id => $data) {
                $ca1 = empty($data['ca1']) ? 0 : (float)$data['ca1'];
                $ca2 = empty($data['ca2']) ? 0 : (float)$data['ca2'];
                $exam = empty($data['exam']) ? 0 : (float)$data['exam'];
                $total = $ca1 + $ca2 + $exam;

                $grade = '';
                $remark = '';
                foreach ($gradingSystem as $g) {
                    if ($total >= $g->min_score && $total <= $g->max_score) {
                        $grade = $g->grade;
                        $remark = $g->remark;
                        break;
                    }
                }

                $itemStmt = $this->db->prepare("SELECT id FROM result_items WHERE result_id = ? AND subject_id = ?");
                $itemStmt->execute([$result_id, $subject_id]);
                $item_id = $itemStmt->fetchColumn();

                if ($item_id) {
                    $updateItem = $this->db->prepare("UPDATE result_items SET ca1 = ?, ca2 = ?, exam = ?, total = ?, grade = ?, remark = ? WHERE id = ?");
                    $updateItem->execute([$ca1, $ca2, $exam, $total, $grade, $remark, $item_id]);
                } else {
                    $insertItem = $this->db->prepare("INSERT INTO result_items (result_id, subject_id, ca1, ca2, exam, total, grade, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $insertItem->execute([$result_id, $subject_id, $ca1, $ca2, $exam, $total, $grade, $remark]);
                }
            }
            $this->db->commit();
            $_SESSION['success_msg'] = "Scores saved successfully.";
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['error_msg'] = "Error saving scores: " . $e->getMessage();
        }

        $this->redirect("/results/student/{$id}");
    }

    public function printResult($id)
    {
        $this->requireRole('Class Teacher');
        $teacher_id = $_SESSION['teacher_id'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        // Get student
        $stuStmt = $this->db->prepare("SELECT * FROM students WHERE id = ? AND current_class_id = ?");
        $stuStmt->execute([$id, $class->id]);
        $student = $stuStmt->fetch();

        if (!$student) {
            die("Student not found.");
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        // Get results
        $resStmt = $this->db->prepare("SELECT * FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
        $resStmt->execute([$id, $currentSession->id, $currentTerm->id]);
        $resultRecord = $resStmt->fetch();

        $items = [];
        $totalScore = 0;
        if ($resultRecord) {
            $itemStmt = $this->db->prepare("SELECT ri.*, s.name as subject_name FROM result_items ri JOIN subjects s ON ri.subject_id = s.id WHERE ri.result_id = ? ORDER BY s.name");
            $itemStmt->execute([$resultRecord->id]);
            $items = $itemStmt->fetchAll();
            foreach ($items as $it) {
                $totalScore += $it->total;
            }
        }

        // Calculate Position and Class Average
        $posStmt = $this->db->prepare("
            SELECT s.id as student_id, COALESCE(SUM(ri.total), 0) as grand_total 
            FROM students s 
            LEFT JOIN results r ON s.id = r.student_id AND r.session_id = ? AND r.term_id = ? 
            LEFT JOIN result_items ri ON r.id = ri.result_id 
            WHERE s.current_class_id = ? AND s.status = 'active'
            GROUP BY s.id 
            ORDER BY grand_total DESC
        ");
        $posStmt->execute([$currentSession->id, $currentTerm->id, $class->id]);
        $rankings = $posStmt->fetchAll();

        $position = 0;
        $total_students = count($rankings);
        $class_total_sum = 0;
        
        $current_rank = 1;
        $last_score = null;

        foreach ($rankings as $index => $rank) {
            $class_total_sum += $rank->grand_total;
            
            if ($last_score !== null && $rank->grand_total < $last_score) {
                $current_rank++;
            }
            
            if ($rank->student_id == $student->id) {
                $position = $current_rank;
            }
            
            $last_score = $rank->grand_total;
        }

        $print_total_students = isset($_GET['ts']) && is_numeric($_GET['ts']) && (int)$_GET['ts'] > 0 ? (int)$_GET['ts'] : $total_students;

        $class_average = $total_students > 0 ? ($class_total_sum / $total_students) : 0;

        // Position Suffix (1st, 2nd, 3rd, 4th...)
        $suffix = 'th';
        if (!in_array($position % 100, [11, 12, 13])) {
            switch ($position % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }
        $positionStr = $position > 0 ? $position . $suffix : 'N/A';

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        // Calculate Highest, Lowest, Class Avr per subject
        $subjectStats = [];
        $statStmt = $this->db->prepare("
            SELECT ri.subject_id, 
                   MAX(ri.total) as highest, 
                   MIN(ri.total) as lowest, 
                   AVG(ri.total) as class_avr 
            FROM result_items ri 
            JOIN results r ON ri.result_id = r.id 
            WHERE r.class_id = ? AND r.session_id = ? AND r.term_id = ? 
            GROUP BY ri.subject_id
        ");
        $statStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
        foreach ($statStmt->fetchAll() as $s) {
            $subjectStats[$s->subject_id] = $s;
        }

        // --- AUTOMATED FEE CALCULATION ---
        // Next term fee based on current term's structure for this class
        $feeStmt = $this->db->prepare("SELECT amount FROM fee_structures WHERE session_id = ? AND term_id = ? AND class_id = ?");
        $feeStmt->execute([$currentSession->id, $currentTerm->id, $class->id]);
        $nextFee = (float) $feeStmt->fetchColumn();

        // Past balance based on all unpaid student_fees
        $balStmt = $this->db->prepare("SELECT SUM(total_amount - paid_amount) FROM student_fees WHERE student_id = ? AND status != 'paid'");
        $balStmt->execute([$student->id]);
        $pastBalance = (float) $balStmt->fetchColumn();

        $this->view('results/report_card', [
            'student' => $student,
            'class' => $class,
            'currentSession' => $currentSession,
            'currentTerm' => $currentTerm,
            'resultRecord' => $resultRecord,
            'items' => $items,
            'totalScore' => $totalScore,
            'positionStr' => $positionStr,
            'position' => $position,
            'totalStudents' => $print_total_students,
            'classAverage' => $class_average,
            'gradingSystem' => $gradingSystem,
            'subjectStats' => $subjectStats,
            'pastBalance' => $pastBalance,
            'nextFee' => $nextFee
        ]);
    }

    public function printAll()
    {
        $this->requireRole('Class Teacher');
        $teacher_id = $_SESSION['teacher_id'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            die("Class not found.");
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();
        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $stuStmt = $this->db->prepare("SELECT * FROM students WHERE current_class_id = ? AND status = 'active' ORDER BY surname, first_name ASC");
        $stuStmt->execute([$class->id]);
        $students = $stuStmt->fetchAll();

        // Pre-calculate positions
        $posStmt = $this->db->prepare("
            SELECT s.id as student_id, COALESCE(SUM(ri.total), 0) as grand_total 
            FROM students s 
            LEFT JOIN results r ON s.id = r.student_id AND r.session_id = ? AND r.term_id = ? 
            LEFT JOIN result_items ri ON r.id = ri.result_id 
            WHERE s.current_class_id = ? AND s.status = 'active'
            GROUP BY s.id 
            ORDER BY grand_total DESC
        ");
        $posStmt->execute([$currentSession->id, $currentTerm->id, $class->id]);
        $rankings = $posStmt->fetchAll();

        $total_students = count($rankings);
        $class_total_sum = 0;
        $studentPositions = [];
        
        $current_rank = 1;
        $last_score = null;

        foreach ($rankings as $rank) {
            $class_total_sum += $rank->grand_total;
            if ($last_score !== null && $rank->grand_total < $last_score) {
                $current_rank++;
            }
            $studentPositions[$rank->student_id] = $current_rank;
            $last_score = $rank->grand_total;
        }

        $print_total_students = isset($_GET['ts']) && is_numeric($_GET['ts']) && (int)$_GET['ts'] > 0 ? (int)$_GET['ts'] : $total_students;
        $classAverage = $total_students > 0 ? ($class_total_sum / $total_students) : 0;

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        // Calculate Highest, Lowest, Class Avr per subject
        $subjectStats = [];
        $statStmt = $this->db->prepare("
            SELECT ri.subject_id, 
                   MAX(ri.total) as highest, 
                   MIN(ri.total) as lowest, 
                   AVG(ri.total) as class_avr 
            FROM result_items ri 
            JOIN results r ON ri.result_id = r.id 
            WHERE r.class_id = ? AND r.session_id = ? AND r.term_id = ? 
            GROUP BY ri.subject_id
        ");
        $statStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
        foreach ($statStmt->fetchAll() as $s) {
            $subjectStats[$s->subject_id] = $s;
        }

        // --- AUTOMATED FEE CALCULATION FOR ALL ---
        $feeStmt = $this->db->prepare("SELECT amount FROM fee_structures WHERE session_id = ? AND term_id = ? AND class_id = ?");
        $feeStmt->execute([$currentSession->id, $currentTerm->id, $class->id]);
        $classNextFee = (float) $feeStmt->fetchColumn();

        $balStmt = $this->db->prepare("SELECT student_id, SUM(total_amount - paid_amount) as balance FROM student_fees WHERE status != 'paid' GROUP BY student_id");
        $balStmt->execute();
        $studentBalances = [];
        foreach($balStmt->fetchAll() as $row) {
            $studentBalances[$row->student_id] = (float) $row->balance;
        }

        $allReports = [];

        foreach ($students as $student) {
            $resStmt = $this->db->prepare("SELECT * FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $resStmt->execute([$student->id, $currentSession->id, $currentTerm->id]);
            $resultRecord = $resStmt->fetch();

            $items = [];
            $totalScore = 0;
            if ($resultRecord) {
                $itemStmt = $this->db->prepare("SELECT ri.*, s.name as subject_name FROM result_items ri JOIN subjects s ON ri.subject_id = s.id WHERE ri.result_id = ? ORDER BY s.name");
                $itemStmt->execute([$resultRecord->id]);
                $items = $itemStmt->fetchAll();
                foreach ($items as $it) {
                    $totalScore += $it->total;
                }
            }

            $position = $studentPositions[$student->id] ?? 0;
            $suffix = 'th';
            if (!in_array($position % 100, [11, 12, 13])) {
                switch ($position % 10) {
                    case 1: $suffix = 'st'; break;
                    case 2: $suffix = 'nd'; break;
                    case 3: $suffix = 'rd'; break;
                }
            }
            $positionStr = $position > 0 ? $position . $suffix : 'N/A';
            
            $pastBalance = $studentBalances[$student->id] ?? 0.0;

            $allReports[] = [
                'student' => $student,
                'resultRecord' => $resultRecord,
                'items' => $items,
                'totalScore' => $totalScore,
                'positionStr' => $positionStr,
                'totalStudents' => $print_total_students,
                'classAverage' => $classAverage,
                'gradingSystem' => $gradingSystem,
                'class' => $class,
                'currentSession' => $currentSession,
                'currentTerm' => $currentTerm,
                'subjectStats' => $subjectStats,
                'pastBalance' => $pastBalance,
                'nextFee' => $classNextFee
            ];
        }

        $this->view('results/report_card_all', [
            'class' => $class,
            'allReports' => $allReports
        ]);
    }

    // --- API ENDPOINTS ---

    private function apiRequireTeacher()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Class Teacher') {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            exit;
        }
    }

    public function apiMyClass()
    {
        $this->apiRequireTeacher();
        $teacher_id = $_SESSION['teacher_id'];

        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        if (!$class) {
            $this->jsonResponse(['error' => 'No class assigned to you.'], 404);
            return;
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $studentStmt = $this->db->prepare("SELECT * FROM students WHERE current_class_id = :class_id AND status = 'active' ORDER BY surname, first_name ASC");
        $studentStmt->execute([':class_id' => $class->id]);
        $students = $studentStmt->fetchAll();

        // Calculate pending statuses
        if ($currentSession && $currentTerm) {
            $subStmt = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE class_id = ? AND session_id = ? AND term_id = ?");
            $subStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
            $totalSubjects = $subStmt->fetchColumn();

            foreach ($students as $student) {
                $resStmt = $this->db->prepare("SELECT id FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
                $resStmt->execute([$student->id, $currentSession->id, $currentTerm->id]);
                $resultRecord = $resStmt->fetch();

                $is_pending = true;
                if ($resultRecord) {
                    $itemStmt = $this->db->prepare("SELECT COUNT(*) FROM result_items WHERE result_id = ?");
                    $itemStmt->execute([$resultRecord->id]);
                    $itemCount = $itemStmt->fetchColumn();
                    if ($totalSubjects > 0 && $itemCount >= $totalSubjects) {
                        $is_pending = false;
                    } elseif ($totalSubjects == 0 && $itemCount > 0) {
                        $is_pending = false;
                    }
                }
                $student->is_pending = $is_pending;
            }
        }

        $this->jsonResponse([
            'class' => $class,
            'students' => $students,
            'currentSession' => $currentSession,
            'currentTerm' => $currentTerm
        ]);
    }

    public function apiStudentResults($id)
    {
        $this->apiRequireTeacher();
        $teacher_id = $_SESSION['teacher_id'];

        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        $stuStmt = $this->db->prepare("SELECT * FROM students WHERE id = ? AND current_class_id = ?");
        $stuStmt->execute([$id, $class->id]);
        $student = $stuStmt->fetch();

        if (!$student) {
            $this->jsonResponse(['error' => 'Student not found in your class'], 404);
            return;
        }

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $subStmt = $this->db->prepare("SELECT * FROM subjects WHERE class_id = ? AND session_id = ? AND term_id = ? ORDER BY name ASC");
        $subStmt->execute([$class->id, $currentSession->id, $currentTerm->id]);
        $subjects = $subStmt->fetchAll();

        $resStmt = $this->db->prepare("SELECT * FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
        $resStmt->execute([$id, $currentSession->id, $currentTerm->id]);
        $resultRecord = $resStmt->fetch();

        $resultItems = [];
        if ($resultRecord) {
            $itemStmt = $this->db->prepare("SELECT * FROM result_items WHERE result_id = ?");
            $itemStmt->execute([$resultRecord->id]);
            $items = $itemStmt->fetchAll();
            foreach ($items as $it) {
                $resultItems[$it->subject_id] = $it;
            }
        }

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        $this->jsonResponse([
            'student' => $student,
            'subjects' => $subjects,
            'resultRecord' => $resultRecord,
            'resultItems' => $resultItems,
            'gradingSystem' => $gradingSystem
        ]);
    }

    public function apiSaveStudentResults($id)
    {
        $this->apiRequireTeacher();
        $teacher_id = $_SESSION['teacher_id'];

        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :teacher_id LIMIT 1");
        $stmt->execute([':teacher_id' => $teacher_id]);
        $class = $stmt->fetch();

        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $data = json_decode(file_get_contents('php://input'), true);
        $scores = $data['scores'] ?? [];
        $remark = $data['class_teacher_remark'] ?? null;
        $ht_remark = $data['head_teacher_remark'] ?? null;
        $ht_name = $data['head_teacher_name'] ?? null;
        $attendance = $data['attendance'] ?? null;
        $resumption_date = $data['resumption_date'] ?? null;
        $past_balance = $data['past_balance'] ?? 0;
        $next_term_fee = $data['next_term_fee'] ?? 0;

        $gradeStmt = $this->db->query("SELECT * FROM grading_system ORDER BY min_score DESC");
        $gradingSystem = $gradeStmt->fetchAll();

        $this->db->beginTransaction();
        try {
            $resStmt = $this->db->prepare("SELECT id FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
            $resStmt->execute([$id, $currentSession->id, $currentTerm->id]);
            $result_id = $resStmt->fetchColumn();

            if (!$result_id) {
                $insertRes = $this->db->prepare("INSERT INTO results (student_id, class_id, session_id, term_id, status, class_teacher_remark, head_teacher_remark, head_teacher_name, attendance, resumption_date, past_balance, next_term_fee) VALUES (?, ?, ?, ?, 'OPEN', ?, ?, ?, ?, ?, ?, ?)");
                $insertRes->execute([$id, $class->id, $currentSession->id, $currentTerm->id, $remark, $ht_remark, $ht_name, $attendance, $resumption_date, $past_balance, $next_term_fee]);
                $result_id = $this->db->lastInsertId();
            } else {
                $updateRes = $this->db->prepare("UPDATE results SET class_teacher_remark = ?, head_teacher_remark = ?, head_teacher_name = ?, attendance = ?, resumption_date = ?, past_balance = ?, next_term_fee = ? WHERE id = ?");
                $updateRes->execute([$remark, $ht_remark, $ht_name, $attendance, $resumption_date, $past_balance, $next_term_fee, $result_id]);
            }

            foreach ($scores as $subject_id => $scoreData) {
                $ca1 = empty($scoreData['ca1']) ? 0 : (float)$scoreData['ca1'];
                $ca2 = empty($scoreData['ca2']) ? 0 : (float)$scoreData['ca2'];
                $exam = empty($scoreData['exam']) ? 0 : (float)$scoreData['exam'];
                $total = $ca1 + $ca2 + $exam;

                $grade = '';
                $rem = '';
                foreach ($gradingSystem as $g) {
                    if ($total >= $g->min_score && $total <= $g->max_score) {
                        $grade = $g->grade;
                        $rem = $g->remark;
                        break;
                    }
                }

                $itemStmt = $this->db->prepare("SELECT id FROM result_items WHERE result_id = ? AND subject_id = ?");
                $itemStmt->execute([$result_id, $subject_id]);
                $item_id = $itemStmt->fetchColumn();

                if ($item_id) {
                    $updateItem = $this->db->prepare("UPDATE result_items SET ca1 = ?, ca2 = ?, exam = ?, total = ?, grade = ?, remark = ? WHERE id = ?");
                    $updateItem->execute([$ca1, $ca2, $exam, $total, $grade, $rem, $item_id]);
                } else {
                    $insertItem = $this->db->prepare("INSERT INTO result_items (result_id, subject_id, ca1, ca2, exam, total, grade, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $insertItem->execute([$result_id, $subject_id, $ca1, $ca2, $exam, $total, $grade, $rem]);
                }
            }
            $this->db->commit();
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->jsonResponse(['error' => 'Failed to save scores'], 500);
        }
    }

    public function apiDeleteResult($id)
    {
        $this->apiRequireTeacher();
        
        $sessionStmt = $this->db->query("SELECT * FROM sessions WHERE is_current = 1 LIMIT 1");
        $currentSession = $sessionStmt->fetch();

        $termStmt = $this->db->query("SELECT * FROM terms WHERE is_current = 1 LIMIT 1");
        $currentTerm = $termStmt->fetch();

        $stmt = $this->db->prepare("DELETE FROM results WHERE student_id = ? AND session_id = ? AND term_id = ?");
        $stmt->execute([$id, $currentSession->id, $currentTerm->id]);

        $this->jsonResponse(['success' => true]);
    }
}



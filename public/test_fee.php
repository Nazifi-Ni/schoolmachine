<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

// Get the latest enrolled student
$stmt = $db->query("SELECT id FROM students ORDER BY id DESC LIMIT 1");
$student = $stmt->fetch(\PDO::FETCH_ASSOC);
$studentId = $student['id'];

// Get active session
$sessStmt = $db->query("SELECT id FROM sessions WHERE is_current = 1 LIMIT 1");
$session = $sessStmt->fetch(\PDO::FETCH_ASSOC);
$sessionId = $session['id'];

// Insert fee
try {
    $db->prepare("INSERT INTO student_fees (student_id, session_id, total_amount, paid_amount, status) VALUES (?, ?, 50000.00, 0, 'Unpaid')")
       ->execute([$studentId, $sessionId]);
    echo "Fee record generated for student $studentId\n";
} catch (\Exception $e) {
    echo "Already exists or error: " . $e->getMessage() . "\n";
}

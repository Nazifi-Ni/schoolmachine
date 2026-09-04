<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

$studentId = 929;

// Get student basic details
$stmt = $db->prepare("
    SELECT s.first_name, s.surname, s.registration_number, c.name as class_name 
    FROM students s 
    LEFT JOIN classes c ON s.current_class_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$studentId]);
$profile = $stmt->fetch(\PDO::FETCH_ASSOC);

// Get current session/term
$sessStmt = $db->query("
    SELECT s.name as session_name, t.name as term_name, s.id as session_id, t.id as term_id
    FROM current_session_term cst
    JOIN sessions s ON cst.session_id = s.id
    JOIN terms t ON cst.term_id = t.id
    LIMIT 1
");
$currentSession = $sessStmt->fetch(\PDO::FETCH_ASSOC);

$fee = null;
if ($currentSession) {
    $sessionId = $currentSession['session_id'];
    $feeStmt = $db->prepare("SELECT total_amount, paid_amount, status FROM student_fees WHERE student_id = ? AND session_id = ?");
    $feeStmt->execute([$studentId, $sessionId]);
    $fee = $feeStmt->fetch(\PDO::FETCH_ASSOC);
}

// Get results
$resStmt = $db->prepare("
    SELECT r.id, s.name as session_name, t.name as term_name, c.name as class_name, r.status
    FROM results r
    JOIN sessions s ON r.session_id = s.id
    JOIN terms t ON r.term_id = t.id
    JOIN classes c ON r.class_id = c.id
    WHERE r.student_id = ?
    ORDER BY s.id DESC, t.id DESC
");
$resStmt->execute([$studentId]);
$results = $resStmt->fetchAll(\PDO::FETCH_ASSOC);

echo json_encode([
    'profile' => $profile,
    'current_session' => $currentSession,
    'fee' => $fee,
    'results' => $results
]);

<?php
require_once __DIR__ . '/../app/Config/Database.php';
use App\Config\Database;

$db = (new Database())->getConnection();

$sessStmt = $db->query("SELECT id FROM sessions WHERE is_current = 1 LIMIT 1");
$sessionId = $sessStmt->fetchColumn();

$termStmt = $db->query("SELECT id FROM terms WHERE is_current = 1 LIMIT 1");
$termId = $termStmt->fetchColumn();

echo "Session ID: " . $sessionId . "\n";
echo "Term ID: " . $termId . "\n";

$stmt = $db->prepare("
    SELECT s.id as student_id, s.first_name, s.surname, s.registration_number, c.name as class_name,
           sf.id as fee_id, sf.total_amount, sf.paid_amount, sf.status
    FROM students s
    JOIN classes c ON s.current_class_id = c.id
    LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.session_id = ? AND sf.term_id = ?
    WHERE s.status = 'active'
    ORDER BY c.name, s.surname
");
$stmt->execute([$sessionId, $termId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($students) . " active students.\n";
if (count($students) > 0) {
    echo "First student: " . json_encode($students[0]) . "\n";
}

$statsStmt = $db->prepare("
    SELECT 
        SUM(total_amount) as expected,
        SUM(paid_amount) as collected
    FROM student_fees WHERE session_id = ? AND term_id = ?
");
$statsStmt->execute([$sessionId, $termId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

echo "Stats: " . json_encode($stats) . "\n";

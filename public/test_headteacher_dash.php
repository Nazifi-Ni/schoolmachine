<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

$stats = [];
$stats['total_students'] = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$stats['total_teachers'] = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$stats['total_classes'] = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$stats['total_revenue'] = $db->query("SELECT SUM(paid_amount) FROM student_fees")->fetchColumn() ?? 0;

echo json_encode(['stats' => $stats]);

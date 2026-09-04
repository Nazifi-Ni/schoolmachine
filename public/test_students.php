<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

$query = "SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.current_class_id = c.id";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(\PDO::FETCH_ASSOC);

print_r($students);

<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

$studentId = 929; // a valid student id

// Get student basic details and class
$stmt = $db->prepare("
    SELECT s.first_name, s.surname, s.registration_number, c.name as class_name 
    FROM students s 
    LEFT JOIN classes c ON s.current_class_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$studentId]);
$profile = $stmt->fetch(\PDO::FETCH_ASSOC);

echo "Profile: \n";
print_r($profile);

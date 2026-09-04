<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();
$stmt = $db->prepare('SELECT first_name FROM students WHERE id = 375');
$stmt->execute();
$profile = $stmt->fetch();
echo json_encode(['profile' => $profile]);

<?php
require 'vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();
$stmt = $db->query('SELECT * FROM classes');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

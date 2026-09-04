<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();
$stmt = $db->query("SELECT id, first_name, status FROM admission_applications");
echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));

<?php
require '../vendor/autoload.php';
$db = (new App\Config\Database())->getConnection();

// Let's get the pending application ID
$stmt = $db->query("SELECT id FROM admission_applications WHERE status = 'pending' LIMIT 1");
$app = $stmt->fetch();
if(!$app) { echo "No pending applications.\n"; exit; }
$id = $app['id'];

echo "Approving application ID: $id\n";

try {
    $stmt = $db->prepare("SELECT * FROM admission_applications WHERE id = ?");
    $stmt->execute([$id]);
    $app = $stmt->fetch();

    $regNo = 'IAMS/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $classId = 1;

    $insert = $db->prepare("
        INSERT INTO students (first_name, surname, middle_name, gender, dob, parent_name, phone, address, registration_number, current_class_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $insert->execute([
        $app['first_name'],
        $app['surname'],
        $app['middle_name'],
        $app['gender'],
        $app['date_of_birth'],
        $app['guardian_name'],
        $app['guardian_phone'],
        $app['address'],
        $regNo,
        $classId
    ]);
    
    echo "Inserted into students!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

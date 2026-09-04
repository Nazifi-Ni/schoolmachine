<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/Database.php';

try {
    $db = (new App\Config\Database())->getConnection();

    // The class IDs in descending order, EXCLUDING Primary 6 (ID: 8)
    $classOrder = [
        7, // Primary 5
        6, 15, 17, 19, // Primary 4
        5, // Primary 3
        4, 16, // Primary 2
        10, 13, // Primary 1
        2, 11, // Nursery 2
        1, 12 // Nursery 1
    ];

    $counter = 1;
    foreach ($classOrder as $classId) {
        $stmt = $db->prepare("SELECT id FROM students WHERE current_class_id = :class_id AND status = 'active' ORDER BY id ASC");
        $stmt->execute([':class_id' => $classId]);
        $students = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($students as $student) {
            $regNumber = 'IAMS/2026/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            
            $updateStmt = $db->prepare("UPDATE students SET registration_number = :reg WHERE id = :id");
            $updateStmt->execute([
                ':reg' => $regNumber,
                ':id' => $student->id
            ]);
            
            echo "Assigned $regNumber to Student ID {$student->id}\n";
            $counter++;
        }
    }
    echo "Done! Total assigned: " . ($counter - 1) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/Database.php";

$db = (new App\Config\Database())->getConnection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("POST only");
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!$data) {
    die("Invalid JSON");
}

$tables = ["roles", "users", "sessions", "terms", "classes", "teachers", "students", "subjects", "grading_system", "results", "result_items", "fee_structures", "student_fees", "fee_payments"];

try {
    $db->beginTransaction();
    
    // Clear existing data to avoid conflicts with IDs
    $tableList = implode(", ", $tables);
    $db->exec("TRUNCATE $tableList CASCADE");
    
    foreach ($tables as $table) {
        if (empty($data[$table])) continue;
        
        foreach ($data[$table] as $row) {
            // Convert boolean fields for PostgreSQL
            foreach (["is_current", "is_core"] as $boolField) {
                if (isset($row[$boolField])) {
                    $row[$boolField] = $row[$boolField] ? "TRUE" : "FALSE";
                }
            }
            // Ignore admission_date as it doesn't exist in postgres schema
            if (isset($row["admission_date"])) {
                unset($row["admission_date"]);
            }
            
            $columns = array_keys($row);
            $values = array_values($row);
            
            $placeholders = implode(", ", array_fill(0, count($columns), "?"));
            $colNames = implode(", ", $columns);
            
            $sql = "INSERT INTO $table ($colNames) VALUES ($placeholders)";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
        }
        
        // Reset sequence so auto-increment works for new inserts
        $db->exec("SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE(MAX(id), 1)) FROM $table");
    }
    
    $db->commit();
    echo "Migration successful!";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}


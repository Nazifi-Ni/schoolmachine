<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/Database.php";

$db = (new App\Config\Database())->getConnection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("POST only");
}

$json = file_get_contents("php://input");
$payload = json_decode($json, true);

if (!$payload || !isset($payload["table"]) || !isset($payload["rows"])) {
    die("Invalid JSON payload");
}

$table = $payload["table"];
$rows = $payload["rows"];
$clear = $payload["clear"] ?? false;

try {
    $db->beginTransaction();
    
    if ($clear) {
        $db->exec("TRUNCATE $table CASCADE");
    }
    
    if (empty($rows)) {
        $db->commit();
        echo "Cleared $table";
        exit;
    }

    $stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = '$table' AND table_schema = 'public'");
    $validColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($rows as $row) {
        if ($table === "teachers" && isset($row["last_name"])) {
            $row["surname"] = $row["last_name"];
        }
        
        $filteredRow = [];
        foreach ($row as $key => $value) {
            if (in_array($key, $validColumns)) {
                $filteredRow[$key] = $value;
            }
        }
        
        foreach (["is_current", "is_core"] as $boolField) {
            if (isset($filteredRow[$boolField])) {
                $filteredRow[$boolField] = $filteredRow[$boolField] ? "TRUE" : "FALSE";
            }
        }
        
        $columns = array_keys($filteredRow);
        $values = array_values($filteredRow);
        
        $placeholders = implode(", ", array_fill(0, count($columns), "?"));
        $colNames = implode(", ", $columns);
        
        $sql = "INSERT INTO $table ($colNames) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
    }
    
    $db->exec("SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE(MAX(id), 1)) FROM $table");
    
    $db->commit();
    echo "Inserted " . count($rows) . " rows into $table";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}


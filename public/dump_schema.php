<?php
$conn = new PDO("mysql:host=localhost;dbname=iams_arms", "root", "");
$stmt = $conn->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "TABLE: $table\n";
    $stmt2 = $conn->query("DESCRIBE $table");
    $cols = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
}

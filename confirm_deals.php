<?php
require_once 'connection.php';
$table = 'deals';
$stmt = $conn->query("SHOW COLUMNS FROM $table");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Columns in $table:\n";
foreach($cols as $col) {
    echo "- " . $col['Field'] . "\n";
}

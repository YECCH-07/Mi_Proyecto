<?php
include 'config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query('DESCRIBE evidencias');
echo "\nEstructura de la tabla 'evidencias':\n";
echo str_repeat("-", 50) . "\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

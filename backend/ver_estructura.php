<?php
include 'config/database.php';

$db = (new Database())->getConnection();

echo "ESTRUCTURA TABLA DENUNCIAS:\n";
echo str_repeat("=", 60) . "\n";
$stmt = $db->query('DESCRIBE denuncias');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-25s %-20s %s\n", $row['Field'], $row['Type'], $row['Null']);
}

echo "\n\nESTRUCTURA TABLA USUARIOS:\n";
echo str_repeat("=", 60) . "\n";
$stmt = $db->query('DESCRIBE usuarios');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-25s %-20s %s\n", $row['Field'], $row['Type'], $row['Null']);
}

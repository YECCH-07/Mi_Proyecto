<?php
$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== ESTRUCTURA DE LA TABLA DENUNCIAS ===\n\n";
$stmt = $conn->query("DESCRIBE denuncias");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']} {$row['Default']}\n";
}
echo "\n";

echo "=== ESTRUCTURA DE LA TABLA USUARIOS ===\n\n";
$stmt = $conn->query("DESCRIBE usuarios");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']} {$row['Default']}\n";
}

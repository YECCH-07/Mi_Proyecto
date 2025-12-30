<?php
echo "=== VERIFICANDO OPERADORES Y SUS ÁREAS ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');

// Ver operadores
echo "OPERADORES:\n";
$stmt = $conn->query("SELECT id, nombres, apellidos, email, rol, area_id FROM usuarios WHERE rol = 'operador'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $area = $row['area_id'] ? $row['area_id'] : 'NULL (⚠️ SIN ÁREA)';
    echo "  ID: {$row['id']} | {$row['nombres']} {$row['apellidos']} | Email: {$row['email']} | Área: $area\n";
}

echo "\n\nÁREAS DISPONIBLES:\n";
$stmt = $conn->query("SELECT id, nombre FROM areas_municipales");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$row['id']} | {$row['nombre']}\n";
}

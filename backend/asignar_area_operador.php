<?php
echo "=== ASIGNANDO ÁREA A OPERADOR ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');

// Asignar área 1 (Gestión Ambiental) a Elena
$stmt = $conn->prepare("UPDATE usuarios SET area_id = ? WHERE email = ?");
$stmt->execute([1, 'elena.op@muni.gob.pe']);

echo "✅ Área asignada a Elena Operadora\n";
echo "   Área: 1 - Gerencia de Gestión Ambiental\n\n";

// Verificar
$stmt = $conn->query("SELECT u.nombres, u.apellidos, u.area_id, a.nombre as area_nombre
                       FROM usuarios u
                       LEFT JOIN areas_municipales a ON u.area_id = a.id
                       WHERE u.email = 'elena.op@muni.gob.pe'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "VERIFICACIÓN:\n";
echo "  Usuario: {$row['nombres']} {$row['apellidos']}\n";
echo "  Área ID: {$row['area_id']}\n";
echo "  Área Nombre: {$row['area_nombre']}\n";

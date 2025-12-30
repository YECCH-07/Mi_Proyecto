<?php
echo "=== ASIGNANDO DENUNCIAS AL ÁREA 1 ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');

// Asignar las primeras 3 denuncias al área 1 y cambiar su estado
$stmt = $conn->query("SELECT id, codigo, titulo, estado FROM denuncias ORDER BY id DESC LIMIT 5");
$denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Asignando denuncias al Área 1 (Gestión Ambiental):\n\n";

$count = 0;
foreach($denuncias as $d) {
    if($count >= 3) break;

    // Asignar al área 1 y poner en estado 'asignada' o 'en_proceso'
    $nuevoEstado = ($count == 0) ? 'en_proceso' : 'asignada';

    $update = $conn->prepare("UPDATE denuncias SET area_asignada_id = ?, estado = ? WHERE id = ?");
    $update->execute([1, $nuevoEstado, $d['id']]);

    echo "✅ ID: {$d['id']} | {$d['codigo']} | {$d['titulo']}\n";
    echo "   Estado: {$d['estado']} → $nuevoEstado\n";
    echo "   Área: NULL → 1 (Gestión Ambiental)\n\n";

    $count++;
}

echo "\n";
echo "RESUMEN:\n";
echo "  ✅ $count denuncias asignadas al área 1\n";
echo "  ✅ Elena ahora puede ver estas denuncias\n";

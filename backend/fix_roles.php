<?php
echo "=== CORRIGIENDO ROLES DE USUARIOS ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');

// Actualizar roles correctos
$updates = [
    ['email' => 'admin@muni.gob.pe', 'rol' => 'admin'],
    ['email' => 'carlos.sup@muni.gob.pe', 'rol' => 'supervisor'],
    ['email' => 'elena.op@muni.gob.pe', 'rol' => 'operador'],
    ['email' => 'juan.perez@mail.com', 'rol' => 'ciudadano']
];

foreach($updates as $update) {
    $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE email = ?");
    $stmt->execute([$update['rol'], $update['email']]);
    echo "✓ {$update['email']} -> rol: {$update['rol']}\n";
}

echo "\n✓ Roles actualizados correctamente\n";

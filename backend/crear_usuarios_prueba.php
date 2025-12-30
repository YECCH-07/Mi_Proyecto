<?php
// Crear/actualizar usuarios con contraseñas conocidas para pruebas
echo "=== CREANDO USUARIOS DE PRUEBA ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$testUsers = [
    [
        'email' => 'admin@muni.gob.pe',
        'password' => 'admin123',
        'rol' => 'admin'
    ],
    [
        'email' => 'carlos.sup@muni.gob.pe',
        'password' => 'carlos123',
        'rol' => 'supervisor'
    ],
    [
        'email' => 'elena.op@muni.gob.pe',
        'password' => 'elena123',
        'rol' => 'operador'
    ],
    [
        'email' => 'juan.perez@mail.com',
        'password' => 'juan123',
        'rol' => 'ciudadano'
    ]
];

foreach($testUsers as $user) {
    // Hash password
    $passwordHash = password_hash($user['password'], PASSWORD_BCRYPT, ['cost' => 12]);

    // Update password
    $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ? WHERE email = ?");
    $stmt->execute([$passwordHash, $user['email']]);

    echo "✓ Actualizada contraseña para: {$user['email']} (password: {$user['password']})\n";
}

echo "\n=== CREDENCIALES DE PRUEBA ===\n\n";
foreach($testUsers as $user) {
    echo "Email: {$user['email']}\n";
    echo "Password: {$user['password']}\n";
    echo "Rol: {$user['rol']}\n";
    echo "---\n";
}

echo "\n✓ Usuarios actualizados correctamente\n";

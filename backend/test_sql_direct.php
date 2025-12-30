<?php
echo "=== PRUEBA SQL DIRECTA ===\n\n";

$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT
            d.id,
            d.codigo,
            d.titulo,
            d.descripcion,
            d.estado,
            d.created_at as fecha_registro,
            d.latitud,
            d.longitud,
            d.direccion_referencia,
            d.es_anonima,
            d.usuario_id,
            d.categoria_id,
            d.area_asignada_id,
            CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
            u.email as usuario_email,
            u.telefono as usuario_telefono,
            c.nombre as categoria_nombre,
            c.icono as categoria_icono,
            a.nombre as area_nombre,
            a.responsable as area_responsable
        FROM
            denuncias d
            LEFT JOIN usuarios u ON d.usuario_id = u.id
            INNER JOIN categorias c ON d.categoria_id = c.id
            LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
        ORDER BY
            d.created_at DESC
        LIMIT 5";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo "✓ Consulta ejecutada\n\n";

    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total: " . count($denuncias) . "\n\n";

    foreach($denuncias as $d) {
        echo "ID: {$d['id']}\n";
        echo "Código: {$d['codigo']}\n";
        echo "Título: {$d['titulo']}\n";
        echo "Usuario: {$d['usuario_nombre']}\n";
        echo "Categoría: {$d['categoria_nombre']}\n";
        echo "---\n";
    }

} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN ===\n";

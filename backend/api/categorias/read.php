<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Categoria.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate categoria object
$categoria = new Categoria($db);

// Read all categories
$stmt = $categoria->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $categorias_arr = array();
    $categorias_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $categoria_item = array(
            "id" => $id,
            "nombre" => $nombre,
            "descripcion" => $descripcion,
            "icono" => $icono
        );
        array_push($categorias_arr["records"], $categoria_item);
    }
    http_response_code(200);
    echo json_encode($categorias_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No categories found."));
}

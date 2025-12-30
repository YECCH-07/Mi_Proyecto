<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Evidencia.php';
include_once '../../middleware/validate_jwt.php';

// Validate JWT
$user_data = validate_jwt();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate evidencia object
$evidencia = new Evidencia($db);

// Check if file was uploaded
if (isset($_FILES['file']) && isset($_POST['denuncia_id'])) {
    
    $denuncia_id = $_POST['denuncia_id'];
    $file = $_FILES['file'];

    // File properties
    $filename = $file['name'];
    $filesize = $file['size'];
    $file_tmp_name = $file['tmp_name'];
    $file_error = $file['error'];

    // Get file extension
    $file_ext = explode('.', $filename);
    $file_actual_ext = strtolower(end($file_ext));

    // Allowed extensions
    $allowed = array('jpg', 'jpeg', 'png', 'mp4', 'pdf');

    if (in_array($file_actual_ext, $allowed)) {
        if ($file_error === 0) {
            // Max file size (e.g., 50MB)
            if ($filesize < 50000000) {
                // Create unique filename
                $new_filename = uniqid('', true) . "." . $file_actual_ext;
                $target_dir = "../../uploads/";
                $target_file = $target_dir . $new_filename;

                // Create uploads directory if it doesn't exist
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp_name, $target_file)) {
                    // Set evidencia properties
                    $evidencia->denuncia_id = $denuncia_id;
                    $evidencia->archivo_url = $new_filename; // Store only the filename

                    // Determine file type
                    $image_types = array('jpg', 'jpeg', 'png');
                    $video_types = array('mp4');
                    if (in_array($file_actual_ext, $image_types)) {
                        $evidencia->tipo = 'imagen';
                    } elseif (in_array($file_actual_ext, $video_types)) {
                        $evidencia->tipo = 'video';
                    } else {
                        $evidencia->tipo = 'documento';
                    }

                    // Create evidencia record
                    if ($evidencia->create()) {
                        http_response_code(201);
                        echo json_encode(array("message" => "File uploaded successfully."));
                    } else {
                        http_response_code(503);
                        echo json_encode(array("message" => "Failed to save file record to database."));
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Failed to move uploaded file."));
                }
            } else {
                http_response_code(413); // Payload Too Large
                echo json_encode(array("message" => "File is too large. Max 50MB."));
            }
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "There was an error uploading your file."));
        }
    } else {
        http_response_code(415); // Unsupported Media Type
        echo json_encode(array("message" => "File type not allowed."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "File or denuncia_id not provided."));
}

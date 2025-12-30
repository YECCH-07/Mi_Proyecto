<?php
// Set your allowed origins
$allowed_origins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}

// Required headers for PDF output
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"reporte_denuncias.pdf\"");

// Handle potential OPTIONS request for preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    http_response_code(200);
    exit();
}

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../middleware/validate_jwt.php';

// Validate JWT and user role
$user_data = validate_jwt(['admin', 'supervisor']); // Only admin and supervisor can generate reports

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate denuncia object
$denuncia = new Denuncia($db);

// Get all denuncias
$stmt = $denuncia->read(); // Using the existing read method for all denuncias
$num = $stmt->rowCount();

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Denuncias</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; color: #9C221C; }
        .footer { text-align: center; font-size: 8pt; color: #555; position: fixed; bottom: 0; width: 100%; }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 5px;
            font-size: 8pt;
            font-weight: bold;
            color: #fff; /* Default to white for better contrast */
        }
        .status-registrada { background-color: #36a2eb; } /* blue */
        .status-en_revision { background-color: #ffcd56; } /* yellow */
        .status-asignada { background-color: #6a0dad; } /* purple */
        .status-en_proceso { background-color: #ff7f00; } /* orange */
        .status-resuelta { background-color: #28a745; } /* green */
        .status-cerrada { background-color: #6c757d; } /* grey */
        .status-rechazada { background-color: #dc3545; } /* red */
    </style>
</head>
<body>
    <h1>Reporte de Denuncias Ciudadanas</h1>
    <p>Fecha de Generación: ' . date('d/m/Y H:i:s') . '</p>
';

if ($num > 0) {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Título</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
    ';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        // Replace spaces in status for CSS class
        $status_class = 'status-' . str_replace(' ', '_', $estado);
        $html .= '
            <tr>
                <td>' . htmlspecialchars($codigo) . '</td>
                <td>' . htmlspecialchars($titulo) . '</td>
                <td>' . htmlspecialchars($usuario_nombre) . '</td>
                <td><span class="status-badge ' . $status_class . '">' . htmlspecialchars($estado) . '</span></td>
                <td>' . htmlspecialchars($created_at) . '</td>
            </tr>
        ';
    }

    $html .= '
        </tbody>
    </table>
    ';
} else {
    $html .= '<p>No se encontraron denuncias para generar el reporte.</p>';
}

$html .= '
    <div class="footer">
        Reporte generado por el Sistema de Denuncia Ciudadana | UNSAAC 2025
    </div>
</body>
</html>';

// Setup Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable remote images if any
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("reporte_denuncias.pdf", array("Attachment" => true));

exit();


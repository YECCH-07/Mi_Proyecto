<?php
/**
 * SCRIPT DE VERIFICACIÓN DE INTEGRIDAD DEL SISTEMA
 *
 * Ejecutar desde navegador: http://localhost/DENUNCIA%20CIUDADANA/backend/verificar_sistema.php
 * O desde línea de comandos: php verificar_sistema.php
 */

// Establecer tipo de contenido
header('Content-Type: text/html; charset=utf-8');

// Incluir configuración
include_once 'config/database.php';

// Estilos CSS para el reporte
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - Denuncia Ciudadana</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-body {
            padding: 20px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 6px;
            background: #f8f9fa;
        }
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            font-size: 18px;
        }
        .check-label {
            flex: 1;
            font-weight: 500;
        }
        .check-value {
            color: #666;
            font-size: 14px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            text-align: center;
        }
        .summary-card.success {
            border-color: #28a745;
            background: #d4edda;
        }
        .summary-card.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .summary-card.warning {
            border-color: #ffc107;
            background: #fff3cd;
        }
        .summary-number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .summary-label {
            font-size: 14px;
            color: #666;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .sql-code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }
        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #721c24;
            color: #721c24;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificación de Integridad del Sistema</h1>
            <p>Sistema de Denuncia Ciudadana - Diagnóstico Completo</p>
        </div>

        <div class="content">
            <?php
            $total_checks = 0;
            $passed_checks = 0;
            $failed_checks = 0;
            $warning_checks = 0;

            // =================================================================
            // 1. VERIFICAR CONEXIÓN A BASE DE DATOS
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">🗄️ Conexión a Base de Datos</div>';
            echo '<div class="section-body">';

            $database = new Database();
            $db = $database->getConnection();

            if ($db) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Conexión a MySQL establecida</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Error de conexión a MySQL</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>CRÍTICO:</strong> No se pudo conectar a la base de datos. Verifica XAMPP y la configuración en .env';
                echo '</div>';
                $failed_checks++;
                echo '</div></div>';
                echo '</div></body></html>';
                exit;
            }
            $total_checks++;

            echo '</div></div>';

            // =================================================================
            // 2. VERIFICAR ESTRUCTURA DE TABLAS
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">📋 Estructura de Tablas</div>';
            echo '<div class="section-body">';

            // Verificar tabla usuarios tiene area_id
            $query = "SHOW COLUMNS FROM usuarios LIKE 'area_id'";
            $stmt = $db->query($query);
            $total_checks++;
            if ($stmt->rowCount() > 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Columna <code>usuarios.area_id</code> existe</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Columna <code>usuarios.area_id</code> NO existe</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>ACCIÓN REQUERIDA:</strong> Ejecutar en MySQL:<br>';
                echo '<div class="sql-code">ALTER TABLE usuarios ADD COLUMN area_id INT DEFAULT NULL AFTER rol;</div>';
                echo '</div>';
                $failed_checks++;
            }

            // Verificar tabla categorias tiene area_id
            $query = "SHOW COLUMNS FROM categorias LIKE 'area_id'";
            $stmt = $db->query($query);
            $total_checks++;
            if ($stmt->rowCount() > 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Columna <code>categorias.area_id</code> existe</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Columna <code>categorias.area_id</code> NO existe</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>ACCIÓN REQUERIDA:</strong> Ejecutar en MySQL:<br>';
                echo '<div class="sql-code">ALTER TABLE categorias ADD COLUMN area_id INT DEFAULT NULL AFTER descripcion;</div>';
                echo '</div>';
                $failed_checks++;
            }

            // Verificar tabla logs_auditoria
            $query = "SHOW TABLES LIKE 'logs_auditoria'";
            $stmt = $db->query($query);
            $total_checks++;
            if ($stmt->rowCount() > 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Tabla <code>logs_auditoria</code> existe</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Tabla <code>logs_auditoria</code> NO existe</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>ACCIÓN REQUERIDA:</strong> Ejecutar script en MySQL:<br>';
                echo 'Ver archivo: <code>backend/MODIFICACIONES_INCREMENTALES.sql</code>';
                echo '</div>';
                $failed_checks++;
            }

            echo '</div></div>';

            // =================================================================
            // 3. VERIFICAR TRIGGERS
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">⚙️ Triggers de Base de Datos</div>';
            echo '<div class="section-body">';

            $query = "SHOW TRIGGERS WHERE `Trigger` = 'tr_denuncias_asignar_area'";
            $stmt = $db->query($query);
            $total_checks++;
            if ($stmt->rowCount() > 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Trigger <code>tr_denuncias_asignar_area</code> existe</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Trigger <code>tr_denuncias_asignar_area</code> NO existe</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>ACCIÓN REQUERIDA:</strong> Este trigger asigna automáticamente el área a las denuncias.<br>';
                echo 'Ejecutar script: <code>backend/MODIFICACIONES_INCREMENTALES.sql</code>';
                echo '</div>';
                $failed_checks++;
            }

            echo '</div></div>';

            // =================================================================
            // 4. VERIFICAR DATOS DE CONFIGURACIÓN
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">🎯 Configuración de Datos</div>';
            echo '<div class="section-body">';

            // Verificar que las categorías tengan área asignada
            $query = "SELECT COUNT(*) as total,
                      SUM(CASE WHEN area_id IS NULL THEN 1 ELSE 0 END) as sin_area
                      FROM categorias";
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_checks++;

            if ($result['sin_area'] == 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Todas las categorías tienen área asignada</span>';
                echo '<span class="check-value">(' . $result['total'] . ' categorías)</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item warning">';
                echo '<span class="icon">⚠️</span>';
                echo '<span class="check-label">Hay categorías sin área asignada</span>';
                echo '<span class="check-value">(' . $result['sin_area'] . ' de ' . $result['total'] . ')</span>';
                echo '</div>';
                echo '<div class="alert alert-info">';
                echo '<strong>RECOMENDACIÓN:</strong> Asignar áreas a las categorías:<br>';
                echo '<div class="sql-code">UPDATE categorias SET area_id = 1 WHERE nombre LIKE \'%basura%\';</div>';
                echo 'Ajusta según tus áreas municipales.';
                echo '</div>';
                $warning_checks++;
            }

            // Verificar operadores con área asignada
            $query = "SELECT COUNT(*) as total
                      FROM usuarios
                      WHERE rol = 'operador' AND area_id IS NULL";
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_checks++;

            if ($result['total'] == 0) {
                echo '<div class="check-item success">';
                echo '<span class="icon">✅</span>';
                echo '<span class="check-label">Todos los operadores tienen área asignada</span>';
                echo '</div>';
                $passed_checks++;
            } else {
                echo '<div class="check-item error">';
                echo '<span class="icon">❌</span>';
                echo '<span class="check-label">Hay operadores sin área asignada</span>';
                echo '<span class="check-value">(' . $result['total'] . ' operadores)</span>';
                echo '</div>';
                echo '<div class="alert alert-danger">';
                echo '<strong>CRÍTICO:</strong> Los operadores DEBEN tener área asignada.<br>';
                echo 'Asignar desde el panel de administración o ejecutar:<br>';
                echo '<div class="sql-code">UPDATE usuarios SET area_id = 1 WHERE rol = \'operador\' AND id = X;</div>';
                echo '</div>';
                $failed_checks++;
            }

            echo '</div></div>';

            // =================================================================
            // 5. VERIFICAR ARCHIVOS CRÍTICOS
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">📁 Archivos del Sistema</div>';
            echo '<div class="section-body">';

            $archivos_criticos = [
                'middleware/validate_jwt.php' => 'Validación JWT',
                'middleware/filter_by_area.php' => 'Filtrado por área',
                'api/usuarios/create.php' => 'Crear usuarios',
                'api/usuarios/read.php' => 'Listar usuarios',
                'api/usuarios/update.php' => 'Actualizar usuarios',
                'api/usuarios/delete.php' => 'Eliminar usuarios',
                'api/denuncias/read.php' => 'Listar denuncias',
                'api/denuncias/locations.php' => 'Coordenadas heatmap'
            ];

            foreach ($archivos_criticos as $archivo => $descripcion) {
                $ruta_completa = __DIR__ . '/' . $archivo;
                $total_checks++;
                if (file_exists($ruta_completa)) {
                    echo '<div class="check-item success">';
                    echo '<span class="icon">✅</span>';
                    echo '<span class="check-label">' . $descripcion . '</span>';
                    echo '<span class="check-value"><code>' . $archivo . '</code></span>';
                    echo '</div>';
                    $passed_checks++;
                } else {
                    echo '<div class="check-item error">';
                    echo '<span class="icon">❌</span>';
                    echo '<span class="check-label">' . $descripcion . '</span>';
                    echo '<span class="check-value"><code>' . $archivo . '</code> NO EXISTE</span>';
                    echo '</div>';
                    $failed_checks++;
                }
            }

            echo '</div></div>';

            // =================================================================
            // 6. VERIFICAR ESTADÍSTICAS DEL SISTEMA
            // =================================================================
            echo '<div class="section">';
            echo '<div class="section-header">📊 Estadísticas del Sistema</div>';
            echo '<div class="section-body">';

            // Contar usuarios por rol
            $query = "SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol";
            $stmt = $db->query($query);
            echo '<h4 style="margin-bottom: 15px;">Usuarios por Rol:</h4>';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="check-item success">';
                echo '<span class="icon">👤</span>';
                echo '<span class="check-label">' . ucfirst($row['rol']) . '</span>';
                echo '<span class="check-value">' . $row['total'] . ' usuarios</span>';
                echo '</div>';
            }

            // Contar denuncias por estado
            $query = "SELECT estado, COUNT(*) as total FROM denuncias GROUP BY estado";
            $stmt = $db->query($query);
            echo '<h4 style="margin: 20px 0 15px;">Denuncias por Estado:</h4>';
            $has_denuncias = false;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $has_denuncias = true;
                echo '<div class="check-item success">';
                echo '<span class="icon">📝</span>';
                echo '<span class="check-label">' . ucfirst($row['estado']) . '</span>';
                echo '<span class="check-value">' . $row['total'] . ' denuncias</span>';
                echo '</div>';
            }

            if (!$has_denuncias) {
                echo '<div class="check-item warning">';
                echo '<span class="icon">⚠️</span>';
                echo '<span class="check-label">No hay denuncias registradas aún</span>';
                echo '</div>';
            }

            // Contar áreas municipales
            $query = "SELECT COUNT(*) as total FROM areas_municipales";
            $stmt = $db->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<h4 style="margin: 20px 0 15px;">Áreas Municipales:</h4>';
            echo '<div class="check-item success">';
            echo '<span class="icon">🏢</span>';
            echo '<span class="check-label">Total de áreas configuradas</span>';
            echo '<span class="check-value">' . $result['total'] . ' áreas</span>';
            echo '</div>';

            echo '</div></div>';

            ?>
        </div>

        <!-- RESUMEN FINAL -->
        <div class="content">
            <h2 style="margin-bottom: 20px; text-align: center;">📋 Resumen de Verificación</h2>
            <div class="summary">
                <div class="summary-card success">
                    <div class="summary-number"><?php echo $passed_checks; ?></div>
                    <div class="summary-label">Verificaciones Exitosas</div>
                </div>
                <div class="summary-card error">
                    <div class="summary-number"><?php echo $failed_checks; ?></div>
                    <div class="summary-label">Errores Detectados</div>
                </div>
                <div class="summary-card warning">
                    <div class="summary-number"><?php echo $warning_checks; ?></div>
                    <div class="summary-label">Advertencias</div>
                </div>
                <div class="summary-card">
                    <div class="summary-number"><?php echo $total_checks; ?></div>
                    <div class="summary-label">Total Verificaciones</div>
                </div>
            </div>

            <?php if ($failed_checks == 0 && $warning_checks == 0): ?>
                <div class="alert" style="background: #d4edda; border-left: 4px solid #28a745; color: #155724;">
                    <h3>✅ ¡Sistema Completamente Funcional!</h3>
                    <p>Todas las verificaciones pasaron exitosamente. El sistema está listo para usarse.</p>
                </div>
            <?php elseif ($failed_checks > 0): ?>
                <div class="alert alert-danger">
                    <h3>❌ Se Detectaron Errores Críticos</h3>
                    <p>Revisa los errores marcados arriba y ejecuta las acciones requeridas.</p>
                    <p><strong>Archivo SQL:</strong> <code>backend/MODIFICACIONES_INCREMENTALES.sql</code></p>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <h3>⚠️ Sistema Funcional con Advertencias</h3>
                    <p>El sistema funciona pero hay configuraciones recomendadas pendientes.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>Sistema de Denuncia Ciudadana - Verificación realizada el <?php echo date('d/m/Y H:i:s'); ?></p>
            <p style="margin-top: 10px; font-size: 12px;">Para soporte, revisa los archivos: SOLUCION_MYSQL_XAMPP.md y ANALISIS_IMPLEMENTACIONES.md</p>
        </div>
    </div>
</body>
</html>

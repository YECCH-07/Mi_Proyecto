-- ============================================================================
-- MODIFICACIONES INCREMENTALES - SIN MIGRACIÓN
-- Solo agrega columnas y triggers sobre la estructura existente
-- NO requiere migración de datos
-- ============================================================================

USE denuncia_ciudadana;

-- ----------------------------------------------------------------------------
-- PASO 1: Agregar columna area_id a tabla usuarios
-- ----------------------------------------------------------------------------

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS area_id INT DEFAULT NULL AFTER rol;

ALTER TABLE usuarios
ADD CONSTRAINT fk_usuarios_area
    FOREIGN KEY (area_id)
    REFERENCES areas_municipales(id)
    ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_usuarios_area ON usuarios(area_id);

SELECT '✅ Columna area_id agregada a tabla usuarios' AS status;

-- ----------------------------------------------------------------------------
-- PASO 2: Agregar columna area_id a tabla categorias
-- ----------------------------------------------------------------------------

ALTER TABLE categorias
ADD COLUMN IF NOT EXISTS area_id INT DEFAULT NULL AFTER descripcion;

-- Asignar áreas a categorías existentes
UPDATE categorias SET area_id = 1 WHERE nombre LIKE '%Basura%' OR nombre LIKE '%Limpieza%' OR nombre LIKE '%Parques%';
UPDATE categorias SET area_id = 2 WHERE nombre LIKE '%Baches%' OR nombre LIKE '%Pistas%' OR nombre LIKE '%Edificaciones%';
UPDATE categorias SET area_id = 3 WHERE nombre LIKE '%Seguridad%';
UPDATE categorias SET area_id = 1 WHERE nombre LIKE '%Contaminación%' OR nombre LIKE '%Ruido%';
UPDATE categorias SET area_id = 4 WHERE nombre LIKE '%Agua%' OR nombre LIKE '%Desagüe%' OR nombre LIKE '%Alumbrado%';
UPDATE categorias SET area_id = 5 WHERE nombre LIKE '%Transporte%';
UPDATE categorias SET area_id = 1 WHERE area_id IS NULL;

ALTER TABLE categorias
MODIFY COLUMN area_id INT NOT NULL;

ALTER TABLE categorias
ADD CONSTRAINT fk_categorias_area
    FOREIGN KEY (area_id)
    REFERENCES areas_municipales(id)
    ON DELETE RESTRICT;

CREATE INDEX IF NOT EXISTS idx_categorias_area ON categorias(area_id);

SELECT '✅ Columna area_id agregada a tabla categorias' AS status;

-- ----------------------------------------------------------------------------
-- PASO 3: Verificar columna area_asignada_id en denuncias
-- ----------------------------------------------------------------------------

ALTER TABLE denuncias
ADD COLUMN IF NOT EXISTS area_asignada_id INT DEFAULT NULL;

ALTER TABLE denuncias
ADD CONSTRAINT fk_denuncias_area
    FOREIGN KEY (area_asignada_id)
    REFERENCES areas_municipales(id)
    ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_denuncias_area ON denuncias(area_asignada_id);

SELECT '✅ Columna area_asignada_id verificada en denuncias' AS status;

-- ----------------------------------------------------------------------------
-- PASO 4: Crear TRIGGER de asignación automática
-- ----------------------------------------------------------------------------

DROP TRIGGER IF EXISTS tr_denuncias_asignar_area;

DELIMITER $$

CREATE TRIGGER tr_denuncias_asignar_area
BEFORE INSERT ON denuncias
FOR EACH ROW
BEGIN
    DECLARE area_id_var INT;

    SELECT area_id INTO area_id_var
    FROM categorias
    WHERE id = NEW.categoria_id;

    IF area_id_var IS NOT NULL THEN
        SET NEW.area_asignada_id = area_id_var;
    END IF;
END$$

DELIMITER ;

SELECT '✅ Trigger de asignación automática creado' AS status;

-- ----------------------------------------------------------------------------
-- PASO 5: Actualizar denuncias existentes
-- ----------------------------------------------------------------------------

UPDATE denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
SET d.area_asignada_id = c.area_id
WHERE d.area_asignada_id IS NULL;

SELECT '✅ Denuncias existentes actualizadas con área' AS status;

-- ----------------------------------------------------------------------------
-- PASO 6: Crear VISTA optimizada
-- ----------------------------------------------------------------------------

CREATE OR REPLACE VIEW v_denuncias_por_area AS
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at,
    d.updated_at,
    d.usuario_id,
    d.area_asignada_id,
    d.categoria_id,
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.es_anonima,
    c.nombre AS categoria_nombre,
    c.icono AS categoria_icono,
    a.id AS area_id,
    a.nombre AS area_nombre,
    a.responsable AS area_responsable,
    a.email_contacto AS area_email,
    CASE
        WHEN d.es_anonima = FALSE THEN CONCAT(u.nombres, ' ', u.apellidos)
        ELSE 'Anónimo'
    END AS ciudadano_nombre,
    u.email AS ciudadano_email,
    u.telefono AS ciudadano_telefono
FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
LEFT JOIN usuarios u ON d.usuario_id = u.id;

SELECT '✅ Vista v_denuncias_por_area creada' AS status;

-- ----------------------------------------------------------------------------
-- PASO 7: Crear tabla de auditoría
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    recurso VARCHAR(100),
    recurso_id INT,
    detalles JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT '✅ Tabla logs_auditoria creada' AS status;

-- ============================================================================
-- VERIFICACIÓN FINAL
-- ============================================================================

SELECT '═══════════════════════════════════════════' AS '';
SELECT '         RESUMEN DE MODIFICACIONES          ' AS '';
SELECT '═══════════════════════════════════════════' AS '';

SELECT 'Usuarios con área:' AS info, COUNT(*) AS total FROM usuarios WHERE area_id IS NOT NULL;
SELECT 'Categorías con área:' AS info, COUNT(*) AS total FROM categorias WHERE area_id IS NOT NULL;
SELECT 'Denuncias con área:' AS info, COUNT(*) AS total FROM denuncias WHERE area_asignada_id IS NOT NULL;

SELECT '═══════════════════════════════════════════' AS '';
SELECT 'Distribución de categorías por área:' AS '';
SELECT '═══════════════════════════════════════════' AS '';

SELECT
    a.nombre AS area,
    COUNT(c.id) AS num_categorias,
    GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
FROM areas_municipales a
LEFT JOIN categorias c ON a.id = c.area_id
GROUP BY a.id, a.nombre
ORDER BY a.nombre;

SELECT '═══════════════════════════════════════════' AS '';
SELECT '✅ MODIFICACIONES COMPLETADAS EXITOSAMENTE' AS '';
SELECT '═══════════════════════════════════════════' AS '';

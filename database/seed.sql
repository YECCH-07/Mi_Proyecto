-- DATOS DE PRUEBA
USE denuncia_ciudadana;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE seguimiento;
TRUNCATE TABLE notificaciones;
TRUNCATE TABLE evidencias;
TRUNCATE TABLE denuncias;
TRUNCATE TABLE categorias;
TRUNCATE TABLE areas_municipales;
TRUNCATE TABLE usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- CATEGORÍAS
INSERT INTO categorias (nombre, descripcion, icono) VALUES
('Basura y Limpieza', 'Acumulación de basura', 'trash'),
('Baches y Pistas', 'Baches en pistas', 'road'),
('Alumbrado Público', 'Postes sin luz', 'lightbulb'),
('Agua y Desagüe', 'Fugas de agua', 'water'),
('Parques y Jardines', 'Áreas verdes', 'tree'),
('Seguridad Ciudadana', 'Robos y asaltos', 'shield'),
('Ruido y Contaminación', 'Ruidos molestos', 'volume'),
('Transporte Público', 'Paraderos', 'bus'),
('Edificaciones Peligrosas', 'Construcciones', 'building'),
('Otros', 'Otras denuncias', 'alert-circle');

-- ÁREAS MUNICIPALES
INSERT INTO areas_municipales (nombre, responsable, email_contacto) VALUES
('Gerencia de Servicios', 'Ing. Carlos Mendoza', 'servicios@municipalidad.gob.pe'),
('Gerencia de Obras Públicas', 'Arq. María Rodríguez', 'obras@municipalidad.gob.pe'),
('Gerencia de Seguridad', 'Mayor Juan Pérez', 'seguridad@municipalidad.gob.pe'),
('Gerencia de Desarrollo', 'Urb. Ana Torres', 'desarrollo@municipalidad.gob.pe'),
('Gerencia de Medio Ambiente', 'Biol. Luis García', 'ambiente@municipalidad.gob.pe'),
('Gerencia de Transportes', 'Ing. Roberto Sánchez', 'transportes@municipalidad.gob.pe');

-- USUARIOS (password: password123)
INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, telefono, rol, verificado, activo) VALUES
('12345678', 'Admin', 'Sistema', 'admin@municipalidad.gob.pe', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyAXq1L.MQWS', '999888777', 'admin', TRUE, TRUE),
('23456789', 'María', 'López', 'maria.lopez@municipalidad.gob.pe', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyAXq1L.MQWS', '999777666', 'supervisor', TRUE, TRUE),
('78901234', 'Juan', 'Pérez', 'juan.perez@gmail.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyAXq1L.MQWS', '987654321', 'ciudadano', TRUE, TRUE);

SELECT 'Datos insertados' AS resultado;

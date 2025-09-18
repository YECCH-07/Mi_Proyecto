-- Esquema SQL básico para Plataforma de Denuncia Ciudadana
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'ciudadano',
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE denuncias (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER REFERENCES usuarios(id),
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT,
  foto_url VARCHAR(500),
  lat NUMERIC(10,6),
  lng NUMERIC(10,6),
  estado VARCHAR(50) DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE comentarios (
  id SERIAL PRIMARY KEY,
  denuncia_id INTEGER REFERENCES denuncias(id),
  usuario_id INTEGER REFERENCES usuarios(id),
  texto TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);

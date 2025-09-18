/**
 * Entrada principal - Express API (mejorada)
 */
require('dotenv').config();
const express = require('express');
const cors = require('cors'); // <-- 1. AÑADIDO: Para permitir peticiones de otros dominios
const helmet = require('helmet'); // <-- 2. AÑADIDO: Para seguridad básica (HTTP Headers)
const morgan = require('morgan'); // <-- 3. AÑADIDO: Para logging de peticiones

// Inicialización de la DB (Asegúrate que se conecte antes de levantar el servidor)
// const { sequelize } = require('./db/models'); // Descomenta si tienes tu config de DB aquí

const app = express();

// Middlewares
app.use(cors()); // Permite el acceso desde cualquier origen
app.use(helmet()); // Establece cabeceras de seguridad
app.use(express.json()); // Middleware para parsear JSON
app.use(morgan('dev')); // Logger de peticiones en formato 'dev'

// Rutas
const authRoutes = require('./routes/auth');
const denunciaRoutes = require('./routes/denuncias');

app.use('/api/auth', authRoutes);
app.use('/api/denuncias', denunciaRoutes);

// <-- 4. AÑADIDO: Middleware para manejar rutas no encontradas (404)
app.use((req, res, next) => {
  const error = new Error('Ruta no encontrada');
  error.status = 404;
  next(error);
});

// <-- 5. AÑADIDO: Middleware para manejar todos los demás errores
app.use((error, req, res, next) => {
  res.status(error.status || 500);
  res.json({
    error: {
      message: error.message
    }
  });
});


const PORT = process.env.PORT || 4000;

app.listen(PORT, async () => {
  console.log(`API escuchando en puerto ${PORT}`);
  // try {
  //   await sequelize.authenticate();
  //   console.log('Conexión a la base de datos establecida.');
  // } catch (error) {
  //   console.error('No se pudo conectar a la base de datos:', error);
  // }
});

const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const usersRoutes = require('./routes/users');
const authRoutes = require('./routes/auth');
const denunciasRoutes = require('./routes/denuncias');

const app = express();
const port = 3000;

// Crear carpeta de uploads si no existe
const uploadsDir = path.join(__dirname, '../public/uploads');
if (!fs.existsSync(uploadsDir)) {
    fs.mkdirSync(uploadsDir, { recursive: true });
    console.log('✓ Uploads directory created');
}

// Middlewares
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true })); // Para FormData
app.use(cors());

// Servir archivos estáticos (uploads)
app.use('/uploads', express.static(path.join(__dirname, '../public/uploads')));

// Rutas
app.use('/api/users', usersRoutes);
app.use('/api/auth', authRoutes);
app.use('/api/denuncias', denunciasRoutes);

// Ruta de prueba
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        message: 'Server is running',
        timestamp: new Date().toISOString()
    });
});

// Manejo de errores global
app.use((err, req, res, next) => {
    console.error('Error:', err.stack);
    res.status(500).json({ 
        error: 'Something went wrong!',
        message: err.message 
    });
});

// Iniciar servidor
app.listen(port, () => {
    console.log(`✓ Server is running on http://localhost:${port}`);
    console.log(`✓ API available at http://localhost:${port}/api`);
    console.log(`✓ Health check: http://localhost:${port}/api/health`);
});

module.exports = app;
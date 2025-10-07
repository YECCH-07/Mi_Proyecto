// ===========================================
// Importación de módulos
// ===========================================
const express = require('express');
const router = express.Router();
const db = require('../db/database');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');

// ===========================================
// Configuración de seguridad
// ===========================================
const saltRounds = 10; // Rondas para generar el hash de contraseñas (compatibilidad futura)
const jwtSecret = process.env.JWT_SECRET || 'your_default_secret_key'; // Clave secreta para firmar el JWT

// ===========================================
// Ruta de registro (placeholder)
// El registro principal se maneja en users.js
// ===========================================
router.post('/register', (req, res) => {
    res.status(404).json({
        msg: 'Registration should be done via /api/users/citizens or /api/users/authorities'
    });
});

// ===========================================
// Ruta de inicio de sesión (Login)
// ===========================================
router.post('/login', (req, res) => {
    const { email, password } = req.body;

    // Verificar que se envíen los campos requeridos
    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password are required' });
    }

    // Buscar al usuario en la base de datos
    const sql = `SELECT * FROM users WHERE email = ?`;
    db.get(sql, [email], (err, user) => {
        if (err) {
            console.error('Database error during login:', err.message);
            return res.status(500).json({ error: 'Internal server error' });
        }

        // Usuario no encontrado
        if (!user) {
            return res.status(401).json({ error: 'Invalid credentials' });
        }

        // Comparar contraseñas usando bcrypt
        bcrypt.compare(password, user.password, (err, result) => {
            if (err) {
                console.error('Bcrypt error:', err);
                return res.status(500).json({ error: 'Error verifying password' });
            }

            if (!result) {
                return res.status(401).json({ error: 'Invalid credentials' });
            }

            // Contraseña correcta: generar token JWT
            const payload = { 
                id: user.id, 
                email: user.email, 
                user_type: user.user_type,
                role: user.role
            };

            // Generar token con expiración de 1 hora
            const token = jwt.sign(payload, jwtSecret, { expiresIn: '1h' });

            // Enviar respuesta con el token y datos básicos del usuario
            res.json({
                message: 'Login successful',
                token: token,
                user: {
                    id: user.id,
                    email: user.email,
                    user_type: user.user_type
                }
            });
        });
    });
});

// ===========================================
// Exportar el router
// ===========================================
module.exports = router;

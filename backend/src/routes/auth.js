
// ===========================================
// Importación de módulos
// ===========================================
const express = require('express');
const router = express.Router();
const db = require('../db/database');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { body, validationResult } = require('express-validator');

// ===========================================
// Configuración de seguridad
// ===========================================
const saltRounds = 10; // Rondas para generar hash
const jwtSecret = process.env.JWT_SECRET || 'your_default_secret_key'; // Usa .env en producción

// ===========================================
// Ruta de registro (placeholder)
// ===========================================
router.post('/register', (req, res) => {
  res.status(404).json({
    msg: 'Registration should be done via /api/users/citizens or /api/users/authorities',
  });
});

// ===========================================
// Ruta de inicio de sesión (Login)
// ===========================================
router.post(
  '/login',
  [
    body('email').isEmail().withMessage('Invalid email format'),
    body('password').notEmpty().withMessage('Password is required'),
  ],
  async (req, res) => {
    try {
      // Validar datos de entrada
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }

      const { email, password } = req.body;

      // Buscar al usuario en la base de datos
      const sql = `SELECT * FROM users WHERE email = ?`;
      const user = await new Promise((resolve, reject) => {
        db.get(sql, [email], (err, row) => {
          if (err) return reject(err);
          resolve(row);
        });
      });

      if (!user) {
        return res.status(401).json({ error: 'Invalid credentials' });
      }

      // Comparar contraseñas
      const isMatch = await bcrypt.compare(password, user.password);
      if (!isMatch) {
        return res.status(401).json({ error: 'Invalid credentials' });
      }

      // Crear payload del JWT
      const payload = {
        id: user.id,
        email: user.email,
        user_type: user.user_type,
        role: user.role,
      };

      // Generar token con expiración de 1 hora
      const token = jwt.sign(payload, jwtSecret, { expiresIn: '1h' });

      // Responder con token y datos del usuario
      res.json({
        message: 'Login successful',
        token,
        user: {
          id: user.id,
          email: user.email,
          user_type: user.user_type,
          role: user.role,
        },
      });
    } catch (err) {
      console.error('Error during login:', err.message);
      res.status(500).json({ error: 'Internal server error' });
    }
  }
);

// ===========================================
// Exportar el router
// ===========================================
module.exports = router;

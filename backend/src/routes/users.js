// ===========================================
// Importación de módulos
// ===========================================
const express = require('express');
const router = express.Router();
const db = require('../db/database');
const bcrypt = require('bcrypt');

// ===========================================
// Configuración de seguridad
// ===========================================
const saltRounds = 10; // Rondas de cifrado para proteger contraseñas

// ===========================================
// Registro de ciudadanos
// ===========================================
router.post('/citizens', (req, res) => {
    const { dni, first_name, last_name, email, phone, address, password } = req.body;
    const user_type = 'citizen';

    // Validar campos obligatorios
    if (!dni || !first_name || !last_name || !email || !password) {
        return res.status(400).json({ error: 'Missing required fields' });
    }

    // Cifrar la contraseña antes de guardar
    bcrypt.hash(password, saltRounds, (err, hash) => {
        if (err) {
            console.error('Error hashing password:', err);
            return res.status(500).json({ error: 'Error hashing password' });
        }

        // Insertar nuevo ciudadano en la base de datos
        const sql = `
            INSERT INTO users 
            (dni, first_name, last_name, email, phone, address, password, user_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `;
        db.run(sql, [dni, first_name, last_name, email, phone, address, hash, user_type], function(err) {
            if (err) {
                console.error('Database error:', err.message);
                return res.status(400).json({ error: err.message });
            }

            // Registro exitoso
            res.status(201).json({
                user_id: this.lastID,
                message: 'Citizen registered successfully'
            });
        });
    });
});

// ===========================================
// Registro de autoridades
// ===========================================
router.post('/authorities', (req, res) => {
    const { dni, first_name, last_name, email, phone, password, institution_id, role } = req.body;
    const user_type = 'authority';

    // Validar campos obligatorios
    if (!dni || !first_name || !last_name || !email || !password || !institution_id || !role) {
        return res.status(400).json({ error: 'Missing required fields' });
    }

    // Cifrar la contraseña antes de guardar
    bcrypt.hash(password, saltRounds, (err, hash) => {
        if (err) {
            console.error('Error hashing password:', err);
            return res.status(500).json({ error: 'Error hashing password' });
        }

        // Insertar nueva autoridad en la base de datos
        const sql = `
            INSERT INTO users 
            (dni, first_name, last_name, email, phone, password, user_type, institution_id, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;
        db.run(sql, [dni, first_name, last_name, email, phone, hash, user_type, institution_id, role], function(err) {
            if (err) {
                console.error('Database error:', err.message);
                return res.status(400).json({ error: err.message });
            }

            // Registro exitoso
            res.status(201).json({
                user_id: this.lastID,
                message: 'Authority registered successfully'
            });
        });
    });
});

// ===========================================
// Exportar el router
// ===========================================
module.exports = router;

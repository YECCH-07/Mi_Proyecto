const express = require('express');
const router = express.Router();
const db = require('../db/database');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const saltRounds = 10;
const jwtSecret = process.env.JWT_SECRET || 'your_default_secret_key';

// Placeholder for registration - main registration is in users.js
router.post('/register', (req, res) => {
    res.status(404).json({ msg: 'Registration should be done via /api/users/citizens or /api/users/authorities' });
});

router.post('/login', (req, res) => {
    const { email, password } = req.body;

    if (!email || !password) {
        return res.status(400).json({ error: 'Email and password are required' });
    }

    const sql = `SELECT * FROM users WHERE email = ?`;
    db.get(sql, [email], (err, user) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        if (!user) {
            return res.status(401).json({ error: 'Invalid credentials' });
        }

        bcrypt.compare(password, user.password, (err, result) => {
            if (err || !result) {
                return res.status(401).json({ error: 'Invalid credentials' });
            }

            // Passwords match, create JWT
            const payload = { 
                id: user.id, 
                email: user.email, 
                user_type: user.user_type,
                role: user.role
            };
            const token = jwt.sign(payload, jwtSecret, { expiresIn: '1h' });

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

module.exports = router;

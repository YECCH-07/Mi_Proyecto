const express = require('express');
const router = express.Router();
const db = require('../db/database');
const bcrypt = require('bcrypt');
const saltRounds = 10;

router.post('/citizens', (req, res) => {
    const { dni, first_name, last_name, email, phone, address, password } = req.body;
    const user_type = 'citizen';

    if (!dni || !first_name || !last_name || !email || !password) {
        return res.status(400).json({ error: 'Missing required fields' });
    }

    bcrypt.hash(password, saltRounds, (err, hash) => {
        if (err) {
            return res.status(500).json({ error: 'Error hashing password' });
        }

        const sql = `INSERT INTO users (dni, first_name, last_name, email, phone, address, password, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`;
        db.run(sql, [dni, first_name, last_name, email, phone, address, hash, user_type], function(err) {
            if (err) {
                return res.status(400).json({ error: err.message });
            }
            res.status(201).json({
                user_id: this.lastID,
                message: 'Citizen registered successfully'
            });
        });
    });
});

router.post('/authorities', (req, res) => {
    const { dni, first_name, last_name, email, phone, password, institution_id, role } = req.body;
    const user_type = 'authority';

    if (!dni || !first_name || !last_name || !email || !password || !institution_id || !role) {
        return res.status(400).json({ error: 'Missing required fields' });
    }
    
    bcrypt.hash(password, saltRounds, (err, hash) => {
        if (err) {
            return res.status(500).json({ error: 'Error hashing password' });
        }

        const sql = `INSERT INTO users (dni, first_name, last_name, email, phone, password, user_type, institution_id, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`;
        db.run(sql, [dni, first_name, last_name, email, phone, hash, user_type, institution_id, role], function(err) {
            if (err) {
                return res.status(400).json({ error: err.message });
            }
            res.status(201).json({
                user_id: this.lastID,
                message: 'Authority registered successfully'
            });
        });
    });
});

module.exports = router;
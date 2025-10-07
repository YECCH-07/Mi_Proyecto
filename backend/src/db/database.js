const sqlite3 = require('sqlite3').verbose();
const path = require('path');

// Ruta absoluta para la base de datos
const dbPath = path.join(__dirname, 'database.db');

const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error('Error connecting to database:', err.message);
    } else {
        console.log('Connected to the SQLite database at:', dbPath);
    }
});

db.serialize(() => {
    // Tabla de instituciones
    db.run(`CREATE TABLE IF NOT EXISTS institutions (
        institution_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE
    )`, (err) => {
        if (err) console.error('Error creating institutions table:', err);
        else console.log('✓ Institutions table ready');
    });

    // Tabla de usuarios
    db.run(`CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        dni TEXT NOT NULL UNIQUE,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT,
        address TEXT,
        password TEXT NOT NULL,
        user_type TEXT NOT NULL CHECK(user_type IN ('citizen', 'authority')),
        verified BOOLEAN DEFAULT 0,
        institution_id INTEGER,
        role TEXT CHECK(role IN ('operator', 'supervisor', 'administrator')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (institution_id) REFERENCES institutions(institution_id)
    )`, (err) => {
        if (err) console.error('Error creating users table:', err);
        else console.log('✓ Users table ready');
    });

    // Tabla de reportes/denuncias
    db.run(`CREATE TABLE IF NOT EXISTS reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        tracking_id TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        category TEXT,
        lat REAL NOT NULL,
        lng REAL NOT NULL,
        address TEXT,
        evidence_files TEXT,
        status TEXT DEFAULT 'received' CHECK(status IN ('received', 'in_progress', 'resolved', 'rejected')),
        priority TEXT DEFAULT 'medium' CHECK(priority IN ('low', 'medium', 'high')),
        assigned_to INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (assigned_to) REFERENCES users(user_id)
    )`, (err) => {
        if (err) console.error('Error creating reports table:', err);
        else console.log('✓ Reports table ready');
    });

    // Tabla de seguimiento de reportes
    db.run(`CREATE TABLE IF NOT EXISTS report_tracking (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        report_id INTEGER NOT NULL,
        user_id INTEGER,
        previous_status TEXT,
        new_status TEXT NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (report_id) REFERENCES reports(id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )`, (err) => {
        if (err) console.error('Error creating report_tracking table:', err);
        else console.log('✓ Report tracking table ready');
    });
});

module.exports = db;
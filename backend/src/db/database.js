const sqlite3 = require('sqlite3').verbose();

const db = new sqlite3.Database('./src/db/database.db', (err) => {
    if (err) {
        console.error(err.message);
    }
    console.log('Connected to the SQLite database.');
});

db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS institutions (
        institution_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE
    )`);

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
        FOREIGN KEY (institution_id) REFERENCES institutions(institution_id)
    )`);
});

module.exports = db;
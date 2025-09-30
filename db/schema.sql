-- Updated schema for Urban Issue Reporting Platform

-- Users table: Stores citizens, municipal authorities, and administrators
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dni VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    password TEXT NOT NULL, -- Storing hash from bcrypt
    user_type VARCHAR(50) NOT NULL CHECK(user_type IN ('citizen', 'authority', 'admin')), -- citizen, authority, admin
    institution_id INTEGER, -- For authorities
    role VARCHAR(50), -- For authorities: operator, supervisor, admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT 0,
    FOREIGN KEY (institution_id) REFERENCES institutions(id)
);

-- Institutions table: For municipal dependencies
CREATE TABLE institutions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT
);

-- Reports table: Stores all citizen complaints
CREATE TABLE reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    photo_url VARCHAR(500),
    lat REAL,
    lng REAL,
    status VARCHAR(50) DEFAULT 'pending' CHECK(status IN ('pending', 'in_progress', 'resolved', 'rejected')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Comments table: For discussion on reports
CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Report Status History table: Tracks changes in a report's status
CREATE TABLE report_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    changed_by_user_id INTEGER,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    comment TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id)
);
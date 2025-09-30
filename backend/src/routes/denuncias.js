const express = require('express');
const router = express.Router();
const db = require('../db/database');
const multer = require('multer');
const path = require('path');

// --- Multer Configuration for File Uploads ---
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        // The destination folder for uploads
        cb(null, 'public/uploads/');
    },
    filename: function (req, file, cb) {
        // Create a unique filename to avoid overwrites
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB file size limit
    fileFilter: function (req, file, cb) {
        // Allowed file types
        const filetypes = /jpeg|jpg|png|pdf/;
        const mimetype = filetypes.test(file.mimetype);
        const extname = filetypes.test(path.extname(file.originalname).toLowerCase());
        if (mimetype && extname) {
            return cb(null, true);
        }
        cb("Error: File upload only supports the following filetypes - " + filetypes);
    }
}).array('evidenceFiles', 5); // Field name 'evidenceFiles', max 5 files

// --- Helper function to generate unique tracking ID ---
async function generateTrackingId() {
    return new Promise((resolve, reject) => {
        const year = new Date().getFullYear();
        const prefix = `DU-${year}-`;

        // Find the last ID for the current year to create a sequential number
        const sql = `SELECT tracking_id FROM reports WHERE tracking_id LIKE ? ORDER BY tracking_id DESC LIMIT 1`;
        db.get(sql, [`${prefix}%`], (err, row) => {
            if (err) {
                return reject('Database error while generating tracking ID');
            }

            let nextId = 1;
            if (row) {
                const lastId = parseInt(row.tracking_id.split('-')[2], 10);
                nextId = lastId + 1;
            }

            // Format the number with leading zeros
            const sequentialNumber = nextId.toString().padStart(6, '0');
            resolve(prefix + sequentialNumber);
        });
    });
}

// --- Routes ---

// GET all reports (basic version)
router.get('/', (req, res) => {
    const sql = `SELECT id, tracking_id, title, status, created_at FROM reports ORDER BY created_at DESC`;
    db.all(sql, [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json({ reports: rows });
    });
});

// POST a new report
router.post('/', async (req, res) => {
    upload(req, res, async function (err) {
        if (err) {
            return res.status(400).json({ error: err.message || 'Error uploading files.' });
        }

        const { title, description, lat, lng, userId } = req.body;

        // Validation
        if (!title || !description || !lat || !lng) {
            return res.status(400).json({ error: 'Missing required fields: title, description, lat, lng.' });
        }

        try {
            const trackingId = await generateTrackingId();
            const files = req.files ? req.files.map(file => file.path) : [];
            const evidenceFiles = JSON.stringify(files);

            const sql = `INSERT INTO reports (user_id, tracking_id, title, description, lat, lng, evidence_files) VALUES (?, ?, ?, ?, ?, ?, ?)`;
            const params = [userId || null, trackingId, title, description, lat, lng, evidenceFiles];

            db.run(sql, params, function (err) {
                if (err) {
                    return res.status(500).json({ error: err.message });
                }
                res.status(201).json({
                    message: 'Report created successfully',
                    reportId: this.lastID,
                    trackingId: trackingId
                });
            });
        } catch (error) {
            res.status(500).json({ error: error.toString() });
        }
    });
});

module.exports = router;
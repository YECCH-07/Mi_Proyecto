const express = require('express');
const router = express.Router();
const db = require('../db/database');
const multer = require('multer');
const path = require('path');

// --- Multer Configuration for File Uploads ---
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, path.join(__dirname, '../../public/uploads/'));
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB
    fileFilter: function (req, file, cb) {
        const filetypes = /jpeg|jpg|png|pdf/;
        const mimetype = filetypes.test(file.mimetype);
        const extname = filetypes.test(path.extname(file.originalname).toLowerCase());
        
        if (mimetype && extname) {
            return cb(null, true);
        }
        cb(new Error("Solo se permiten archivos: jpeg, jpg, png, pdf"));
    }
}).array('evidenceFiles', 5);

// --- Helper function to generate unique tracking ID ---
async function generateTrackingId() {
    return new Promise((resolve, reject) => {
        const year = new Date().getFullYear();
        const prefix = `DEN-${year}-`;

        const sql = `SELECT tracking_id FROM reports WHERE tracking_id LIKE ? ORDER BY tracking_id DESC LIMIT 1`;
        db.get(sql, [`${prefix}%`], (err, row) => {
            if (err) {
                console.error('Error generating tracking ID:', err);
                return reject('Database error while generating tracking ID');
            }

            let nextId = 1;
            if (row) {
                const lastId = parseInt(row.tracking_id.split('-')[2], 10);
                nextId = lastId + 1;
            }

            const sequentialNumber = nextId.toString().padStart(6, '0');
            resolve(prefix + sequentialNumber);
        });
    });
}

// --- Routes ---

// GET all reports
router.get('/', (req, res) => {
    console.log('GET /api/denuncias - Fetching all reports');
    
    const sql = `SELECT id, tracking_id, title, category, status, created_at FROM reports ORDER BY created_at DESC`;
    db.all(sql, [], (err, rows) => {
        if (err) {
            console.error('Error fetching reports:', err);
            return res.status(500).json({ error: err.message });
        }
        console.log(`✓ Found ${rows.length} reports`);
        res.json({ success: true, reports: rows });
    });
});

// GET single report by ID
router.get('/:id', (req, res) => {
    const { id } = req.params;
    console.log(`GET /api/denuncias/${id} - Fetching report`);
    
    const sql = `SELECT * FROM reports WHERE id = ?`;
    db.get(sql, [id], (err, row) => {
        if (err) {
            console.error('Error fetching report:', err);
            return res.status(500).json({ error: err.message });
        }
        if (!row) {
            return res.status(404).json({ error: 'Report not found' });
        }
        
        // Parse evidence files JSON
        if (row.evidence_files) {
            try {
                row.evidence_files = JSON.parse(row.evidence_files);
            } catch (e) {
                row.evidence_files = [];
            }
        }
        
        res.json({ success: true, report: row });
    });
});

// POST a new report
router.post('/', async (req, res) => {
    console.log('POST /api/denuncias - Creating new report');
    
    upload(req, res, async function (err) {
        if (err) {
            console.error('Upload error:', err);
            return res.status(400).json({ 
                success: false,
                error: err.message || 'Error uploading files.' 
            });
        }

        console.log('Request body:', req.body);
        console.log('Uploaded files:', req.files?.length || 0);

        const { title, description, category, lat, lng, address, userId } = req.body;

        // Validation
        if (!title || !description || !lat || !lng) {
            console.error('Missing required fields');
            return res.status(400).json({ 
                success: false,
                error: 'Faltan campos requeridos: título, descripción, latitud, longitud.' 
            });
        }

        try {
            const trackingId = await generateTrackingId();
            console.log('Generated tracking ID:', trackingId);
            
            const files = req.files ? req.files.map(file => `/uploads/${path.basename(file.path)}`) : [];
            const evidenceFiles = JSON.stringify(files);

            const sql = `INSERT INTO reports (user_id, tracking_id, title, description, category, lat, lng, address, evidence_files, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'received')`;
            const params = [
                userId || null, 
                trackingId, 
                title, 
                description, 
                category || 'otro',
                parseFloat(lat), 
                parseFloat(lng), 
                address || '',
                evidenceFiles
            ];

            db.run(sql, params, function (err) {
                if (err) {
                    console.error('Database insert error:', err);
                    return res.status(500).json({ 
                        success: false,
                        error: err.message 
                    });
                }
                
                console.log(`✓ Report created successfully with ID: ${this.lastID}`);
                
                res.status(201).json({
                    success: true,
                    message: 'Denuncia creada exitosamente',
                    reportId: this.lastID,
                    trackingId: trackingId,
                    files: files
                });
            });
        } catch (error) {
            console.error('Error creating report:', error);
            res.status(500).json({ 
                success: false,
                error: error.toString() 
            });
        }
    });
});

// PUT update report status
router.put('/:id/status', (req, res) => {
    const { id } = req.params;
    const { status, comment, userId } = req.body;
    
    console.log(`PUT /api/denuncias/${id}/status - Updating status to ${status}`);
    
    if (!status) {
        return res.status(400).json({ 
            success: false,
            error: 'Status is required' 
        });
    }
    
    // Get current status first
    db.get('SELECT status FROM reports WHERE id = ?', [id], (err, row) => {
        if (err || !row) {
            return res.status(404).json({ 
                success: false,
                error: 'Report not found' 
            });
        }
        
        const previousStatus = row.status;
        
        // Update report status
        const updateSql = `UPDATE reports SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`;
        db.run(updateSql, [status, id], function (err) {
            if (err) {
                console.error('Error updating status:', err);
                return res.status(500).json({ 
                    success: false,
                    error: err.message 
                });
            }
            
            // Insert tracking record
            const trackingSql = `INSERT INTO report_tracking (report_id, user_id, previous_status, new_status, comment) 
                                VALUES (?, ?, ?, ?, ?)`;
            db.run(trackingSql, [id, userId, previousStatus, status, comment || ''], (err) => {
                if (err) {
                    console.error('Error inserting tracking:', err);
                }
                
                console.log(`✓ Status updated: ${previousStatus} → ${status}`);
                res.json({ 
                    success: true,
                    message: 'Estado actualizado correctamente',
                    previousStatus,
                    newStatus: status
                });
            });
        });
    });
});

module.exports = router;
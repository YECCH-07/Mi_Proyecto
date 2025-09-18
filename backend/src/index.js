/**
 * Entrada principal - Express API (esqueleto)
 */
require('dotenv').config();
const express = require('express');
const app = express();
app.use(express.json());

// Rutas
const authRoutes = require('./routes/auth');
const denunciaRoutes = require('./routes/denuncias');

app.use('/api/auth', authRoutes);
app.use('/api/denuncias', denunciaRoutes);

const PORT = process.env.PORT || 4000;
app.listen(PORT, ()=> console.log(`API escuchando en puerto ${PORT}`));

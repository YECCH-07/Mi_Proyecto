const express = require('express');
const router = express.Router();
// Endpoints básicos
router.post('/', (req,res) => res.json({msg:'crear denuncia'}));
router.get('/', (req,res) => res.json({msg:'listar denuncias'}));
router.get('/:id', (req,res) => res.json({msg:'detalle denuncia'}));
module.exports = router;

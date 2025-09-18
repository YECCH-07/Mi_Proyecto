const express = require('express');
const router = express.Router();
// TODO: implementar controladores
router.post('/register', (req,res)=> res.json({msg:'register endpoint'}));
router.post('/login', (req,res)=> res.json({msg:'login endpoint'}));
module.exports = router;

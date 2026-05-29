// routes/queueRoutes.js
const express = require('express');
const router = express.Router();
const queueController = require('../controllers/queueController');

router.post('/generate', queueController.generateToken);
router.post('/next', queueController.callNext);
router.get('/serving', queueController.getActiveServing);

module.exports = router;
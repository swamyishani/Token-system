// server.js
const express = require('express');
const cors = require('cors');
const db = require('./config/db');
const authRoutes = require('./routes/authRoutes'); 
const queueRoutes = require('./routes/queueRoutes'); // 👈 Added: Imports the new token system routes
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/queue', queueRoutes); // 👈 Added: Links token routes to http://localhost:3000/api/queue

// Test route to ensure server health and database connectivity
app.get('/api/health', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT 1 + 1 AS solution');
        res.status(200).json({ 
            status: "Online", 
            message: "Backend server running and MySQL connected safely.", 
            test: rows[0].solution 
        });
    } catch (error) {
        res.status(500).json({ 
            status: "Error", 
            message: "Server is online, but MySQL connection failed.", 
            error: error.message 
        });
    }
});

app.listen(PORT, () => {
    console.log(`🚀 Smart Queue Engine executing live on http://localhost:${PORT}`);
});
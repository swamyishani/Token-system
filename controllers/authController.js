// controllers/authController.js
const db = require('../config/db');
const bcrypt = require('bcryptjs');

// 1. Staff Registration
exports.register = async (req, res) => {
    const { username, password } = req.body; // 'username' comes from the test HTML form

    if (!username || !password) {
        return res.status(400).json({ error: "Name and password are required." });
    }

    try {
        // Check if user already exists using the 'name' column
        const [existingUser] = await db.query('SELECT * FROM staff WHERE name = ?', [username]);
        if (existingUser.length > 0) {
            return res.status(400).json({ error: "This name is already registered." });
        }

        // Hash the password for security
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);

        // Insert into database matching your exact columns: name, email, password
        await db.query(
            'INSERT INTO staff (name, email, password) VALUES (?, ?, ?)',
            [username, `${username.toLowerCase()}@queue.com`, hashedPassword]
        );

        res.status(201).json({ message: "Staff member registered successfully!" });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// 2. Staff Login
exports.login = async (req, res) => {
    const { username, password } = req.body;

    if (!username || !password) {
        return res.status(400).json({ error: "Please provide both name and password." });
    }

    try {
        // Find user in database using the 'name' column
        const [users] = await db.query('SELECT * FROM staff WHERE name = ?', [username]);
        if (users.length === 0) {
            return res.status(401).json({ error: "Invalid credentials." });
        }

        const user = users[0];

        // Compare entered password with hashed password in DB
        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) {
            return res.status(401).json({ error: "Invalid credentials." });
        }

        res.status(200).json({
            message: "Login successful!",
            user: { id: user.id, name: user.name }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};
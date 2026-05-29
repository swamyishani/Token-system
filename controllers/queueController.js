// controllers/queueController.js
const db = require('../config/db');

// 1. Generate a Token (Updated to include customer name)
exports.generateToken = async (req, res) => {
    const { customer_name, phone_or_email, department_id } = req.body;

    // Validate inputs
    if (!phone_or_email || !department_id) {
        return res.status(400).json({ error: "Missing required contact details or department selection." });
    }

    try {
        // Fetch department code to build prefix (e.g., Dept ID 1 -> 'TKT')
        const [dept] = await db.query('SELECT id FROM departments WHERE id = ?', [department_id]);
        if (dept.length === 0) {
            return res.status(404).json({ error: "Selected department does not exist." });
        }

        // Count existing tokens for today to generate sequential number
        const [countResult] = await db.query(
            'SELECT COUNT(*) AS total FROM queue_tokens WHERE department_id = ? AND DATE(created_at) = CURRENT_DATE()',
            [department_id]
        );
        const nextNumber = countResult[0].total + 1;
        const computedToken = `D${department_id}-${100 + nextNumber}`;

        // Insert new token into the queue_tokens table (Including customer_name)
        const [result] = await db.query(
            'INSERT INTO queue_tokens (token_number, customer_name, phone_or_email, security_credentials, department_id, status) VALUES (?, ?, ?, ?, ?, ?)',
            [computedToken, customer_name || 'Guest', phone_or_email, '1234', department_id, 'Waiting']
        );

        res.status(201).json({
            message: "Token generated successfully!",
            token_id: result.insertId,
            token_number: computedToken
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// 2. Call Next Token (For the Staff Dashboard Counter)
exports.callNext = async (req, res) => {
    const { assigned_counter_id, department_id } = req.body;

    if (!assigned_counter_id || !department_id) {
        return res.status(400).json({ error: "Counter ID and Department ID are required to call next customer." });
    }

    try {
        const [waiting] = await db.query(
            'SELECT * FROM queue_tokens WHERE department_id = ? AND status = "Waiting" ORDER BY id ASC LIMIT 1',
            [department_id]
        );

        if (waiting.length === 0) {
            return res.status(200).json({ message: "No customers waiting in this line right now!" });
        }

        const nextToken = waiting[0];

        await db.query(
            'UPDATE queue_tokens SET status = "Serving", assigned_counter_id = ? WHERE id = ?',
            [assigned_counter_id, nextToken.id]
        );

        res.status(200).json({
            message: `Calling Token ${nextToken.token_number}`,
            token: {
                id: nextToken.id,
                token_number: nextToken.token_number,
                customer_name: nextToken.customer_name, // Now returning name to dashboard
                phone_or_email: nextToken.phone_or_email,
                assigned_counter_id: assigned_counter_id
            }
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};

// 3. Get Active Serving Tokens (For the Waiting Room Monitor)
exports.getActiveServing = async (req, res) => {
    try {
        const [serving] = await db.query(
            'SELECT token_number, assigned_counter_id FROM queue_tokens WHERE status = "Serving" ORDER BY updated_at DESC LIMIT 6'
        );
        res.status(200).json(serving);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
};
const jwt = require('jsonwebtoken');
const JWT_SECRET = 'rahasia_super_kuat_fittrack_2026'; // Sebaiknya diletakkan di .env nantinya

const verifyToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return res.status(401).json({ error: "Akses ditolak" });

    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) return res.status(403).json({ error: "Token tidak valid" });
        req.user = decoded;
        next();
    });
};

module.exports = verifyToken;
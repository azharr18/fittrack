const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { User } = require('../models/index.js');
const JWT_SECRET = 'rahasia_super_kuat_fittrack_2026';

const register = async (req, res) => {
   
    const { name, email, age, height_cm, weight_kg, password } = req.body;
    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        const newUser = await User.create({
            name, 
            email, 
            age: age || null,              
            height_cm: height_cm || null,   
            weight_kg: weight_kg || null, 
            password: hashedPassword
        });
        res.status(201).json({ message: "Registrasi berhasil", userId: newUser.id });
    } catch (error) {
        res.status(400).json({ error: "Email mungkin sudah digunakan" });
    }
};

const login = async (req, res) => {
    const { email, password } = req.body;
    try {
        const user = await User.findOne({ where: { email } });
        if (!user) return res.status(401).json({ error: "Email atau password salah" });

        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) return res.status(401).json({ error: "Email atau password salah" });

        const token = jwt.sign({ id: user.id, name: user.name }, JWT_SECRET, { expiresIn: '24h' });
        
        res.json({ 
            message: "Login berhasil", 
            token, 
            name: user.name,           
            weight_kg: user.weight_kg, 
            age: user.age,             
            user: { name: user.name, id: user.id } 
        });
    } catch (error) {
        res.status(500).json({ error: "Terjadi kesalahan server" });
    }
};


const getProfile = async (req, res) => {
    try {
        const user = await User.findByPk(req.params.id);
        if (!user) return res.status(404).json({ error: "User tidak ditemukan" });
        

        res.json({
            id: user.id,
            name: user.name,
            email: user.email,
            age: user.age,
            height_cm: user.height_cm,
            weight_kg: user.weight_kg
        });
    } catch (error) {
        res.status(500).json({ error: "Terjadi kesalahan saat mengambil profil" });
    }
};


const updateProfile = async (req, res) => {
    try {
        const { name, age, height_cm, weight_kg } = req.body;
        const user = await User.findByPk(req.params.id);
        if (!user) return res.status(404).json({ error: "User tidak ditemukan" });

      
        await user.update({ 
            name: name || user.name, 
            age: age || user.age, 
            height_cm: height_cm || user.height_cm, 
            weight_kg: weight_kg || user.weight_kg 
        });

        res.json({ message: "Profil berhasil diperbarui", user });
    } catch (error) {
        res.status(500).json({ error: "Gagal memperbarui profil" });
    }
};

// 5. Pastikan semua fungsi diekspor dengan benar
module.exports = { register, login, getProfile, updateProfile };

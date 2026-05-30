const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const router = require('./routes/index.js');
const db = require('./config/database.js');

const app = express();

app.use(helmet()); 
app.use(cors());   
app.use(express.json());

// Menggunakan semua rute yang sudah didefinisikan
app.use(router);

const PORT = 3000;

// Tes koneksi database lalu jalankan server
db.authenticate()
    .then(() => {
        console.log('Database Connected...');
        app.listen(PORT, () => console.log(`Server MVC API berjalan di http://localhost:${PORT}`));
    })
    .catch(err => {
        console.error('Database connection error:', err);
    });
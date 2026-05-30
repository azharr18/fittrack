const { Sequelize } = require('sequelize');

const db = new Sequelize('fitness_tracker', 'root', '', {
    host: 'localhost',
    dialect: 'mysql',
    logging: false // Matikan log SQL di terminal agar bersih
});

module.exports = db;
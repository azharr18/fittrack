const { Sequelize } = require('sequelize');

const db = new Sequelize('fitness_tracker', 'root', '', {
    host: 'localhost',
    dialect: 'mysql',
    logging: false 
});

module.exports = db;

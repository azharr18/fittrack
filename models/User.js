const { Sequelize, DataTypes } = require('sequelize');
const db = require('../config/database.js');

const User = db.define('users', {
    name: { type: DataTypes.STRING, allowNull: false },
    email: { type: DataTypes.STRING, allowNull: false, unique: true },
    password: { type: DataTypes.STRING, allowNull: false },
    age: { type: DataTypes.INTEGER },         
    height_cm: { type: DataTypes.FLOAT },    
    weight_kg: { type: DataTypes.FLOAT }
}, {
    timestamps: false
});

module.exports = User;

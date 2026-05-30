const { Sequelize, DataTypes } = require('sequelize');
const db = require('../config/database.js');

const Shoe = db.define('shoes', {
    brand: { type: DataTypes.STRING, allowNull: false },
    model: { type: DataTypes.STRING, allowNull: false },
    user_id: { type: DataTypes.INTEGER }
}, {
    timestamps: false
});

module.exports = Shoe;
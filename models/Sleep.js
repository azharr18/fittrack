const { DataTypes } = require('sequelize');
const db = require('../config/database.js');

const Sleep = db.define('sleeps', {
    sleep_hours: { type: DataTypes.INTEGER, allowNull: false },
    sleep_minutes: { type: DataTypes.INTEGER, defaultValue: 0 },
    sleep_quality: { type: DataTypes.INTEGER, allowNull: false },
    sleep_notes: { type: DataTypes.TEXT },
    user_id: { type: DataTypes.INTEGER }
}, {
    timestamps: false
});

module.exports = Sleep;
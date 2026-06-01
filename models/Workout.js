const { Sequelize, DataTypes } = require('sequelize');
const db = require('../config/database.js');

const Workout = db.define('workouts', {
    type: { type: DataTypes.STRING, allowNull: false },
    distance_km: { type: DataTypes.FLOAT },
    elevation_m: { type: DataTypes.INTEGER },
    duration_minutes: { type: DataTypes.INTEGER },
    heart_rate_bpm: { type: DataTypes.INTEGER },
    max_heart_rate_bpm: { type: DataTypes.INTEGER },
    user_id: { type: DataTypes.INTEGER },
    shoe_id: { type: DataTypes.INTEGER }
}, {
    timestamps: false
});

module.exports = Workout;

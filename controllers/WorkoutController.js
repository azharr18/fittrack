const { Workout, User, Shoe, Sleep } = require('../models/index.js');

const getWorkouts = async (req, res) => {
    try {
        const workouts = await Workout.findAll({
            where: { user_id: req.user.id },
            include: [ { model: User, attributes: ['name', 'weight_kg'] }, { model: Shoe, attributes: ['brand', 'model'] } ]
        });

        const formattedData = workouts.map(w => ({
            id: w.id, type: w.type, distance_km: w.distance_km, elevation_m: w.elevation_m,
            duration_minutes: w.duration_minutes, heart_rate_bpm: w.heart_rate_bpm, 
            max_heart_rate_bpm: w.max_heart_rate_bpm, 
            shoe_id: w.shoe_id, user_name: w.user ? w.user.name : null, weight_kg: w.user ? w.user.weight_kg : null,
            shoe_brand: w.shoe ? w.shoe.brand : null, shoe_model: w.shoe ? w.shoe.model : null
        }));
        res.json(formattedData);
    } catch (error) { res.status(500).json({ error: "Gagal mengambil data" }); }
};

const createWorkout = async (req, res) => {
    const { type, distance_km, elevation_m, duration_minutes, heart_rate_bpm, max_heart_rate_bpm, shoe_id } = req.body;
    try {
        await Workout.create({
            type, distance_km, elevation_m, duration_minutes, heart_rate_bpm, max_heart_rate_bpm, 
            shoe_id: shoe_id || null, user_id: req.user.id
        });
        res.status(201).json({ message: "Aktivitas tersimpan!" });
    } catch (error) { res.status(500).json({ error: "Penyebab: " + error.message }); }
};

const updateWorkout = async (req, res) => {
    const { type, distance_km, elevation_m, duration_minutes, heart_rate_bpm, max_heart_rate_bpm, shoe_id } = req.body;
    try {
        await Workout.update(
            { type, distance_km, elevation_m, duration_minutes, heart_rate_bpm, max_heart_rate_bpm, shoe_id: shoe_id || null },
            { where: { id: req.params.id, user_id: req.user.id } }
        );
        res.json({ message: "Aktivitas berhasil diupdate!" });
    } catch (error) { res.status(500).json({ error: "Gagal mengupdate" }); }
};

const deleteWorkout = async (req, res) => {
    try {
        await Workout.destroy({ where: { id: req.params.id, user_id: req.user.id } });
        res.json({ message: "Aktivitas berhasil dihapus!" });
    } catch (error) { res.status(500).json({ error: "Gagal menghapus aktivitas" }); }
};

const getSleeps = async (req, res) => {
    try {
        const sleeps = await Sleep.findAll({ where: { user_id: req.user.id } });
        res.json(sleeps);
    } catch (error) { res.status(500).json({ error: "Gagal mengambil data tidur" }); }
};

const createSleep = async (req, res) => {
    const { sleep_hours, sleep_minutes, sleep_quality, sleep_notes } = req.body;
    try {
        await Sleep.create({ sleep_hours, sleep_minutes: sleep_minutes || 0, sleep_quality, sleep_notes, user_id: req.user.id });
        res.status(201).json({ message: "Data tidur tersimpan!" });
    } catch (error) { res.status(500).json({ error: "Penyebab: " + error.message }); }
};

module.exports = { getWorkouts, createWorkout, updateWorkout, deleteWorkout, getSleeps, createSleep };

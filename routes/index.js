const express = require('express');
const router = express.Router();

const verifyToken = require('../middleware/VerifyToken.js');
const { register, login, getProfile, updateProfile } = require('../controllers/AuthController.js');
const { getWorkouts, createWorkout, updateWorkout, deleteWorkout, getSleeps, createSleep } = require('../controllers/WorkoutController.js');

// Rute Autentikasi
router.post('/api/register', register);
router.post('/api/login', login);

// Rute Workouts & Sleeps (Semua dilindungi verifyToken)
router.get('/api/workouts', verifyToken, getWorkouts);
router.post('/api/workouts', verifyToken, createWorkout);
router.put('/api/workouts/:id', verifyToken, updateWorkout);
router.delete('/api/workouts/:id', verifyToken, deleteWorkout);
router.get('/api/sleeps', verifyToken, getSleeps);
router.post('/api/sleeps', verifyToken, createSleep);
router.get('/api/users/:id', getProfile);
router.put('/api/users/:id', updateProfile);

module.exports = router;
const User = require('./User.js');
const Shoe = require('./Shoe.js');
const Workout = require('./Workout.js');
const Sleep = require('./Sleep.js'); 

// Relasi User & Workout
User.hasMany(Workout, { foreignKey: 'user_id' });
Workout.belongsTo(User, { foreignKey: 'user_id' });

// Relasi Shoe & Workout
Shoe.hasMany(Workout, { foreignKey: 'shoe_id' });
Workout.belongsTo(Shoe, { foreignKey: 'shoe_id' });

// Relasi User & Sleep (Tidur)
User.hasMany(Sleep, { foreignKey: 'user_id' });
Sleep.belongsTo(User, { foreignKey: 'user_id' });

// 2. PASTIKAN Sleep IKUT DI-EXPORT DI BAWAH INI
module.exports = { User, Shoe, Workout, Sleep };

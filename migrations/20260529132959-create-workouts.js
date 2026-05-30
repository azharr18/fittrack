'use strict';
module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('workouts', {
      id: { allowNull: false, autoIncrement: true, primaryKey: true, type: Sequelize.INTEGER },
      type: { type: Sequelize.STRING, allowNull: false },
      distance_km: { type: Sequelize.FLOAT },
      elevation_m: { type: Sequelize.INTEGER },
      duration_minutes: { type: Sequelize.INTEGER },
      heart_rate_bpm: { type: Sequelize.INTEGER },
      user_id: {
        type: Sequelize.INTEGER,
        references: { model: 'users', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'CASCADE'
      },
      shoe_id: {
        type: Sequelize.INTEGER,
        references: { model: 'shoes', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'SET NULL' // Jika sepatu dihapus, biarkan histori workout tetap ada (tanpa sepatu)
      }
    });
  },
  async down(queryInterface, Sequelize) {
    await queryInterface.dropTable('workouts');
  }
};
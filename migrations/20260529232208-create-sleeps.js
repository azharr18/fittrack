'use strict';
module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('sleeps', {
      id: { allowNull: false, autoIncrement: true, primaryKey: true, type: Sequelize.INTEGER },
      sleep_hours: { type: Sequelize.INTEGER, allowNull: false },
      sleep_minutes: { type: Sequelize.INTEGER, defaultValue: 0 },
      sleep_quality: { type: Sequelize.INTEGER, allowNull: false },
      sleep_notes: { type: Sequelize.TEXT },
      user_id: {
        type: Sequelize.INTEGER,
        references: { model: 'users', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'CASCADE'
      }
    });
  },
  async down(queryInterface, Sequelize) {
    await queryInterface.dropTable('sleeps');
  }
};
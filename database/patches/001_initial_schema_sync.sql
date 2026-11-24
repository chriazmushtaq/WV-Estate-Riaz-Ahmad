-- Baseline schema marker
CREATE TABLE IF NOT EXISTS `schema_migrations_test` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `created` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
DROP TABLE IF EXISTS `schema_migrations_test`;

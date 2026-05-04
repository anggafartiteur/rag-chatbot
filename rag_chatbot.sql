-- =============================================
-- RAG Chatbot – Database Schema
-- =============================================
-- Import file ini via phpMyAdmin atau MySQL CLI:
--   mysql -u root -p < rag_chatbot.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS `rag_chatbot`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `rag_chatbot`;

-- ---------------------------------------------
-- Tabel: users
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user (password: admin123) — SEGERA GANTI!
INSERT INTO `users` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ---------------------------------------------
-- Tabel: bot_settings
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `bot_settings` (
  `key`        VARCHAR(100) PRIMARY KEY,
  `value`      TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `bot_settings` (`key`, `value`) VALUES
('bot_name',      ''),
('persona',       ''),
('business_info', ''),
('language',      ''),
('tone',          ''),
('length',        ''),
('format',        ''),
('topic_limit',   ''),
('out_of_topic',  ''),
('unknown',       'honest'),
('closing',       '');

-- ---------------------------------------------
-- Tabel: ingest_sources
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `ingest_sources` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `type`       ENUM('file','mysql','url') NOT NULL,
  `label`      VARCHAR(255) NOT NULL,
  `config`     JSON NOT NULL,
  `enabled`    TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default source: folder knowledge/
INSERT INTO `ingest_sources` (`type`, `label`, `config`, `enabled`) VALUES
('file', 'Folder Knowledge', '{"path": "./knowledge"}', 1);

-- ---------------------------------------------
-- Tabel: ingest_log
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `ingest_log` (
  `id`      INT AUTO_INCREMENT PRIMARY KEY,
  `source`  VARCHAR(100),
  `chunks`  INT DEFAULT 0,
  `status`  ENUM('success','error') DEFAULT 'success',
  `message` TEXT,
  `ran_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------
-- Tabel: auto_ingest_schedule
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `auto_ingest_schedule` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `enabled`        TINYINT(1) DEFAULT 0,
  `interval_unit`  ENUM('minutes','hours','days') DEFAULT 'hours',
  `interval_value` INT DEFAULT 24,
  `last_run`       TIMESTAMP NULL,
  `next_run`       TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `auto_ingest_schedule` (`id`, `enabled`, `interval_unit`, `interval_value`) VALUES
(1, 0, 'hours', 24);

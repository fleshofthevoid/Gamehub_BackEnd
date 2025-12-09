CREATE DATABASE IF NOT EXISTS saper_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saper_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  difficulty ENUM('easy','medium','hard','extreme') NOT NULL,
  result ENUM('win','lose') NOT NULL,
  time_seconds INT NOT NULL,
  played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(user_id), INDEX(played_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stats (
  user_id INT PRIMARY KEY,
  total_games INT DEFAULT 0,
  wins INT DEFAULT 0,
  losses INT DEFAULT 0,
  best_time_easy INT DEFAULT NULL,
  best_time_medium INT DEFAULT NULL,
  best_time_hard INT DEFAULT NULL,
  best_time_extreme INT DEFAULT NULL,
  current_streak INT DEFAULT 0,
  best_streak INT DEFAULT 0,
  rating_points INT DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blockblast_scores (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  score INT NOT NULL,
  max_combo INT NOT NULL,
  played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(user_id),
  INDEX(score)
);
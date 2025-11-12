-- database.sql - Script de criação do banco de dados TechFit

CREATE DATABASE IF NOT EXISTS techfit_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE techfit_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    plan ENUM('Basic', 'Premium', 'Elite') DEFAULT 'Basic',
    member_since TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notifications INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de horários disponíveis
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time VARCHAR(20) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, appointment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de planos de treino
CREATE TABLE IF NOT EXISTS workout_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de exercícios dos planos de treino
CREATE TABLE IF NOT EXISTS workout_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workout_plan_id INT NOT NULL,
    exercise_name VARCHAR(100) NOT NULL,
    sets INT NOT NULL,
    reps INT NOT NULL,
    order_number INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workout_plan_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    INDEX idx_workout_plan (workout_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de treino (para estatísticas)
CREATE TABLE IF NOT EXISTS workout_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_plan_id INT,
    workout_date DATE NOT NULL,
    duration_minutes INT,
    calories_burned INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_plan_id) REFERENCES workout_plans(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, workout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados de exemplo
INSERT INTO workout_logs (user_id, workout_plan_id, workout_date, duration_minutes, calories_burned, notes)
VALUES
(1, 1, CURDATE(), 45, 280, 'Treino rápido de teste para verificar integração.');

-- Usuário de teste
INSERT INTO users (name, email, password, phone, plan, member_since, notifications) VALUES
('João Silva', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(11) 99999-9999', 'Premium', '2024-01-01', 3);

-- Horários disponíveis
INSERT INTO schedules (time, available) VALUES
('06:00 - 07:00', TRUE),
('07:00 - 08:00', TRUE),
('18:00 - 19:00', FALSE),
('19:00 - 20:00', TRUE),
('20:00 - 21:00', TRUE),
('21:00 - 22:00', FALSE);

-- Planos de treino de exemplo
INSERT INTO workout_plans (user_id, name) VALUES
(1, 'Treino A - Peito e Tríceps'),
(1, 'Treino B - Costas e Bíceps');

-- Exercícios do Treino A
INSERT INTO workout_exercises (workout_plan_id, exercise_name, sets, reps, order_number) VALUES
(1, 'Supino Reto', 4, 12, 1),
(1, 'Supino Inclinado', 3, 10, 2),
(1, 'Crucifixo', 3, 12, 3),
(1, 'Tríceps Testa', 3, 12, 4),
(1, 'Tríceps Corda', 3, 15, 5);

-- Exercícios do Treino B
INSERT INTO workout_exercises (workout_plan_id, exercise_name, sets, reps, order_number) VALUES
(2, 'Puxada Frontal', 4, 12, 1),
(2, 'Remada Curvada', 3, 10, 2),
(2, 'Pullover', 3, 12, 3),
(2, 'Rosca Direta', 3, 12, 4),
(2, 'Rosca Martelo', 3, 15, 5);

-- Agendamentos de exemplo
INSERT INTO appointments (user_id, schedule_id, title, appointment_date, status) VALUES
(1, 1, 'Avaliação Física Mensal', CURDATE(), 'pending'),
(1, 2, 'Consulta com Personal Trainer', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'pending'),
(1, 4, 'Treino de Pernas', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'confirmed');

-- Logs de treino para estatísticas
INSERT INTO workout_logs (user_id, workout_plan_id, workout_date, duration_minutes, calories_burned)
VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 60, 350),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 55, 320),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 60, 350),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 55, 320),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 60, 350),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 55, 320);

CREATE DATABASE techfit;
USE techfit;

-- ====================================
-- Tabela: usuarios
-- ====================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    plano VARCHAR(50) DEFAULT 'Standard',
    nivel_acesso VARCHAR(50) NOT NULL DEFAULT 'comum',
    status VARCHAR(20) NOT NULL DEFAULT 'ativo',
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL,
    access_token VARCHAR(100) NULL,
    foto VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ====================================
-- Tabela: agendamentos
-- ====================================
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_treino DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    tipo_treino VARCHAR(50) DEFAULT 'Musculação',
    status VARCHAR(20) DEFAULT 'agendado', -- agendado, concluido, cancelado
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ====================================
-- Tabela: planos_treino
-- ====================================
CREATE TABLE planos_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_treino VARCHAR(100) NOT NULL, -- Ex: "Treino A"
    descricao TEXT,                    -- Ex: exercícios
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ====================================
-- Tabela: notificacoes
-- ====================================
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensagem VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) DEFAULT 'info', -- info, success, warning
    lida TINYINT(1) DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE DATABASE techfit;
USE techfit;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE usuarios
ADD COLUMN nivel_acesso VARCHAR(50) NOT NULL DEFAULT 'comum';
ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(20) AFTER email;
ALTER TABLE usuarios ADD COLUMN plano VARCHAR(50) DEFAULT 'Standard';
ALTER TABLE usuarios 
ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'ativo';
ALTER TABLE usuarios 
ADD COLUMN reset_token VARCHAR(255) NULL, 
ADD COLUMN reset_expires DATETIME NULL;


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

INSERT INTO agendamentos (usuario_id, data_treino, hora_inicio, hora_fim, tipo_treino, status)
SELECT id, CURDATE(), '19:00:00', '20:00:00', 'Treino C - Pernas', 'agendado'
FROM usuarios
WHERE email = 'teste@email.com.br'; -- COLOQUE SEU EMAIL DE LOGIN AQUI

CREATE TABLE planos_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_treino VARCHAR(100) NOT NULL, -- Ex: "Treino A", "Costas e Bíceps"
    descricao TEXT, -- Ex: "Supino 4x12, Crucifixo 3x10..."
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensagem VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) DEFAULT 'info', -- 'info', 'success', 'warning'
    lida TINYINT(1) DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Troque '3' pelo ID do seu usuário
INSERT INTO agendamentos (usuario_id, data_treino, hora_inicio, hora_fim, tipo_treino, status) VALUES 
(3, CURDATE(), '10:00', '11:00', 'Teste Streak Hoje', 'concluido'),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:00', '11:00', 'Teste Streak Ontem', 'concluido'),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:00', '11:00', 'Teste Streak Anteontem', 'concluido');

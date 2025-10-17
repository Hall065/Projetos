-- Script SQL - Banco de Dados TechFit
CREATE DATABASE techfit;
USE techfit;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    tipo_usuario ENUM('aluno', 'admin', 'instrutor') DEFAULT 'aluno',
    plano ENUM('Básico', 'Premium', 'VIP') DEFAULT 'Básico',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_instrutor INT,
    data_hora DATETIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado') DEFAULT 'pendente',
    tipo VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_instrutor) REFERENCES usuarios(id_usuario)
);

CREATE TABLE treinos (
    id_treino INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE exercicios (
    id_exercicio INT AUTO_INCREMENT PRIMARY KEY,
    id_treino INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    series INT NOT NULL,
    repeticoes INT NOT NULL,
    FOREIGN KEY (id_treino) REFERENCES treinos(id_treino)
);

CREATE TABLE atividades (
    id_atividade INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    descricao TEXT NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo VARCHAR(50),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE agenda_treinos (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    status ENUM('ativo', 'concluido', 'cancelado') DEFAULT 'ativo',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

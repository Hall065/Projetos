INSERT INTO usuarios (nome, email, senha, telefone, tipo_usuario, plano)
VALUES
('Lucas Silva', 'lucas.silva@email.com', 'senha123', '11999998888', 'aluno', 'Básico'),
('Maria Oliveira', 'maria.oliveira@email.com', 'senha123', '11988887777', 'aluno', 'Premium'),
('Pedro Santos', 'pedro.santos@email.com', 'senha123', '11977776666', 'aluno', 'VIP'),
('Ana Costa', 'ana.costa@email.com', 'senha123', '11966665555', 'instrutor', 'VIP'),
('Rafael Lima', 'rafael.lima@email.com', 'senha123', '11955554444', 'instrutor', 'Básico'),
('Carla Souza', 'carla.souza@email.com', 'senha123', '11944443333', 'instrutor', 'Premium'),
('Tiago Fernandes', 'tiago.fernandes@email.com', 'senha123', '11933332222', 'aluno', 'Básico'),
('Fernanda Rocha', 'fernanda.rocha@email.com', 'senha123', '11922221111', 'aluno', 'Premium'),
('Bruno Martins', 'bruno.martins@email.com', 'senha123', '11911110000', 'aluno', 'VIP'),
('Juliana Pereira', 'juliana.pereira@email.com', 'senha123', '11900009999', 'aluno', 'Básico');

INSERT INTO agendamentos (id_usuario, id_instrutor, data_hora, status, tipo)
VALUES
(1, 4, '2025-10-18 08:00:00', 'pendente', 'musculação'),
(2, 5, '2025-10-18 09:00:00', 'confirmado', 'pilates'),
(3, 4, '2025-10-19 10:00:00', 'confirmado', 'HIIT'),
(7, 5, '2025-10-19 11:00:00', 'cancelado', 'yoga'),
(8, 4, '2025-10-20 08:30:00', 'pendente', 'musculação'),
(9, 6, '2025-10-20 09:30:00', 'confirmado', 'spinning'),
(10, 5, '2025-10-21 10:30:00', 'pendente', 'crossfit'),
(1, 6, '2025-10-21 11:30:00', 'confirmado', 'alongamento'),
(2, 4, '2025-10-22 08:00:00', 'pendente', 'musculação'),
(3, 5, '2025-10-22 09:00:00', 'confirmado', 'pilates');

INSERT INTO treinos (id_usuario, nome, descricao)
VALUES
(1, 'Treino Full Body', 'Treino completo para todo o corpo.'),
(2, 'Treino HIIT', 'Treino de alta intensidade intervalada.'),
(3, 'Treino Cardio', 'Foco em exercícios aeróbicos.'),
(7, 'Treino Força', 'Exercícios de musculação com pesos.'),
(8, 'Treino Flexibilidade', 'Alongamentos e yoga.'),
(9, 'Treino Resistência', 'Exercícios para resistência muscular.'),
(10, 'Treino Pernas', 'Foco em quadríceps e glúteos.'),
(1, 'Treino Peito e Braço', 'Exercícios de peito e braço.'),
(2, 'Treino Core', 'Exercícios para abdômen e lombar.'),
(3, 'Treino Spinning', 'Treino de bicicleta indoor.');

INSERT INTO exercicios (id_treino, nome, series, repeticoes)
VALUES
(1, 'Agachamento', 4, 12),
(1, 'Supino', 3, 10),
(2, 'Burpee', 5, 15),
(2, 'Mountain Climber', 4, 20),
(3, 'Corrida', 1, 30),
(4, 'Levantamento Terra', 4, 10),
(5, 'Alongamento de Pernas', 3, 15),
(6, 'Flexão de Braço', 4, 12),
(7, 'Leg Press', 4, 12),
(8, 'Rosca Direta', 3, 10);

INSERT INTO atividades (id_usuario, descricao, tipo)
VALUES
(1, 'Participou de aula de HIIT', 'treino'),
(2, 'Acompanhamento nutricional', 'nutrição'),
(3, 'Aula de yoga', 'bem-estar'),
(4, 'Orientação de treino', 'instrutor'),
(5, 'Sessão de pilates', 'treino'),
(6, 'Avaliação física', 'checkup'),
(7, 'Aula de spinning', 'treino'),
(8, 'Planejamento de treino', 'instrutor'),
(9, 'Treino funcional', 'treino'),
(10, 'Aula de alongamento', 'bem-estar');

INSERT INTO agenda_treinos (id_usuario, titulo, data_inicio, data_fim, status)
VALUES
(1, 'Treino Manhã', '2025-10-18 08:00:00', '2025-10-18 09:00:00', 'ativo'),
(2, 'Treino HIIT', '2025-10-18 09:00:00', '2025-10-18 10:00:00', 'concluido'),
(3, 'Treino Cardio', '2025-10-19 10:00:00', '2025-10-19 11:00:00', 'ativo'),
(4, 'Treino Força', '2025-10-19 11:00:00', '2025-10-19 12:00:00', 'ativo'),
(5, 'Treino Flex', '2025-10-20 08:30:00', '2025-10-20 09:30:00', 'concluido'),
(6, 'Treino Spinning', '2025-10-20 09:30:00', '2025-10-20 10:30:00', 'cancelado'),
(7, 'Treino Resistência', '2025-10-21 10:30:00', '2025-10-21 11:30:00', 'ativo'),
(8, 'Treino Pernas', '2025-10-21 11:30:00', '2025-10-21 12:30:00', 'ativo'),
(9, 'Treino Peito', '2025-10-22 08:00:00', '2025-10-22 09:00:00', 'ativo'),
(10, 'Treino Core', '2025-10-22 09:00:00', '2025-10-22 10:00:00', 'concluido');

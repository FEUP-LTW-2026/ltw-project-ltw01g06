-- Dados de exemplo para popular a base de dados.
-- Executar após database.sql
-- Senhas: admin → admin123 | treinadores → trainer123 | membros → user123

PRAGMA foreign_keys = ON;

-- ============================================================
-- POPULAÇÃO
-- ============================================================

-- UTILIZADORES (Senhas: admin123, trainer123, user123)
INSERT INTO users (full_name, username, email, password_hash, role, bio) VALUES
    ('Cláudio Admin', 'admin', 'admin@gym.pt', '$2b$12$ieaOB/Lvpw.jqZ/1gc3am.9dup5kkjrokobIk87BkhvqhTM8dsSgW', 'admin', 'Diretor Geral.'),
    ('Sofia Neves', 'sofia_t', 'sofia@gym.pt', '$2b$12$qM66jEGJcxC/OgqpgqnCAud8RG/EnwgVd.nc/LcpKyCHjZRPiGxDe', 'trainer', 'Especialista em HIIT.'),
    ('Marco Silva', 'marco_t', 'marco@gym.pt', '$2b$12$qM66jEGJcxC/OgqpgqnCAud8RG/EnwgVd.nc/LcpKyCHjZRPiGxDe', 'trainer', 'Coach de Powerlifting.'),
    ('Tiago Aluno', 'tiago_m', 'tiago@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Foco em força.'),
    ('Ana Rita', 'ana_m', 'ana@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Adoro Yoga.'),
    ('Bruno Vaz', 'bruno_m', 'bruno@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', NULL),
    ('Catarina S.', 'cat_m', 'cat@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', NULL),
    ('David Luiz', 'david_m', 'david@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Treino matinal.');

INSERT INTO trainer_profiles (user_id, specializations, certifications) VALUES
    (2, 'HIIT, Zumba, Perda de Peso', 'NASM Certified Personal Trainer'),
    (3, 'Powerlifting, Bodybuilding', 'IPF Level 2 Coach');

INSERT INTO classes (name, description, type, level, trainer_id, day_of_week, start_time, capacity) VALUES
    ('Yoga Flow', 'Equilíbrio e mente.', 'Yoga', 'beginner', 2, 'monday', '08:00', 20),
    ('HIIT Abs', 'Intensidade pura.', 'HIIT', 'intermediate', 2, 'tuesday', '18:30', 15),
    ('Supino Pesado', 'Técnica de peito.', 'Weights', 'advanced', 3, 'thursday', '17:00', 3), -- Pequena para waitlist
    ('Zumba Party', 'Dança latina.', 'Zumba', 'beginner', 2, 'friday', '20:00', 30),
    ('Leg Day', 'Foco em quadríceps.', 'Weights', 'intermediate', 3, 'wednesday', '10:00', 10);

INSERT INTO enrollments (user_id, class_id, status) VALUES 
    (4, 3, 'active'), (5, 3, 'active'), (6, 3, 'active');

INSERT INTO waitlist (user_id, class_id, position) VALUES (7, 3, 1), (8, 3, 2);

INSERT INTO equipment (name, category, quantity, available, status) VALUES
    ('Passadeira T90', 'Cardio', 10, 8, 'available'),
    ('Bicicleta Spin', 'Cardio', 15, 15, 'available'),
    ('Rack de Squat', 'Weights', 4, 3, 'available'),
    ('Leg Press', 'Weights', 1, 0, 'maintenance');

INSERT INTO reviews (user_id, class_id, rating, comment) VALUES
    (4, 3, 5, 'Melhor treino de força!'),
    (5, 1, 4, 'Muito relaxante.');

INSERT INTO analytics_snapshots (metric_key, metric_value) VALUES
    ('most_popular', 'HIIT'),
    ('peak_hour', '18:30'),
    ('retention_rate', '88%');
-- ============================================================
-- DADOS EXTRA — mais aulas, membros e reviews
-- ============================================================

-- Membros adicionais (password: user123)
INSERT OR IGNORE INTO users (full_name, username, email, password_hash, role, bio) VALUES
    ('Marta Sousa',    'marta_m',  'marta@gmail.com',   '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Apaixonada por yoga e pilates.'),
    ('Ricardo Lima',   'ricardo_m','ricardo@gmail.com',  '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Foco em cardio e resistência.'),
    ('Inês Carvalho',  'ines_m',   'ines@gmail.com',    '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', NULL),
    ('Gonçalo Martins','goncalo_m','goncalo@gmail.com',  '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', 'Ex-atleta de natação.'),
    ('Beatriz Costa',  'beatriz_m','beatriz@gmail.com',  '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member', NULL);

-- Treinador extra (password: trainer123)
INSERT OR IGNORE INTO users (full_name, username, email, password_hash, role, bio) VALUES
    ('Pedro Rodrigues','pedro_t',  'pedro@gym.pt',      '$2b$12$qM66jEGJcxC/OgqpgqnCAud8RG/EnwgVd.nc/LcpKyCHjZRPiGxDe', 'trainer', 'Especialista em Pilates Clínico e reabilitação.');

INSERT OR IGNORE INTO trainer_profiles (user_id, specializations, certifications)
    SELECT id, 'Pilates, Reabilitação, Core', 'BASI Pilates Certified, FMS Level 2'
    FROM users WHERE username = 'pedro_t';

-- Aulas adicionais
INSERT OR IGNORE INTO classes (name, description, type, level, trainer_id, day_of_week, start_time, duration_min, capacity) VALUES
    ('Pilates Core',    'Fortalecimento do core e postura.',     'Pilates', 'beginner',     (SELECT id FROM users WHERE username='pedro_t'),  'tuesday',   '09:00', 50, 12),
    ('Spinning Power',  'Cardio de alta intensidade em bicicleta.','Spinning','intermediate',(SELECT id FROM users WHERE username='sofia_t'), 'wednesday', '18:30', 45, 20),
    ('Zumba Gold',      'Dança e fitness para todos os níveis.', 'Zumba',   'beginner',     (SELECT id FROM users WHERE username='sofia_t'),  'saturday',  '10:00', 60, 25),
    ('HIIT Express',    'Treino intervalado de 30 minutos.',     'HIIT',    'advanced',     (SELECT id FROM users WHERE username='marco_t'),  'monday',    '07:00', 30, 10),
    ('Musculação Guiada','Técnica e segurança no treino de força.','Weights','intermediate', (SELECT id FROM users WHERE username='marco_t'), 'friday',    '11:00', 60, 8),
    ('Yoga Restaurativo','Relaxamento profundo e flexibilidade.', 'Yoga',   'beginner',     (SELECT id FROM users WHERE username='sofia_t'),  'sunday',    '09:30', 75, 15);

-- Inscrições dos novos membros nas aulas
INSERT OR IGNORE INTO enrollments (user_id, class_id, status)
    SELECT u.id, c.id, 'active'
    FROM users u, classes c
    WHERE (u.username = 'marta_m'   AND c.name IN ('Yoga Flow', 'Pilates Core'))
       OR (u.username = 'ricardo_m' AND c.name IN ('Spinning Power', 'HIIT Express', 'HIIT Abs'))
       OR (u.username = 'ines_m'    AND c.name IN ('Zumba Gold', 'Yoga Flow'))
       OR (u.username = 'goncalo_m' AND c.name IN ('Leg Day', 'Musculação Guiada'))
       OR (u.username = 'beatriz_m' AND c.name IN ('Yoga Flow', 'Zumba Gold', 'Pilates Core'))
       OR (u.username = 'tiago_m'   AND c.name IN ('Spinning Power', 'HIIT Express'))
       OR (u.username = 'ana_m'     AND c.name IN ('Yoga Restaurativo', 'Yoga Flow'))
       OR (u.username = 'bruno_m'   AND c.name IN ('Leg Day', 'Spinning Power'));

-- Reviews
INSERT OR IGNORE INTO reviews (user_id, class_id, rating, comment)
    SELECT u.id, c.id, 5, 'Aula incrível, treinador muito profissional!'
    FROM users u, classes c WHERE u.username = 'marta_m'   AND c.name = 'Yoga Flow';

INSERT OR IGNORE INTO reviews (user_id, class_id, rating, comment)
    SELECT u.id, c.id, 4, 'Excelente para quem quer começar.'
    FROM users u, classes c WHERE u.username = 'beatriz_m' AND c.name = 'Pilates Core';

INSERT OR IGNORE INTO reviews (user_id, class_id, rating, comment)
    SELECT u.id, c.id, 5, 'O melhor treino de spinning que já fiz!'
    FROM users u, classes c WHERE u.username = 'ricardo_m' AND c.name = 'Spinning Power';

INSERT OR IGNORE INTO reviews (user_id, class_id, rating, comment)
    SELECT u.id, c.id, 4, 'Muito intenso mas muito bom.'
    FROM users u, classes c WHERE u.username = 'tiago_m'   AND c.name = 'HIIT Abs';

INSERT OR IGNORE INTO reviews (user_id, class_id, rating, comment)
    SELECT u.id, c.id, 5, 'Recomendo a toda a gente!'
    FROM users u, classes c WHERE u.username = 'ana_m'     AND c.name = 'Yoga Restaurativo';

-- Equipamento adicional
INSERT OR IGNORE INTO equipment (name, category, quantity, available, status, notes) VALUES
    ('Elíptica Technogym', 'Cardio',   6,  5, 'available',   NULL),
    ('Remo WaterRower',    'Cardio',   4,  3, 'available',   NULL),
    ('Rack de Peso Livre', 'Weights',  3,  3, 'available',   'Halteres 2–40 kg'),
    ('Tapete de Yoga',     'Yoga',    30, 28, 'available',   NULL),
    ('Bola de Pilates',    'Pilates', 20, 18, 'available',   NULL),
    ('TRX Suspension',     'Funcional', 8,  6, 'available',  NULL),
    ('Corda de Saltar',    'Funcional',15, 15, 'available',  NULL),
    ('Kettlebell Set',     'Weights',  2,  1, 'maintenance', 'Em reparação');
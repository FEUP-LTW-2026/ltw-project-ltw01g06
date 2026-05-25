PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name     TEXT    NOT NULL,
    username      TEXT    NOT NULL UNIQUE,
    email         TEXT    NOT NULL UNIQUE,
    password_hash TEXT    NOT NULL,
    role          TEXT    NOT NULL DEFAULT 'member'
                          CHECK(role IN ('member', 'trainer', 'admin')),
    photo         TEXT    DEFAULT 'default_profile.png',
    bio           TEXT,
    is_active     INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    created_at    TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at    TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS trainer_profiles (
    user_id         INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    specializations TEXT,
    certifications  TEXT,
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS classes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    description  TEXT,
    type         TEXT    NOT NULL,
    level        TEXT    NOT NULL DEFAULT 'beginner'
                         CHECK(level IN ('beginner', 'intermediate', 'advanced')),
    trainer_id   INTEGER REFERENCES users(id) ON DELETE SET NULL,
    day_of_week  TEXT    NOT NULL
                         CHECK(day_of_week IN ('monday','tuesday','wednesday',
                                               'thursday','friday','saturday','sunday')),
    start_time   TEXT    NOT NULL, 
    duration_min INTEGER NOT NULL DEFAULT 60 CHECK(duration_min > 0),
    capacity     INTEGER NOT NULL DEFAULT 20 CHECK(capacity > 0),
    is_active    INTEGER NOT NULL DEFAULT 1  CHECK(is_active IN (0, 1)),
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS enrollments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id    INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    status      TEXT    NOT NULL DEFAULT 'active'
                        CHECK(status IN ('active', 'cancelled')),
    enrolled_at TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id)
);

CREATE TABLE IF NOT EXISTS waitlist (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id   INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id  INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    position  INTEGER NOT NULL CHECK(position > 0),
    joined_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id),
    UNIQUE(class_id, position)
);

CREATE TABLE IF NOT EXISTS equipment (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    category   TEXT,
    quantity   INTEGER NOT NULL DEFAULT 1 CHECK(quantity >= 0),
    available  INTEGER NOT NULL DEFAULT 1
                       CHECK(available >= 0 AND available <= quantity),
    status     TEXT    NOT NULL DEFAULT 'available'
                       CHECK(status IN ('available', 'maintenance', 'retired')),
    notes      TEXT,
    updated_at TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS reviews (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id   INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    rating     INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id)
);

CREATE TABLE IF NOT EXISTS analytics_snapshots (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    metric_key    TEXT NOT NULL,
    metric_value  TEXT NOT NULL,
    snapshot_date TEXT NOT NULL DEFAULT (date('now')),
    UNIQUE(metric_key, snapshot_date)
);

CREATE TRIGGER IF NOT EXISTS trg_users_updated_at
AFTER UPDATE ON users FOR EACH ROW BEGIN
    UPDATE users SET updated_at = datetime('now') WHERE id = OLD.id;
END;

CREATE TRIGGER IF NOT EXISTS trg_check_capacity
BEFORE INSERT ON enrollments
FOR EACH ROW WHEN NEW.status = 'active' BEGIN
    SELECT RAISE(ABORT, 'Class is full')
    WHERE (SELECT COUNT(*) FROM enrollments WHERE class_id = NEW.class_id AND status = 'active') 
          >= (SELECT capacity FROM classes WHERE id = NEW.class_id);
END;

CREATE TRIGGER IF NOT EXISTS trg_promote_from_waitlist
AFTER UPDATE OF status ON enrollments
FOR EACH ROW WHEN OLD.status = 'active' AND NEW.status = 'cancelled' BEGIN
    INSERT INTO enrollments (user_id, class_id, status)
    SELECT user_id, class_id, 'active' FROM waitlist WHERE class_id = OLD.class_id ORDER BY position ASC LIMIT 1;

    DELETE FROM waitlist WHERE user_id = (SELECT user_id FROM waitlist WHERE class_id = OLD.class_id ORDER BY position ASC LIMIT 1) AND class_id = OLD.class_id;
    UPDATE waitlist SET position = position - 1 WHERE class_id = OLD.class_id;
END;

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

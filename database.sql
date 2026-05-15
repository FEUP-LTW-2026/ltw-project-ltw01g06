-- ============================================================
-- GYM MANAGEMENT SYSTEM 
-- ============================================================

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

CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

CREATE TRIGGER IF NOT EXISTS trg_users_updated_at
AFTER UPDATE ON users FOR EACH ROW
BEGIN
    UPDATE users SET updated_at = datetime('now') WHERE id = OLD.id;
END;

CREATE TABLE IF NOT EXISTS trainer_profiles (
    user_id         INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    specializations TEXT,
    certifications  TEXT,
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TRIGGER IF NOT EXISTS trg_trainer_profiles_updated_at
AFTER UPDATE ON trainer_profiles FOR EACH ROW
BEGIN
    UPDATE trainer_profiles SET updated_at = datetime('now') WHERE user_id = OLD.user_id;
END;


CREATE TABLE IF NOT EXISTS classes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    description  TEXT,
    type         TEXT    NOT NULL, -- e.g., Yoga, HIIT, Pilates
    level        TEXT    NOT NULL DEFAULT 'beginner'
                         CHECK(level IN ('beginner', 'intermediate', 'advanced')),
    trainer_id   INTEGER REFERENCES users(id) ON DELETE SET NULL,
    day_of_week  TEXT    NOT NULL
                         CHECK(day_of_week IN ('monday','tuesday','wednesday',
                                               'thursday','friday','saturday','sunday')),
    start_time   TEXT    NOT NULL, -- format HH:MM
    duration_min INTEGER NOT NULL DEFAULT 60 CHECK(duration_min > 0),
    capacity     INTEGER NOT NULL DEFAULT 20 CHECK(capacity > 0),
    is_active    INTEGER NOT NULL DEFAULT 1  CHECK(is_active IN (0, 1)),
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_classes_trainer   ON classes(trainer_id);
CREATE INDEX IF NOT EXISTS idx_classes_day       ON classes(day_of_week);
CREATE INDEX IF NOT EXISTS idx_classes_type      ON classes(type);

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

CREATE TRIGGER IF NOT EXISTS trg_check_capacity
BEFORE INSERT ON enrollments
FOR EACH ROW
WHEN NEW.status = 'active'
BEGIN
    SELECT RAISE(ABORT, 'Class is full')
    WHERE (
        SELECT COUNT(*) FROM enrollments
        WHERE class_id = NEW.class_id AND status = 'active'
    ) >= (SELECT capacity FROM classes WHERE id = NEW.class_id);
END;

CREATE TABLE IF NOT EXISTS waitlist (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id   INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id  INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    position  INTEGER NOT NULL CHECK(position > 0),
    joined_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id),
    UNIQUE(class_id, position)
);

CREATE TRIGGER IF NOT EXISTS trg_promote_from_waitlist
AFTER UPDATE OF status ON enrollments
FOR EACH ROW
WHEN OLD.status = 'active' AND NEW.status = 'cancelled'
BEGIN
    INSERT INTO enrollments (user_id, class_id, status)
    SELECT user_id, class_id, 'active'
    FROM waitlist
    WHERE class_id = OLD.class_id
    ORDER BY position ASC
    LIMIT 1;

    DELETE FROM waitlist
    WHERE user_id = (
        SELECT user_id FROM waitlist 
        WHERE class_id = OLD.class_id 
        ORDER BY position ASC LIMIT 1
    ) AND class_id = OLD.class_id;

    -- Reordena as posições restantes
    UPDATE waitlist 
    SET position = position - 1 
    WHERE class_id = OLD.class_id;
END;

CREATE TABLE IF NOT EXISTS equipment (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    category   TEXT,
    quantity   INTEGER NOT NULL DEFAULT 1 CHECK(quantity >= 0),
    available  INTEGER NOT NULL DEFAULT 1
                       CHECK(available >= 0 AND available <= quantity),
    status     TEXT    NOT NULL DEFAULT 'available'
                       CHECK(status IN ('available', 'maintenance', 'retired')),
    updated_at TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS equipment_logs (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    equipment_id  INTEGER REFERENCES equipment(id) ON DELETE CASCADE,
    change_type   TEXT CHECK(change_type IN ('usage_start', 'usage_end', 'maintenance')),
    timestamp     TEXT DEFAULT (datetime('now'))
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

CREATE TRIGGER IF NOT EXISTS trg_validate_review_enrollment
BEFORE INSERT ON reviews
FOR EACH ROW
BEGIN
    SELECT RAISE(ABORT, 'You can only review classes you have enrolled in.')
    WHERE NOT EXISTS (
        SELECT 1 FROM enrollments 
        WHERE user_id = NEW.user_id AND class_id = NEW.class_id
    );
END;


CREATE TABLE IF NOT EXISTS analytics_snapshots (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    metric_key    TEXT NOT NULL, -- e.g., 'popular_class', 'retention_rate'
    metric_value  TEXT NOT NULL,
    snapshot_date TEXT NOT NULL DEFAULT (date('now')),
    UNIQUE(metric_key, snapshot_date)
);

INSERT OR IGNORE INTO users (full_name, username, email, password_hash, role) VALUES
    ('Admin User', 'admin', 'admin@gym.com', '$2b$12$ieaOB/Lvpw.jqZ/1gc3am.9dup5kkjrokobIk87BkhvqhTM8dsSgW', 'admin'),
    ('John Trainer', 'jtrainer', 'john@gym.com', '$2b$12$qM66jEGJcxC/OgqpgqnCAud8RG/EnwgVd.nc/LcpKyCHjZRPiGxDe', 'trainer'),
    ('Alice Member', 'amember', 'alice@gmail.com', '$2b$12$Dx/lmPBWNSz1S4dh9VjWCejSFrKzSRoJXO3XQCF7PVazThI7NTtae', 'member');

INSERT OR IGNORE INTO trainer_profiles (user_id, specializations, certifications)
SELECT id, 'Yoga, Meditation', 'RYT-500' FROM users WHERE username = 'jtrainer';

INSERT OR IGNORE INTO classes (name, description, type, level, trainer_id, day_of_week, start_time, capacity)
SELECT 'Sunset Yoga', 'Relaxing yoga at the end of the day', 'Yoga', 'beginner', id, 'monday', '18:30', 20
FROM users WHERE username = 'jtrainer';
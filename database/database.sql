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
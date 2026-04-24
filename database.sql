CREATE TABLE users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name     TEXT    NOT NULL,
    username      TEXT    NOT NULL UNIQUE,
    email         TEXT    NOT NULL UNIQUE,
    password_hash TEXT    NOT NULL,
    role          TEXT    NOT NULL DEFAULT 'member'
                          CHECK(role IN ('member', 'trainer', 'admin')),
    photo         TEXT,                          -- not done
    bio           TEXT,                          -- not done
    created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
);


CREATE TABLE trainer_profiles (
    user_id         INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    specializations TEXT,
    certifications  TEXT
);

CREATE TABLE classes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    description  TEXT,
    type         TEXT    NOT NULL,
    level        TEXT    NOT NULL DEFAULT 'beginner'
                         CHECK(level IN ('beginner', 'intermediate', 'advanced')),
    trainer_id   INTEGER REFERENCES users(id) ON DELETE SET NULL,
    day_of_week  TEXT    NOT NULL,               
    start_time   TEXT    NOT NULL,               -- 'HH:MM'
    duration_min INTEGER NOT NULL DEFAULT 60,
    capacity     INTEGER NOT NULL DEFAULT 20
);

CREATE TABLE enrollments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id    INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    enrolled_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id)
);

CREATE TABLE equipment (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    name      TEXT    NOT NULL,
    quantity  INTEGER NOT NULL DEFAULT 1,
    available INTEGER NOT NULL DEFAULT 1 CHECK(available >= 0),
    status    TEXT    NOT NULL DEFAULT 'available'
                      CHECK(status IN ('available', 'maintenance', 'retired'))
);

CREATE TABLE reviews (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id)   ON DELETE CASCADE,
    class_id   INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    rating     INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, class_id)
);

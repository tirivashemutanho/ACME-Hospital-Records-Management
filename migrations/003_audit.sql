-- Migration: audit log
BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS audit (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    actor TEXT NOT NULL,
    action TEXT NOT NULL,
    target TEXT NULL,
    meta TEXT NULL,
    created_at TEXT NOT NULL
);

COMMIT;

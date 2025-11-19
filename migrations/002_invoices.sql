-- Migration: invoices table
BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_no TEXT UNIQUE NOT NULL,
    patient_id TEXT NOT NULL,
    created_at TEXT NOT NULL,
    amount REAL NOT NULL,
    data TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'issued'
);

COMMIT;

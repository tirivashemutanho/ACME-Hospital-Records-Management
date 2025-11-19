-- Migrations: create tables for patients, consultations, procedures

BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    age INTEGER NOT NULL,
    type TEXT NOT NULL, -- outpatient, inpatient, daycase
    admission_date TEXT NULL,
    discharge_date TEXT NULL,
    ward_number INTEGER NULL,
    daily_bed_charge REAL NULL,
    procedure_name TEXT NULL,
    theatre_fee REAL NULL
);

CREATE TABLE IF NOT EXISTS consultations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id TEXT NOT NULL,
    date TEXT NOT NULL,
    doctor TEXT NOT NULL,
    fee REAL NOT NULL,
    FOREIGN KEY(patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS procedures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id TEXT NOT NULL,
    name TEXT NOT NULL,
    cost REAL NOT NULL,
    FOREIGN KEY(patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

COMMIT;

-- Users table for simple authentication
BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL -- admin, doctor, biller
);
COMMIT;

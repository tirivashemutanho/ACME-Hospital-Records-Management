# Hospital Patient Record & Billing System (PHP)

This repository contains a simple PHP implementation of a hospital patient-record and billing system. It supports Outpatient, Inpatient, and Daycase patients. Each patient has a unique ID, name, age, and a list of consultations. Inpatients have extra attributes and the total bill calculation includes bed charges and procedures. Daycase patients are admitted and discharged the same day and have theatre fees.

## Implemented classes (≥ 6)

- `Patient` (abstract) — common properties: patientId, name, age, consultations; abstract method `getTotalBill()`.
- `Consultation` — date, doctor's name, fee.
- `ProcedurePerformed` — name, cost.
- `Outpatient` — extends `Patient`; `getTotalBill()` returns sum of consultation fees.
- `Inpatient` — extends `Patient`; adds admissionDate, dischargeDate, wardNumber, dailyBedCharge, procedures; `getTotalBill()` includes consultations + procedures + bedCharge * days.
- `DaycasePatient` — extends `Inpatient` but sets theatre fee and assumes same-day discharge; total bill includes consultation fees + theatre fee + procedures.
- `Ward` — basic ward management (capacity and admitted patients).
- `Invoice` — produce invoice summary and total amount for a patient.
- `Hospital` — manager class for patients and generating invoices.

Additional domain/service classes introduced in the redesign:

- `PatientRepository` — persist/load patient aggregates and associated consultations/procedures to SQLite.
- `DB` — small database helper (SQLite PDO singleton) used by repositories.

## UML Class Diagram (text / PlantUML)

Below is a simple PlantUML description. Paste into https://plantuml.com/ or a PlantUML tool to render.

```
@startuml
class Patient {
  - patientId: string
  - name: string
  - age: int
  - consultations: Consultation[]
  + addConsultation(c: Consultation)
  + getTotalBill(): float <<abstract>>
}

class Consultation {
  - date: string
  - doctor: string
  - fee: float
}

class ProcedurePerformed {
  - name: string
  - cost: float
}

class Outpatient {
  + getTotalBill(): float
}

class Inpatient {
  - admissionDate: string
  - dischargeDate: string
  - wardNumber: int
  - dailyBedCharge: float
  + getTotalBill(): float
  + addProcedure(p: ProcedurePerformed)
}

class DaycasePatient {
  - procedureName: string
  - theatreFee: float
  + getTotalBill(): float
}

class Hospital {
  - patients: Patient[]
  + addPatient(p: Patient)
  + invoicePatient(id: string): Invoice
}

class Invoice {
  - patient: Patient
  + getAmount(): float
  + getSummary(): string
}

Patient <|-- Outpatient
Patient <|-- Inpatient
Inpatient <|-- DaycasePatient
Patient "1" *-- "*" Consultation
Inpatient "1" *-- "*" ProcedurePerformed
Hospital "1" o-- "*" Patient
Invoice "1" o-- "1" Patient
@enduml
```

## Deployment Diagram (text)

We describe a simple high-level deployment diagram for a PHP-based system.

- Client Device(s): Web browser, Mobile App — used by doctors, administrators, billers.
- Web Server: PHP runtime (Apache/Nginx + PHP-FPM) — the application logic (the example code can be run on CLI or PHP server).
- Database: MySQL or PostgreSQL — persistent storage of patients, consultations, procedures, invoices.
- Storage: File storage for attachments (scans, attachments), optional NFS/S3.
- Admin Console: A protected web interface for hospital staff to view/create patients and invoices.

ASCII-style diagram:

```
[Client] ---> [Web Server: PHP app] ---> [Database]
                            |            
                            +--> [File Storage / S3]
                            +--> [Print queue/External Billing API]
```

If you want a dedicated server diagram, a possible layout uses a Load Balancer in front of multiple app servers and a separate DB server with replication.

## Billing Rules & Examples

- Outpatient bill = sum(consultation fees)
- Inpatient bill = sum(consultation fees) + sum(procedure costs) + dailyBedCharge * numberOfDays
- Daycase bill = sum(consultation fees) + theatre fee + sum(procedures)

## Quick CLI Demo

Run the demo script to see outputs. Requires PHP 8+.

```powershell
php demo.php
```

This will create sample Outpatient, Inpatient, and Daycase patients and print invoices.

## Estimation

Rough estimates for a minimal but robust implementation:

- Scoping & design: 4-6 hours
- Implement basic domain models & billing: 3-6 hours
- CLI demo and unit tests: 2-4 hours
- Persistence (DB models/migrations) + admin UI: 2-3 days
- Authentication & role management (doctor, admin, billing): 1-2 days
- Testing, performance tuning, and documentation: 1-2 days

Total Minimal Viable Product (MVP): ~3-5 days for a single developer (assuming standard stack and small hospital). A more feature-complete solution with UI and full persistence: ~2-4 weeks depending on complexity.

## Notes & Next Steps

- This repository is a demo — data is in-memory. For production, add DB storage (ORM or raw SQL), authentication, and UI.
- Add tests, validation, and stronger type checks for production use.
- To enable the provided SQLite persistence and web UI, run the migration and start the PHP built-in server:

```powershell
php migrate.php
php -S localhost:8000 -t public
```

Open http://localhost:8000 in your browser.

## New Pages & Workflows

- `public/index.php` — patient list dashboard
- `public/add_patient.php` — create patient workflow (Outpatient / Inpatient / Daycase)
- `public/patient.php` — patient detail page (add consultation, add procedure, discharge)
- `public/invoices.php` — list invoices and quick links to invoice view
- `public/view_invoice.php` — formatted invoice for a patient

Workflows supported:

- Create patient (Outpatient/Inpatient/Daycase)
- Add consultation to patient
- Add procedure to inpatient/daycase
- Admit/discharge inpatient
- Generate/view invoice

## Updated Estimation

Small project tasks and rough estimates (single developer):

- Add domain models & CLI demo: 4 hours
- SQLite persistence & repository: 6 hours
- Basic web UI (list/add/view): 8 hours
- Patient detail & workflow pages: 6 hours
- Styling & UX improvements: 4 hours
- Testing & minor fixes: 4 hours

Total: ~1.5 - 3 days to reach an MVP with the current codebase and features. A production-ready application with auth, tests, API, and robust validation would take longer (~2-3 weeks).

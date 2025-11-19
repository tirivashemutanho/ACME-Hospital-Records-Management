<?php

require_once __DIR__ . '/src/Consultation.php';
require_once __DIR__ . '/src/ProcedurePerformed.php';
require_once __DIR__ . '/src/Patient.php';
require_once __DIR__ . '/src/Outpatient.php';
require_once __DIR__ . '/src/Inpatient.php';
require_once __DIR__ . '/src/DaycasePatient.php';
require_once __DIR__ . '/src/Ward.php';
require_once __DIR__ . '/src/Invoice.php';
require_once __DIR__ . '/src/Hospital.php';

use Hospital\Consultation;
use Hospital\ProcedurePerformed;
use Hospital\Outpatient;
use Hospital\Inpatient;
use Hospital\DaycasePatient;
use Hospital\Hospital;

function genId(): string { return bin2hex(random_bytes(4)); }

// Sample Patients
$hospital = new Hospital();

$out = new Outpatient(genId(), 'John Doe', 32);
$out->addConsultation(new Consultation('2025-11-10', 'Adams', 40.0));
$out->addConsultation(new Consultation('2025-11-11', 'Baker', 60.0));
$hospital->addPatient($out);

$in = new Inpatient(genId(), 'Jane Roe', 45, '2025-11-01', 12, 80.0); // bed charge 80/day
$in->addConsultation(new Consultation('2025-11-01', 'Clark', 120.0));
$in->addProcedure(new ProcedurePerformed('Appendectomy', 900.0));
$in->discharge('2025-11-05');
$hospital->addPatient($in);

$day = new DaycasePatient(genId(), 'Mary Lane', 28, '2025-11-18', 15, 'Cataract removal', 300.0);
$day->addConsultation(new Consultation('2025-11-18', 'Davis', 80.0));
$day->addProcedure(new ProcedurePerformed('Cataract removal', 200.0));
$hospital->addPatient($day);

// Print invoices
foreach ($hospital->getAllPatients() as $patient) {
    $invoice = new \Hospital\Invoice($patient);
    if ($invoice) {
        echo "-------------------------------------\n";
        echo $invoice->getSummary() . "\n";
    }
}

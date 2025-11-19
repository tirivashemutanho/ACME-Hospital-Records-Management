<?php
require_once __DIR__ . '/bootstrap.php';

use Hospital\Consultation;
use Hospital\ProcedurePerformed;
use Hospital\Outpatient;
use Hospital\Inpatient;
use Hospital\DaycasePatient;

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'outpatient';
    $patientId = $_POST['patient_id'] ?: bin2hex(random_bytes(4));
    $name = $_POST['name'] ?? '';
    $age = (int)($_POST['age'] ?? 0);

    if (!$name) $errors[] = 'Name is required';
    if ($age <= 0) $errors[] = 'Age must be > 0';

    if (empty($errors)) {
        if ($type === 'outpatient') {
            $p = new Outpatient($patientId, $name, $age);
        } elseif ($type === 'inpatient') {
            $admission = $_POST['admission_date'] ?? date('Y-m-d');
            $ward = (int)($_POST['ward_number'] ?? 0);
            $daily = (float)($_POST['daily_bed_charge'] ?? 50.0);
            $p = new Inpatient($patientId, $name, $age, $admission, $ward, $daily);
            if (!empty($_POST['discharge_date'])) $p->discharge($_POST['discharge_date']);
        } else { // daycase
            $admission = $_POST['admission_date'] ?? date('Y-m-d');
            $ward = (int)($_POST['ward_number'] ?? 0);
            $procName = $_POST['procedure_name'] ?? '';
            $theatre = (float)($_POST['theatre_fee'] ?? 0.0);
            $p = new DaycasePatient($patientId, $name, $age, $admission, $ward, $procName, $theatre);
        }

        // add one consultation if provided
        if (!empty($_POST['consult_date']) && !empty($_POST['consult_doctor']) && is_numeric($_POST['consult_fee'])) {
            $p->addConsultation(new Consultation($_POST['consult_date'], $_POST['consult_doctor'], (float)$_POST['consult_fee']));
        }

        // add procedure if provided (only Inpatient/Daycase support procedures)
        if (!empty($_POST['proc_name']) && is_numeric($_POST['proc_cost']) && $p instanceof Inpatient) {
            $p->addProcedure(new ProcedurePerformed($_POST['proc_name'], (float)$_POST['proc_cost']));
        }

        $repo->save($p);
        header('Location: index.php');
        exit;
    }
}

?><!doctype html>
<html>
<head>
        <meta charset="utf-8">
        <title>Add Patient</title>
        <link rel="stylesheet" href="assets/style.css">
        <script src="assets/app.js" defer></script>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand"><div class="logo">H</div><div><div style="font-weight:700">Add Patient</div><div class="small">Create a new patient record</div></div></div>
        <div class="controls"><a class="btn secondary" href="index.php">Back to list</a></div>
    </div>

    <div class="card">
        <?php if ($errors): ?>
            <div style="color:var(--danger);margin-bottom:12px"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="field"><label class="small">Type</label>
                    <select name="type" class="input">
                        <option value="outpatient">Outpatient</option>
                        <option value="inpatient">Inpatient</option>
                        <option value="daycase">Daycase</option>
                    </select>
                </div>
                <div class="field"><label class="small">Patient ID (optional)</label><input name="patient_id" class="input"></div>
            </div>

            <div class="form-row">
                <div class="field"><label class="small">Name</label><input name="name" class="input"></div>
                <div class="field"><label class="small">Age</label><input name="age" type="number" class="input"></div>
            </div>

            <div class="fieldset card">
                <div class="legend">Consultation (optional)</div>
                <div class="form-row">
                    <div class="field"><input name="consult_date" type="date" class="input"></div>
                    <div class="field"><input name="consult_doctor" placeholder="Doctor" class="input"></div>
                    <div class="field"><input name="consult_fee" type="number" step="0.01" placeholder="Fee" class="input"></div>
                </div>
            </div>

            <div class="fieldset card">
                <div class="legend">Procedure (optional)</div>
                <div class="form-row">
                    <div class="field"><input name="proc_name" placeholder="Procedure name" class="input"></div>
                    <div class="field"><input name="proc_cost" type="number" step="0.01" placeholder="Cost" class="input"></div>
                </div>
            </div>

            <div class="fieldset card inpatient-field">
                <div class="legend">Inpatient / Daycase details</div>
                <div class="form-row">
                    <div class="field"><label class="small">Admission date</label><input name="admission_date" type="date" class="input"></div>
                    <div class="field"><label class="small">Discharge date</label><input name="discharge_date" type="date" class="input"></div>
                </div>
                <div class="form-row">
                    <div class="field"><label class="small">Ward number</label><input name="ward_number" type="number" class="input"></div>
                    <div class="field"><label class="small">Daily bed charge</label><input name="daily_bed_charge" type="number" step="0.01" class="input"></div>
                </div>
            </div>

            <div class="fieldset card daycase-field">
                <div class="legend">Daycase only</div>
                <div class="form-row">
                    <div class="field"><input name="procedure_name" placeholder="Procedure (daycase)" class="input"></div>
                    <div class="field"><input name="theatre_fee" type="number" step="0.01" placeholder="Theatre fee" class="input"></div>
                </div>
            </div>

            <div style="display:flex;gap:8px">
                <button class="btn" type="submit">Save</button>
                <a class="btn secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>

    <div class="footer">Made for demo purposes</div>
</div>
</body>
</html>
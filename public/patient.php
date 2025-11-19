<?php
require_once __DIR__ . '/bootstrap.php';

use Hospital\Consultation;
use Hospital\ProcedurePerformed;

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$p = $repo->getByPatientId($id);
if (!$p) {
    echo "Patient not found";
    exit;
}

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_consult') {
        $date = $_POST['consult_date'] ?? date('Y-m-d');
        $doctor = $_POST['consult_doctor'] ?? 'Unknown';
        $fee = (float)($_POST['consult_fee'] ?? 0);
        $p->addConsultation(new Consultation($date, $doctor, $fee));
        $repo->save($p);
        $messages[] = 'Consultation added';
    }
    if ($action === 'add_proc' && $p instanceof \Hospital\Inpatient) {
        $name = $_POST['proc_name'] ?? '';
        $cost = (float)($_POST['proc_cost'] ?? 0);
        if ($name) {
            $p->addProcedure(new ProcedurePerformed($name, $cost));
            $repo->save($p);
            $messages[] = 'Procedure added';
        }
    }
    if ($action === 'discharge' && $p instanceof \Hospital\Inpatient) {
        $dis = $_POST['discharge_date'] ?? date('Y-m-d');
        $p->discharge($dis);
        $repo->save($p);
        $messages[] = 'Patient discharged';
    }
    // reload fresh object
    $p = $repo->getByPatientId($id);
}

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Patient — <?php echo htmlspecialchars($p->getName()); ?></title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/app.js" defer></script>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">H</div><div><div style="font-weight:700">Patient</div><div class="small"><?php echo htmlspecialchars($p->getName()); ?></div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a> <a class="btn" href="view_invoice.php?id=<?php echo urlencode($p->getPatientId()); ?>">View Invoice</a></div>
  </div>

  <div class="card">
    <?php foreach ($messages as $m): ?>
      <div style="color:var(--success)"><?php echo htmlspecialchars($m); ?></div>
    <?php endforeach; ?>

    <h3>Details</h3>
    <div class="kv"><div class="small">Patient ID</div><div><?php echo htmlspecialchars($p->getPatientId()); ?></div></div>
    <div class="kv"><div class="small">Name</div><div><?php echo htmlspecialchars($p->getName()); ?></div></div>
    <div class="kv"><div class="small">Age</div><div><?php echo htmlspecialchars($p->getAge()); ?></div></div>

    <hr>
    <h4>Consultations</h4>
    <?php if (count($p->getConsultations())===0): ?>
      <div class="small">No consultations</div>
    <?php else: ?>
      <?php foreach ($p->getConsultations() as $c): ?>
        <div class="kv"><div><?php echo htmlspecialchars($c->getDate()); ?> — Dr <?php echo htmlspecialchars($c->getDoctor()); ?></div><div><?php echo number_format($c->getFee(),2); ?></div></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" style="margin-top:12px">
      <input type="hidden" name="action" value="add_consult">
      <div class="form-row">
        <div class="field"><input name="consult_date" type="date" class="input"></div>
        <div class="field"><input name="consult_doctor" placeholder="Doctor" class="input"></div>
        <div class="field"><input name="consult_fee" placeholder="Fee" type="number" step="0.01" class="input"></div>
      </div>
      <div style="margin-top:8px"><button class="btn" type="submit">Add Consultation</button></div>
    </form>

    <hr>
    <?php if ($p instanceof \Hospital\Inpatient): ?>
      <h4>Procedures</h4>
      <?php if (count($p->getProcedures())===0): ?>
        <div class="small">No procedures</div>
      <?php else: ?>
        <?php foreach ($p->getProcedures() as $pr): ?>
          <div class="kv"><div><?php echo htmlspecialchars($pr->getName()); ?></div><div><?php echo number_format($pr->getCost(),2); ?></div></div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form method="post" style="margin-top:12px">
        <input type="hidden" name="action" value="add_proc">
        <div class="form-row">
          <div class="field"><input name="proc_name" placeholder="Procedure name" class="input"></div>
          <div class="field"><input name="proc_cost" placeholder="Cost" type="number" step="0.01" class="input"></div>
        </div>
        <div style="margin-top:8px"><button class="btn" type="submit">Add Procedure</button></div>
      </form>

      <hr>
      <form method="post">
        <input type="hidden" name="action" value="discharge">
        <div class="form-row">
          <div class="field"><label class="small">Discharge date</label><input name="discharge_date" type="date" class="input"></div>
          <div class="field" style="align-self:end"><button class="btn" type="submit">Discharge</button></div>
        </div>
      </form>
    <?php endif; ?>

  </div>

  <div class="footer">Patient detail & workflows</div>
</div>
</body>
</html>

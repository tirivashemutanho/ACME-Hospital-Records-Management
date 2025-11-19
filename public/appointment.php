<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\AppointmentRepository;

$repoA = new AppointmentRepository();
$id = $_GET['id'] ?? null;
if (!$id) { echo "No appointment id"; exit; }
$row = $repoA->getById((int)$id);
if (!$row) { echo "Appointment not found"; exit; }
$p = $repo->getByPatientId($row['patient_id']);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Appointment <?php echo htmlspecialchars($row['id']); ?></title>
  <link rel="stylesheet" href="assets/style.css">
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">A</div><div><div style="font-weight:700">Appointment Detail</div><div class="small">Consultation information</div></div></div>
    <div class="controls"><a class="btn secondary" href="appointments.php">Back</a></div>
  </div>

  <div class="card">
    <h2><?php echo htmlspecialchars($p->getName()); ?> <span class="small">(<?php echo htmlspecialchars($p->getPatientId()); ?>)</span></h2>
    <div class="kv"><div class="small">When</div><div><?php echo htmlspecialchars($row['date']); ?></div></div>
    <div class="kv"><div class="small">Doctor</div><div><?php echo htmlspecialchars($row['doctor']); ?></div></div>
    <div class="kv"><div class="small">Fee</div><div><?php echo number_format($row['fee'],2); ?></div></div>
    <hr>
    <div class="small">Patient Summary</div>
    <div class="kv"><div class="small">Age</div><div><?php echo htmlspecialchars($p->getAge()); ?></div></div>
    <div class="kv"><div class="small">Type</div><div><?php
      if ($p instanceof \Hospital\Inpatient) echo 'Inpatient';
      elseif ($p instanceof \Hospital\DaycasePatient) echo 'Daycase';
      else echo 'Outpatient';
    ?></div></div>
  </div>

</div>
</body>
</html>

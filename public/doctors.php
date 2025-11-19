<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\AppointmentRepository;
$repoA = new AppointmentRepository();
$q = trim($_GET['q'] ?? '');
$all = $repoA->listDoctorsWithPatients();
// filter by query
if ($q) {
    $filtered = [];
    foreach ($all as $d) {
        if (stripos($d['doctor'], $q) !== false) $filtered[] = $d;
    }
    $doctors = $filtered;
} else {
    $doctors = $all;
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Doctors & Patients</title>
  <link rel="stylesheet" href="assets/style.css">
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">D</div><div><div style="font-weight:700">Doctors</div><div class="small">Doctors and their patients</div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a></div>
  </div>

  <div class="card">
    <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
      <input class="input" name="q" placeholder="Search doctors" value="<?php echo htmlspecialchars($q); ?>">
      <button class="btn" type="submit">Search</button>
    </form>

    <div class="grid doctors-grid">
    <?php foreach ($doctors as $doc): ?>
      <?php $patients = $doc['patients']; $count = count($patients); ?>
      <div class="doctor-card card">
        <div class="doctor-head">
          <div class="avatar"><?php echo htmlspecialchars(substr($doc['doctor'],0,1)); ?></div>
          <div>
            <div class="doctor-name"><?php echo htmlspecialchars($doc['doctor']); ?></div>
            <div class="small"><?php echo $count; ?> patient<?php echo $count!==1?'s':''; ?></div>
          </div>
        </div>
        <div class="doctor-body">
          <?php if (empty($patients)): ?>
            <div class="small">No patients</div>
          <?php else: ?>
            <div class="patient-list">
              <?php foreach ($patients as $pid): $pp = $repo->getByPatientId($pid); ?>
                <a class="patient-chip" href="patient.php?id=<?php echo urlencode($pid); ?>">
                  <strong><?php echo htmlspecialchars($pp ? $pp->getName() : $pid); ?></strong>
                  <div class="small"><?php echo htmlspecialchars($pid); ?></div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>

</div>
</body>
</html>

<?php
require_once __DIR__ . '/bootstrap.php';

// We'll reuse PatientRepository to get all patients and split them by class
$all = $repo->getAll();
$out = []; $in = []; $day = [];
foreach ($all as $p) {
    if ($p instanceof \Hospital\Inpatient) $in[] = $p;
    elseif ($p instanceof \Hospital\DaycasePatient) $day[] = $p;
    else $out[] = $p;
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Patients</title>
  <link rel="stylesheet" href="assets/style.css">
  <script>
    function showTab(id){
      document.querySelectorAll('.tab').forEach(e=>e.style.display='none');
      document.getElementById(id).style.display='block';
      document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
      document.querySelector('[data-target="'+id+'"]').classList.add('active');
    }
    window.addEventListener('load',()=>{ showTab('outpatients'); });
  </script>
  <style>
    .tab-btn{padding:8px 12px;border-radius:8px;background:#fff;border:1px solid #eef6ff;margin-right:8px}
    .tab-btn.active{background:var(--accent);color:#fff}
  </style>
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">P</div><div><div style="font-weight:700">Patients</div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a></div>
  </div>

  <div class="card">
    <div style="margin-bottom:12px">
      <button class="tab-btn" data-target="outpatients" onclick="showTab('outpatients')">Outpatients (<?php echo count($out); ?>)</button>
      <button class="tab-btn" data-target="inpatients" onclick="showTab('inpatients')">Inpatients (<?php echo count($in); ?>)</button>
      <button class="tab-btn" data-target="daycases" onclick="showTab('daycases')">Daycase Patients (<?php echo count($day); ?>)</button>
    </div>

    <div id="outpatients" class="tab">
      <h3>Outpatients</h3>
      <div class="patients-grid">
        <?php foreach ($out as $p): ?>
          <a class="patient-card" href="patient.php?id=<?php echo urlencode($p->getPatientId()); ?>">
            <div class="initial"><?php echo htmlspecialchars(substr($p->getName(),0,1)); ?></div>
            <div class="patient-meta">
              <strong><?php echo htmlspecialchars($p->getName()); ?></strong>
              <div class="small"><?php echo htmlspecialchars($p->getPatientId()); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="inpatients" class="tab" style="display:none">
      <h3>Inpatients</h3>
      <div class="patients-grid">
        <?php foreach ($in as $p): ?>
          <a class="patient-card" href="patient.php?id=<?php echo urlencode($p->getPatientId()); ?>">
            <div class="initial"><?php echo htmlspecialchars(substr($p->getName(),0,1)); ?></div>
            <div class="patient-meta">
              <strong><?php echo htmlspecialchars($p->getName()); ?></strong>
              <div class="small">Ward: <?php echo htmlspecialchars($p->getWardNumber()); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="daycases" class="tab" style="display:none">
      <h3>Daycase Patients</h3>
      <div class="patients-grid">
        <?php foreach ($day as $p): ?>
          <a class="patient-card" href="patient.php?id=<?php echo urlencode($p->getPatientId()); ?>">
            <div class="initial"><?php echo htmlspecialchars(substr($p->getName(),0,1)); ?></div>
            <div class="patient-meta">
              <strong><?php echo htmlspecialchars($p->getName()); ?></strong>
              <div class="small">Procedure: <?php echo htmlspecialchars(method_exists($p,'getProcedureName') ? $p->getProcedureName() : ''); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

</div>
</body>
</html>

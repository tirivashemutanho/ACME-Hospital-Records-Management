<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\AppointmentRepository;

$repoA = new AppointmentRepository();
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 25;
$offset = ($page - 1) * $per;
$rows = $repoA->search($q, $per, $offset);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Appointments</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>.clickable{cursor:pointer}</style>
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">A</div><div><div style="font-weight:700">Appointments</div><div class="small">Scheduled consultations</div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a></div>
  </div>

  <div class="card">
    <form method="get" style="display:flex;gap:8px">
      <input class="input" name="q" placeholder="Search by patient, doctor or date" value="<?php echo htmlspecialchars($q); ?>">
      <button class="btn" type="submit">Search</button>
    </form>

    <table class="table" style="margin-top:12px">
      <thead><tr><th>When</th><th>Patient</th><th>Doctor</th><th style="text-align:right">Fee</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="clickable" onclick="location.href='appointment.php?id=<?php echo $r['id']; ?>'">
          <td><?php echo htmlspecialchars($r['date']); ?></td>
          <td><?php echo htmlspecialchars($r['patient_name']); ?> <span class="small">(<?php echo htmlspecialchars($r['patient_id']); ?>)</span></td>
          <td><?php echo htmlspecialchars($r['doctor']); ?></td>
          <td style="text-align:right"><?php echo number_format($r['fee'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>

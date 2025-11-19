<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\AppointmentRepository;

$apptRepo = new AppointmentRepository();
$pdo = \Hospital\DB::getPDO();

$totalPatients = (int)$pdo->query('SELECT COUNT(*) as c FROM patients')->fetch(\PDO::FETCH_ASSOC)['c'];
$totalAppointments = (int)$pdo->query('SELECT COUNT(*) as c FROM consultations')->fetch(\PDO::FETCH_ASSOC)['c'];
$totalInvoices = (int)$pdo->query('SELECT COUNT(*) as c FROM invoices')->fetch(\PDO::FETCH_ASSOC)['c'];
$outstanding = (int)$pdo->query("SELECT COUNT(*) as c FROM invoices WHERE status!='paid'")->fetch(\PDO::FETCH_ASSOC)['c'];

$recentAppts = $apptRepo->search(null, 8, 0);
$recentInvoicesStmt = $pdo->query('SELECT invoice_no, patient_id, created_at, amount, status FROM invoices ORDER BY created_at DESC LIMIT 8');
$recentInvoices = $recentInvoicesStmt->fetchAll(\PDO::FETCH_ASSOC);

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">D</div><div><div style="font-weight:700">Dashboard</div></div></div>
    <div class="controls">
      <a class="btn secondary" href="patients_by_type.php">Patients</a>
      <a class="btn secondary" href="add_patient.php" style="margin-left:8px">Add Patient</a>
      <a class="btn secondary" href="appointments.php" style="margin-left:8px">Appointments</a>
      <a class="btn secondary" href="doctors.php" style="margin-left:8px">Doctors</a>
      <a class="btn secondary" href="invoices.php" style="margin-left:8px">Invoices</a>
      <div style="flex:1"></div>
      <a class="btn btn-logout" href="logout.php" title="Sign out">Logout</a>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px">
    <div class="card"><div class="small">Total Patients</div><div style="font-size:22px;font-weight:700"><?php echo $totalPatients; ?></div></div>
    <div class="card"><div class="small">Appointments</div><div style="font-size:22px;font-weight:700"><?php echo $totalAppointments; ?></div></div>
    <div class="card"><div class="small">Invoices</div><div style="font-size:22px;font-weight:700"><?php echo $totalInvoices; ?></div></div>
    <div class="card"><div class="small">Outstanding</div><div style="font-size:22px;font-weight:700;color:#b45309"><?php echo $outstanding; ?></div></div>
  </div>

  <div style="display:flex;gap:12px">
    <div style="flex:1">
      <div class="card">
        <h4 style="margin-top:0">Recent Appointments</h4>
        <table class="table"><thead><tr><th>When</th><th>Patient</th><th>Doctor</th></tr></thead><tbody>
        <?php foreach ($recentAppts as $a): ?>
          <tr class="clickable" onclick="location.href='appointment.php?id=<?php echo $a['id']; ?>'">
            <td><?php echo htmlspecialchars($a['date']); ?></td>
            <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
            <td><?php echo htmlspecialchars($a['doctor']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table>
      </div>
    </div>
    <div style="width:380px">
      <div class="card">
        <h4 style="margin-top:0">Recent Invoices</h4>
        <ul>
          <?php foreach ($recentInvoices as $inv): ?>
            <li><a href="view_invoice.php?invoice_no=<?php echo urlencode($inv['invoice_no']); ?>"><?php echo htmlspecialchars($inv['invoice_no']); ?></a> — <?php echo htmlspecialchars($inv['patient_id']); ?> — <?php echo number_format($inv['amount'],2); ?> <span class="small">(<?php echo htmlspecialchars($inv['status']); ?>)</span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

</div>
</body>
</html>

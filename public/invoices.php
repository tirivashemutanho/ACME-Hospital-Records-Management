<?php
require_once __DIR__ . '/bootstrap.php';
use Hospital\Repositories\InvoiceRepository;
use Hospital\Repositories\AuditRepository;
use Hospital\Auth;

$repoI = new InvoiceRepository();
$audit = new AuditRepository();

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

// handle mark-as-paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_paid') {
    Auth::requireRole(['biller','admin']);
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { echo 'Invalid CSRF'; exit; }
    $invNo = $_POST['invoice_no'] ?? '';
    if ($invNo) {
        $pdo = \Hospital\DB::getPDO();
        $stmt = $pdo->prepare('UPDATE invoices SET status = :s WHERE invoice_no = :no');
        $stmt->execute([':s' => 'paid', ':no' => $invNo]);
        $audit->log($_SESSION['user']['username'] ?? 'system', 'mark_paid', $invNo, []);
    }
}

$pdo = \Hospital\DB::getPDO();
$sql = 'SELECT * FROM invoices';
$conds = [];
$params = [];
if ($q) { $conds[] = '(invoice_no LIKE :q OR patient_id LIKE :q)'; $params[':q'] = '%'.$q.'%'; }
if ($status) { $conds[] = 'status = :status'; $params[':status'] = $status; }
if (!empty($conds)) $sql .= ' WHERE ' . implode(' AND ', $conds);
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoices</title>
  <link rel="stylesheet" href="assets/style.css">
  </head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">I</div><div><div style="font-weight:700">Invoices</div><div class="small">Manage invoices</div></div></div>
    <div class="controls"><a class="btn secondary" href="dashboard.php">Back</a></div>
  </div>

  <div class="card">
    <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
      <input class="input" name="q" placeholder="Invoice # or patient id" value="<?php echo htmlspecialchars($q); ?>">
      <select name="status" class="input" style="width:180px">
        <option value="">Any status</option>
        <option value="issued" <?php if($status==='issued') echo 'selected'; ?>>Issued</option>
        <option value="paid" <?php if($status==='paid') echo 'selected'; ?>>Paid</option>
      </select>
      <button class="btn" type="submit">Filter</button>
    </form>

    <table class="table"><thead><tr><th>Invoice</th><th>Patient</th><th>Date</th><th style="text-align:right">Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><a href="view_invoice.php?invoice_no=<?php echo urlencode($r['invoice_no']); ?>"><?php echo htmlspecialchars($r['invoice_no']); ?></a></td>
        <td><?php echo htmlspecialchars($r['patient_id']); ?></td>
        <td><?php echo htmlspecialchars($r['created_at']); ?></td>
        <td style="text-align:right"><?php echo number_format($r['amount'],2); ?></td>
        <td><?php echo htmlspecialchars($r['status']); ?></td>
        <td>
          <?php if ($r['status'] !== 'paid'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="mark_paid">
              <input type="hidden" name="invoice_no" value="<?php echo htmlspecialchars($r['invoice_no']); ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($GLOBALS['csrf_token'] ?? ''); ?>">
              <button class="btn" type="submit">Mark Paid</button>
            </form>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
</body>
</html>
<?php
require_once __DIR__ . '/bootstrap.php';

// support search and pagination on invoices too
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
list($patients, $total) = $repo->getPaged($search, $page, $perPage);
$totalPages = (int)ceil($total / $perPage);

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoices</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo">H</div><div><div style="font-weight:700">Invoices</div><div class="small">Generated invoices</div></div></div>
    <div class="controls"><a class="btn secondary" href="index.php">Back</a></div>
  </div>

  <div class="card">
    <h3>Invoices</h3>
    <div class="small">Click to view invoice for a patient</div>
    <table class="table">
      <thead><tr><th>Patient</th><th>Type</th><th>Amount</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($patients as $p): $inv = new \Hospital\Invoice($p); ?>
        <tr>
          <td><?php echo htmlspecialchars($p->getName()); ?></td>
          <td><?php echo htmlspecialchars(get_class($p)); ?></td>
          <td><?php echo number_format($inv->getAmount(),2); ?></td>
          <td><a class="btn secondary" href="view_invoice.php?id=<?php echo urlencode($p->getPatientId()); ?>">View</a>
              <a class="btn" href="patient.php?id=<?php echo urlencode($p->getPatientId()); ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="footer">Invoices generated on demand</div>
</div>
</body>
</html>

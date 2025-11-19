<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect to dashboard as the default home page.
// Keep the existing patients listing logic as a fallback if someone opens index.php directly.
if (basename($_SERVER['SCRIPT_NAME']) === 'index.php' && empty($_GET['raw'])) {
    header('Location: dashboard.php');
    exit;
}

// search and pagination (fallback)
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
list($patients, $total) = $repo->getPaged($search, $page, $perPage);
$totalPages = (int)ceil($total / $perPage);
?><!doctype html>
<html>
<head>
        <meta charset="utf-8">
        <title>Hospital - Patients</title>
        <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <div class="logo">H</div>
            <div>
                <div style="font-weight:700">Hospital Records</div>
                <div class="small">Patient management & billing</div>
            </div>
        </div>
        <div class="controls">
            <a class="btn" href="add_patient.php">+ Add Patient</a>
            <a class="btn secondary" href="dashboard.php" style="margin-left:8px">Dashboard</a>
            <a class="btn secondary" href="appointments.php" style="margin-left:8px">Appointments</a>
            <a class="btn secondary" href="doctors.php" style="margin-left:8px">Doctors</a>
            <a class="btn secondary" href="patients_by_type.php" style="margin-left:8px">By Patient Type</a>
        </div>
    </div>

    <div class="card">
        <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
            <input name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or ID" class="input" style="flex:1">
            <button class="btn" type="submit">Search</button>
        </form>

        <h3 style="margin:0 0 8px">Patients</h3>
        <div class="small">Listing of patients in the system (<?php echo $total; ?>)</div>
        <table class="table">
            <thead>
                <tr><th>Patient ID</th><th>Name</th><th>Age</th><th>Type</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p->getPatientId()); ?></td>
                    <td><?php echo htmlspecialchars($p->getName()); ?></td>
                    <td><?php echo htmlspecialchars($p->getAge()); ?></td>
                    <td><?php echo htmlspecialchars(get_class($p)); ?></td>
                    <td>
                        <a class="btn secondary" href="view_invoice.php?id=<?php echo urlencode($p->getPatientId()); ?>">View Invoice</a>
                        <a class="btn" href="patient.php?id=<?php echo urlencode($p->getPatientId()); ?>">Open</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div style="margin-top:12px;display:flex;gap:8px;align-items:center">
                <?php for ($i=1;$i<=$totalPages;$i++): ?>
                    <a class="btn <?php echo $i===$page ? '' : 'secondary'; ?>" href="?q=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">Prototype UI — not for production use</div>
</div>

</body>
</html>
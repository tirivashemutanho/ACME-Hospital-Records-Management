<?php
require_once __DIR__ . '/bootstrap.php';

use Hospital\Invoice;
use Hospital\Auth;
use Hospital\Repositories\InvoiceRepository;
use Hospital\Repositories\AuditRepository;

$invoiceRepo = new InvoiceRepository();
$audit = new AuditRepository();

// support viewing a saved invoice by invoice_no, or previewing/generating by patient id
$invoiceNo = $_GET['invoice_no'] ?? null;
$patientId = $_GET['id'] ?? null;
$savedInvoice = null;
$p = null;
$invoice = null;

// handle save request (only billers/admins allowed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save' && !empty($_POST['patient_id'])) {
    Auth::requireRole(['biller', 'admin']);
    // CSRF check
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo 'Invalid CSRF token'; exit;
    }
    $pid = $_POST['patient_id'];
    $pobj = $repo->getByPatientId($pid);
    if (!$pobj) {
        echo "Patient not found";
        exit;
    }
    $inv = new Invoice($pobj);
    $items = $inv->getItems();
    $amount = $inv->getAmount();
    $meta = [
        'generated_by' => $_SESSION['user']['username'] ?? 'system',
        'patient_name' => $pobj->getName(),
        'patient_id' => $pobj->getPatientId(),
        'created_at' => date('c')
    ];
    $invoiceNo = $invoiceRepo->create($pid, $amount, ['items' => $items, 'meta' => $meta]);
    // audit log
    $audit->log($_SESSION['user']['username'] ?? 'system', 'create_invoice', $invoiceNo, ['amount' => $amount]);
    header('Location: view_invoice.php?invoice_no=' . urlencode($invoiceNo));
    exit;
}

if ($invoiceNo) {
    $savedInvoice = $invoiceRepo->getByInvoiceNo($invoiceNo);
    if (!$savedInvoice) {
        echo "Invoice not found";
        exit;
    }
    $p = $repo->getByPatientId($savedInvoice['patient_id']);
} elseif ($patientId) {
    $p = $repo->getByPatientId($patientId);
    if (!$p) {
        echo "Patient not found";
        exit;
    }
    $invoice = new Invoice($p);
} else {
    echo "No patient id or invoice number provided";
    exit;
}
?><!doctype html>
<html>
<head>
        <meta charset="utf-8">
        <title>Invoice - <?php echo htmlspecialchars($p->getName()); ?></title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <div class="container">
        <div class="header">
            <div class="brand"><div class="logo">H</div><div><div style="font-weight:700">Invoice</div><div class="small">Patient billing summary</div></div></div>
            <div class="controls">
                <a class="btn secondary" href="index.php">Back</a>
                <?php if (!$savedInvoice && isset($p)): ?>
                    <form method="post" style="display:inline;margin-left:8px">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($p->getPatientId()); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($GLOBALS['csrf_token'] ?? ''); ?>">
                        <button class="btn" type="submit">Save Invoice</button>
                    </form>
                <?php endif; ?>
                <button class="btn print" onclick="window.print();" style="margin-left:8px">Print</button>
            </div>
        </div>

                <div class="invoice card">
                    <div class="invoice-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                        <div class="company">
                            <div style="font-size:18px;font-weight:800">Acme Hospital</div>
                            <div class="small">123 Health St, Wellness City</div>
                            <div class="small">Phone: (555) 123-4567 &nbsp; Email: billing@acmehospital.local</div>
                        </div>
                        <div class="meta">
                            <div><strong>Invoice</strong></div>
                            <?php if ($savedInvoice): ?>
                                <div>Invoice No: <strong><?php echo htmlspecialchars($savedInvoice['invoice_no']); ?></strong></div>
                                <div>Date: <?php echo htmlspecialchars(date('Y-m-d', strtotime($savedInvoice['created_at']))); ?></div>
                            <?php else: ?>
                                <div>Invoice No: <strong><?php echo 'INV-' . strtoupper(htmlspecialchars($p->getPatientId())) . '-' . date('Ymd'); ?></strong></div>
                                <div>Date: <?php echo date('Y-m-d'); ?></div>
                                <div>Due: <?php echo date('Y-m-d', strtotime('+7 days')); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:12px">
                        <div>
                            <div class="small">Billed To</div>
                            <div style="font-weight:700"><?php echo htmlspecialchars($p->getName()); ?></div>
                            <div class="small">Patient ID: <?php echo htmlspecialchars($p->getPatientId()); ?></div>
                            <div class="small">Age: <?php echo htmlspecialchars($p->getAge()); ?></div>
                        </div>
                        <div>
                            <div class="small">Payment Method</div>
                            <div class="small">Cash / Card / Insurance</div>
                        </div>
                    </div>

                    <table class="table items" style="margin-top:6px">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:80px;text-align:right">Qty</th>
                                <th style="width:120px;text-align:right">Unit</th>
                                <th style="width:120px;text-align:right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($savedInvoice) {
                            foreach ($savedInvoice['data']['items'] as $it): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($it['description']); ?></td>
                                    <td style="text-align:right"><?php echo (int)$it['qty']; ?></td>
                                    <td style="text-align:right"><?php echo number_format($it['unit'],2); ?></td>
                                    <td style="text-align:right"><?php echo number_format($it['amount'],2); ?></td>
                                </tr>
                            <?php endforeach;
                        } else {
                            foreach ($invoice->getItems() as $it): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($it['description']); ?></td>
                                    <td style="text-align:right"><?php echo (int)$it['qty']; ?></td>
                                    <td style="text-align:right"><?php echo number_format($it['unit'],2); ?></td>
                                    <td style="text-align:right"><?php echo number_format($it['amount'],2); ?></td>
                                </tr>
                            <?php endforeach;
                        }
                        ?>
                        </tbody>
                    </table>

                    <div style="display:flex;justify-content:flex-end;margin-top:12px">
                        <div style="width:320px">
                            <?php $total = $savedInvoice ? (float)$savedInvoice['amount'] : $invoice->getAmount(); ?>
                            <div class="kv"><div>Subtotal</div><div><?php echo number_format($total,2); ?></div></div>
                            <div class="kv"><div>Tax (0%)</div><div>0.00</div></div>
                            <hr>
                            <div class="kv" style="font-weight:800;font-size:1.05em"><div>Total</div><div><?php echo number_format($total,2); ?></div></div>
                        </div>
                    </div>

                    <div style="margin-top:18px" class="small">Payment terms: Payment due within 7 days. For insurance claims, please provide policy details to billing.</div>
                </div>

        <div class="footer">Generated by Hospital demo</div>
    </div>
    </body>
</html>
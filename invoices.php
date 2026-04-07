<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Invoices';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $invNo    = 'Inv-' . str_pad(mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*)+7 AS n FROM invoices WHERE type='invoice'"))['n'], 2, '0', STR_PAD_LEFT);
    $customer = clean($conn, $_POST['customer_name']);
    $phone    = clean($conn, $_POST['customer_phone']);
    $vehicle  = clean($conn, $_POST['vehicle_name']);
    $rate     = (float)$_POST['rate_per_day'];
    $qty      = (int)$_POST['quantity'];
    $days     = (int)$_POST['days'];
    $cf       = (float)$_POST['contract_fee'];
    $exKms    = (int)$_POST['excess_kms'];
    $exRate   = (float)$_POST['excess_rate'];
    $exTotal  = $exKms * $exRate;
    $subtotal = ($rate * $qty * $days) + $cf + $exTotal;
    $vat      = $subtotal * 0.15;
    $total    = $subtotal + $vat;
    $date     = date('Y-m-d');

    mysqli_query($conn, "INSERT INTO invoices (invoice_no,type,customer_name,customer_phone,vehicle_name,rate_per_day,quantity,days,contract_fee,excess_kms,excess_rate,excess_total,subtotal,vat,total,status,invoice_date)
        VALUES ('$invNo','invoice','$customer','$phone','$vehicle',$rate,$qty,$days,$cf,$exKms,$exRate,$exTotal,$subtotal,$vat,$total,'pending','$date')");
    $msg = 'success:Invoice ' . $invNo . ' created.';
    mysqli_query($conn, "INSERT INTO notifications (message,type) VALUES ('Invoice $invNo created for $customer','payment')");
}

if (isset($_GET['mark_paid'])) {
    $id = (int)$_GET['mark_paid'];
    mysqli_query($conn, "UPDATE invoices SET status='paid' WHERE id=$id");
    $msg = 'success:Invoice marked as paid.';
}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM invoices WHERE id=" . (int)$_GET['delete']);
    $msg = 'success:Invoice deleted.';
}

$invoices = mysqli_query($conn, "SELECT * FROM invoices WHERE type='invoice' ORDER BY created_at DESC");

// For print view
$printId = isset($_GET['print']) ? (int)$_GET['print'] : 0;
$printInv = null;
if ($printId) {
    $printInv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM invoices WHERE id=$printId"));
}
?>
<?php include 'admin_header.php'; ?>

<?php if($msg): list($type,$text) = explode(':',$msg,2); ?>
<div class="alert alert-<?php echo $type === 'success' ? 'success' : 'error' ?>"><?php echo htmlspecialchars($text) ?></div>
<?php endif; ?>

<?php if($printInv): ?>
<!-- PRINT VIEW -->
<div class="print-area" id="printArea">
    <div class="invoice-doc">
        <div class="inv-header">
            <div>
                <img src="logo.png.jpg" alt="Fenix" style="height:50px;margin-bottom:8px"><br>
                <strong style="font-size:20px;color:#1e3a8a">FENIX CAR HIRE</strong><br>
                <small>For All your rental Problems</small><br>
                <small>P.O. Box 7909 Mbabane, Eswatini<br>
                Lilanga Complex, Litsemba Street, Sidwashini<br>
                Cell: (+268) 76829797, 79846935 | Tel: (+268) 2422 1045<br>
                Email: reception@fenix.co.sz</small>
            </div>
            <div class="inv-meta-table">
                <table>
                    <tr><td>Date</td><td><?php echo $printInv['invoice_date'] ?></td></tr>
                    <tr><td>Invoice No.</td><td><?php echo htmlspecialchars($printInv['invoice_no']) ?></td></tr>
                    <tr><td>Customer</td><td><?php echo htmlspecialchars($printInv['customer_name']) ?></td></tr>
                    <tr><td>Phone</td><td><?php echo htmlspecialchars($printInv['customer_phone']) ?></td></tr>
                    <tr><td>Type</td><td>Car Rental</td></tr>
                </table>
            </div>
        </div>
        <h2 style="text-align:center;letter-spacing:.2em;margin:20px 0">TAX INVOICE</h2>
        <table class="inv-table">
            <thead><tr><th>Description</th><th>Rate/day</th><th>Qty</th><th>Days</th><th>KMs/day</th><th>Excess</th><th>Total</th></tr></thead>
            <tbody>
                <tr><td>1. <?php echo htmlspecialchars($printInv['vehicle_name']) ?></td><td>E <?php echo number_format($printInv['rate_per_day'],2) ?></td><td><?php echo $printInv['quantity'] ?></td><td><?php echo $printInv['days'] ?></td><td><?php echo $printInv['kms_free_per_day'] ?></td><td></td><td>E <?php echo number_format($printInv['rate_per_day']*$printInv['quantity']*$printInv['days'],2) ?></td></tr>
                <tr><td>2. Contract fee</td><td></td><td></td><td></td><td></td><td></td><td>E <?php echo number_format($printInv['contract_fee'],2) ?></td></tr>
                <?php if($printInv['excess_kms'] > 0): ?>
                <tr><td>3. Excess KMs (<?php echo $printInv['excess_kms'] ?> km)</td><td></td><td></td><td></td><td></td><td>E<?php echo number_format($printInv['excess_rate'],2) ?>/km</td><td>E <?php echo number_format($printInv['excess_total'],2) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="inv-totals">
            <table>
                <tr><td>Subtotal</td><td>E <?php echo number_format($printInv['subtotal'],2) ?></td></tr>
                <tr><td>VAT - 15%</td><td>E <?php echo number_format($printInv['vat'],2) ?></td></tr>
                <tr class="total-row"><td><strong>TOTAL</strong></td><td><strong>E <?php echo number_format($printInv['total'],2) ?></strong></td></tr>
            </table>
        </div>
        <div class="inv-banking">
            <strong>Banking Details:</strong><br>
            Account Name: Sempeerfi Investments (Pty) &nbsp;|&nbsp; Bank: Standard Bank Swaziland<br>
            Branch Code: 663164 &nbsp;|&nbsp; Account Number: 9110005689573
        </div>
    </div>
    <div style="margin-top:20px;display:flex;gap:12px" class="no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Invoice</button>
        <a href="invoices.php" class="btn btn-ghost">← Back</a>
    </div>
</div>
<?php else: ?>

<!-- CREATE INVOICE FORM -->
<div class="admin-card mb-20">
    <div class="card-header"><h3>➕ Create New Invoice</h3></div>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid-3">
            <div class="form-group"><label>Customer Name *</label><input type="text" name="customer_name" required></div>
            <div class="form-group"><label>Customer Phone</label><input type="tel" name="customer_phone"></div>
            <div class="form-group"><label>Vehicle Name *</label><input type="text" name="vehicle_name" required placeholder="e.g. Staria x2"></div>
            <div class="form-group"><label>Rate per Day (E) *</label><input type="number" step="0.01" name="rate_per_day" required value="1400"></div>
            <div class="form-group"><label>Quantity (no. of vehicles)</label><input type="number" name="quantity" value="1" min="1"></div>
            <div class="form-group"><label>Number of Days *</label><input type="number" name="days" required value="1" min="1"></div>
            <div class="form-group"><label>Contract Fee (E)</label><input type="number" step="0.01" name="contract_fee" value="200"></div>
            <div class="form-group"><label>Excess KMs</label><input type="number" name="excess_kms" value="0" min="0"></div>
            <div class="form-group"><label>Excess Rate (E/km)</label><input type="number" step="0.01" name="excess_rate" value="11"></div>
        </div>
        <!-- Live Calculation -->
        <div class="calc-preview" id="calcPreview"></div>
        <button type="submit" class="btn btn-primary">Generate Invoice</button>
    </form>
</div>

<!-- INVOICE LIST -->
<div class="admin-card">
    <div class="card-header"><h3>💰 All Invoices</h3></div>
    <table class="admin-table">
        <thead><tr><th>Invoice No.</th><th>Date</th><th>Customer</th><th>Vehicle</th><th>Days</th><th>Subtotal</th><th>VAT</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($inv = mysqli_fetch_assoc($invoices)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($inv['invoice_no']) ?></strong></td>
            <td><?php echo $inv['invoice_date'] ?></td>
            <td><?php echo htmlspecialchars($inv['customer_name']) ?></td>
            <td><?php echo htmlspecialchars($inv['vehicle_name']) ?></td>
            <td><?php echo $inv['days'] ?></td>
            <td><?php echo formatMoney($inv['subtotal']) ?></td>
            <td><?php echo formatMoney($inv['vat']) ?></td>
            <td><strong><?php echo formatMoney($inv['total']) ?></strong></td>
            <td><span class="badge badge-<?php echo $inv['status'] ?>"><?php echo ucfirst($inv['status']) ?></span></td>
            <td>
                <a href="?print=<?php echo $inv['id'] ?>" class="btn btn-ghost btn-xs">🖨️ Print</a>
                <?php if($inv['status'] !== 'paid'): ?>
                <a href="?mark_paid=<?php echo $inv['id'] ?>" class="btn btn-primary btn-xs">Mark Paid</a>
                <?php endif; ?>
                <a href="?delete=<?php echo $inv['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete invoice?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
// Live calculation preview
const fields = ['rate_per_day','quantity','days','contract_fee','excess_kms','excess_rate'];
fields.forEach(f => { const el = document.querySelector('[name="'+f+'"]'); if(el) el.addEventListener('input', calcPreview); });
function calcPreview() {
    const rate = parseFloat(document.querySelector('[name="rate_per_day"]')?.value)||0;
    const qty  = parseInt(document.querySelector('[name="quantity"]')?.value)||1;
    const days = parseInt(document.querySelector('[name="days"]')?.value)||1;
    const cf   = parseFloat(document.querySelector('[name="contract_fee"]')?.value)||0;
    const exkm = parseInt(document.querySelector('[name="excess_kms"]')?.value)||0;
    const exr  = parseFloat(document.querySelector('[name="excess_rate"]')?.value)||0;
    const subtotal = (rate * qty * days) + cf + (exkm * exr);
    const vat = subtotal * 0.15;
    const total = subtotal + vat;
    const p = document.getElementById('calcPreview');
    if(p) p.innerHTML = `<div class="calc-box"><span>Subtotal: <strong>E ${subtotal.toFixed(2)}</strong></span><span>VAT 15%: <strong>E ${vat.toFixed(2)}</strong></span><span style="color:#22c55e;font-size:18px">Total: <strong>E ${total.toFixed(2)}</strong></span></div>`;
}
calcPreview();
</script>

<?php include 'admin_footer.php'; ?>
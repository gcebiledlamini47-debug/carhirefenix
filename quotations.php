<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Quotations';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $quoNo    = 'Quo-' . str_pad(mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*)+2 AS n FROM invoices WHERE type='quotation'"))['n'], 2, '0', STR_PAD_LEFT);
    $customer = clean($conn, $_POST['customer_name']);
    $phone    = clean($conn, $_POST['customer_phone']);
    $vehicle  = clean($conn, $_POST['vehicle_name']);
    $rate     = (float)$_POST['rate_per_day'];
    $qty      = (int)$_POST['quantity'];
    $days     = (int)$_POST['days'];
    $cf       = (float)$_POST['contract_fee'];
    $deposit  = (float)$_POST['deposit'];
    $subtotal = ($rate * $qty * $days) + $cf + $deposit;
    $vat      = ($rate * $qty * $days + $cf) * 0.15;
    $total    = ($rate * $qty * $days + $cf) * 1.15 + $deposit;
    $date     = date('Y-m-d');

    mysqli_query($conn, "INSERT INTO invoices (invoice_no,type,customer_name,customer_phone,vehicle_name,rate_per_day,quantity,days,contract_fee,deposit,subtotal,vat,total,status,invoice_date)
        VALUES ('$quoNo','quotation','$customer','$phone','$vehicle',$rate,$qty,$days,$cf,$deposit,$subtotal,$vat,$total,'pending','$date')");
    $msg = 'success:Quotation ' . $quoNo . ' created.';
}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM invoices WHERE id=" . (int)$_GET['delete']);
    $msg = 'success:Quotation deleted.';
}

$quotes = mysqli_query($conn, "SELECT * FROM invoices WHERE type='quotation' ORDER BY created_at DESC");

$printId = isset($_GET['print']) ? (int)$_GET['print'] : 0;
$printQ  = null;
if ($printId) $printQ = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM invoices WHERE id=$printId"));
?>
<?php include 'admin_header.php'; ?>

<?php if($msg): list($type,$text) = explode(':',$msg,2); ?>
<div class="alert alert-<?php echo $type === 'success' ? 'success' : 'error' ?>"><?php echo htmlspecialchars($text) ?></div>
<?php endif; ?>

<?php if($printQ): ?>
<div class="print-area">
    <div class="invoice-doc">
        <div class="inv-header">
            <div>
                <img src="logo.png.jpg" alt="Fenix" style="height:50px;margin-bottom:8px"><br>
                <strong style="font-size:20px;color:#1e3a8a">FENIX CAR HIRE</strong><br>
                <small>P.O. Box 7909 Mbabane, Eswatini | Cell: (+268) 76829797</small>
            </div>
            <div class="inv-meta-table">
                <table>
                    <tr><td>Date</td><td><?php echo $printQ['invoice_date'] ?></td></tr>
                    <tr><td>Quotation No.</td><td><?php echo htmlspecialchars($printQ['invoice_no']) ?></td></tr>
                    <tr><td>Customer</td><td><?php echo htmlspecialchars($printQ['customer_name']) ?></td></tr>
                    <tr><td>Phone</td><td><?php echo htmlspecialchars($printQ['customer_phone']) ?></td></tr>
                    <tr><td>Type</td><td>Car Rental</td></tr>
                </table>
            </div>
        </div>
        <h2 style="text-align:center;letter-spacing:.2em;margin:20px 0">QUOTATION</h2>
        <table class="inv-table">
            <thead><tr><th>Description</th><th>Rate/day</th><th>Qty</th><th>Days</th><th>KMs Free/day</th><th>Total</th></tr></thead>
            <tbody>
                <tr><td>1. <?php echo htmlspecialchars($printQ['vehicle_name']) ?></td><td>E <?php echo number_format($printQ['rate_per_day'],2) ?></td><td><?php echo $printQ['quantity'] ?></td><td><?php echo $printQ['days'] ?></td><td>200</td><td>E <?php echo number_format($printQ['rate_per_day']*$printQ['quantity']*$printQ['days'],2) ?></td></tr>
                <tr><td>2. Contract fee</td><td></td><td></td><td></td><td></td><td>E <?php echo number_format($printQ['contract_fee'],2) ?></td></tr>
                <tr><td>3. Deposit (Refundable)</td><td></td><td></td><td></td><td></td><td>E <?php echo number_format($printQ['deposit'],2) ?></td></tr>
            </tbody>
        </table>
        <div class="inv-totals">
            <table>
                <tr><td>Subtotal</td><td>E <?php echo number_format($printQ['subtotal'],2) ?></td></tr>
                <tr><td>VAT - 15%</td><td>E <?php echo number_format($printQ['vat'],2) ?></td></tr>
                <tr class="total-row"><td><strong>TOTAL</strong></td><td><strong>E <?php echo number_format($printQ['total'],2) ?></strong></td></tr>
            </table>
        </div>
        <div class="inv-banking">
            <strong>Banking Details:</strong><br>
            Account Name: Sempeerfi Investments (Pty) | Bank: Standard Bank Swaziland<br>
            Branch Code: 663164 | Account Number: 9110005689573
        </div>
    </div>
    <div style="margin-top:20px;display:flex;gap:12px" class="no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Quotation</button>
        <a href="quotations.php" class="btn btn-ghost">← Back</a>
    </div>
</div>
<?php else: ?>

<div class="admin-card mb-20">
    <div class="card-header"><h3>➕ Create Quotation</h3></div>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid-3">
            <div class="form-group"><label>Customer Name *</label><input type="text" name="customer_name" required></div>
            <div class="form-group"><label>Phone *</label><input type="tel" name="customer_phone" required></div>
            <div class="form-group"><label>Vehicle *</label><input type="text" name="vehicle_name" required placeholder="e.g. Toyota Fortuner"></div>
            <div class="form-group"><label>Rate per Day (E)</label><input type="number" step="0.01" name="rate_per_day" value="1400"></div>
            <div class="form-group"><label>Quantity</label><input type="number" name="quantity" value="1" min="1"></div>
            <div class="form-group"><label>Days</label><input type="number" name="days" value="1" min="1"></div>
            <div class="form-group"><label>Contract Fee (E)</label><input type="number" step="0.01" name="contract_fee" value="100"></div>
            <div class="form-group"><label>Deposit — Refundable (E)</label><input type="number" step="0.01" name="deposit" value="5000"></div>
        </div>
        <div class="calc-preview" id="calcPreview2"></div>
        <button type="submit" class="btn btn-primary">Generate Quotation</button>
    </form>
</div>

<div class="admin-card">
    <div class="card-header"><h3>📄 All Quotations</h3></div>
    <table class="admin-table">
        <thead><tr><th>Quo No.</th><th>Date</th><th>Customer</th><th>Vehicle</th><th>Days</th><th>Deposit</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($q = mysqli_fetch_assoc($quotes)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($q['invoice_no']) ?></strong></td>
            <td><?php echo $q['invoice_date'] ?></td>
            <td><?php echo htmlspecialchars($q['customer_name']) ?><br><small><?php echo $q['customer_phone'] ?></small></td>
            <td><?php echo htmlspecialchars($q['vehicle_name']) ?></td>
            <td><?php echo $q['days'] ?></td>
            <td><?php echo formatMoney($q['deposit']) ?></td>
            <td><strong><?php echo formatMoney($q['total']) ?></strong></td>
            <td><span class="badge badge-<?php echo $q['status'] ?>"><?php echo ucfirst($q['status']) ?></span></td>
            <td>
                <a href="?print=<?php echo $q['id'] ?>" class="btn btn-ghost btn-xs">🖨️ Print</a>
                <a href="?delete=<?php echo $q['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
const f2 = ['rate_per_day','quantity','days','contract_fee','deposit'];
f2.forEach(f => { const el = document.querySelector('[name="'+f+'"]'); if(el) el.addEventListener('input', calc2); });
function calc2() {
    const rate = parseFloat(document.querySelector('[name="rate_per_day"]')?.value)||0;
    const qty  = parseInt(document.querySelector('[name="quantity"]')?.value)||1;
    const days = parseInt(document.querySelector('[name="days"]')?.value)||1;
    const cf   = parseFloat(document.querySelector('[name="contract_fee"]')?.value)||0;
    const dep  = parseFloat(document.querySelector('[name="deposit"]')?.value)||0;
    const rental = rate*qty*days+cf;
    const vat = rental*0.15;
    const total = rental*1.15+dep;
    const p = document.getElementById('calcPreview2');
    if(p) p.innerHTML = `<div class="calc-box"><span>Rental+VAT: <strong>E ${(rental*1.15).toFixed(2)}</strong></span><span>Deposit: <strong>E ${dep.toFixed(2)}</strong></span><span style="color:#60a5fa;font-size:18px">Total: <strong>E ${total.toFixed(2)}</strong></span></div>`;
}
calc2();
</script>

<?php include 'admin_footer.php'; ?>
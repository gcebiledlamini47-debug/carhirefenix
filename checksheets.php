<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Check Sheets';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $customer  = clean($conn, $_POST['customer_name']);
    $plate     = clean($conn, $_POST['plate']);
    $vid       = (int)$_POST['vehicle_id'];
    $milOut    = (int)$_POST['mileage_out'];
    $milIn     = (int)$_POST['mileage_in'];
    $fuelOut   = clean($conn, $_POST['fuel_out']);
    $fuelIn    = clean($conn, $_POST['fuel_in']);
    $checkedOut= clean($conn, $_POST['checked_by_out']);
    $checkedIn = clean($conn, $_POST['checked_by_in']);
    $renter    = clean($conn, $_POST['renter_name']);
    $dateOut   = clean($conn, $_POST['date_out']);
    $dateIn    = clean($conn, $_POST['date_in']);
    $damage    = clean($conn, $_POST['damage_notes']);

    // Checkboxes — pre rental
    $items = ['windscreen','headlights','taillights','mirrors','hub_caps','spare','jack','triangle','aerial','radio','mats','carpets','Tires'];
    $preCols = $preVals = $postCols = $postVals = [];
    foreach($items as $item) {
        $preCols[]  = "pre_$item";
        $preVals[]  = isset($_POST["pre_$item"]) ? 1 : 0;
        $postCols[] = "post_$item";
        $postVals[] = isset($_POST["post_$item"]) ? 1 : 0;
    }
    $cols = implode(',', array_merge(['vehicle_id','plate','customer_name','mileage_out','mileage_in','fuel_out','fuel_in','checked_by_out','checked_by_in','renter_name','date_out','date_in','damage_notes'], $preCols, $postCols));
    $vals = implode(',', array_merge([$vid, "'$plate'", "'$customer'", $milOut, $milIn, "'$fuelOut'", "'$fuelIn'", "'$checkedOut'", "'$checkedIn'", "'$renter'", "'$dateOut'", "'$dateIn'", "'$damage'"], $preVals, $postVals));
    mysqli_query($conn, "INSERT INTO checksheets ($cols) VALUES ($vals)");
    $msg = 'success:Check sheet saved.';
}

if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM checksheets WHERE id=" . (int)$_GET['delete']);
    $msg = 'success:Check sheet deleted.';
}

$sheets   = mysqli_query($conn, "SELECT cs.*,v.name as vname FROM checksheets cs LEFT JOIN vehicles v ON cs.vehicle_id=v.id ORDER BY cs.created_at DESC");
$vehicles = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY name");

$printId = isset($_GET['print']) ? (int)$_GET['print'] : 0;
$printCS = null;
if ($printId) {
    $printCS = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cs.*,v.name as vname FROM checksheets cs LEFT JOIN vehicles v ON cs.vehicle_id=v.id WHERE cs.id=$printId"));
}

$checkItems = [
    'windscreen'=>'Windscreen','headlights'=>'Headlights','taillights'=>'Taillights',
    'mirrors'=>'Mirrors','hub_caps'=>'Hub Caps','spare'=>'Spare Wheel',
    'jack'=>'Jack + Wheel','triangle'=>'Triangle','aerial'=>'Aerial',
    'radio'=>'Radio','mats'=>'Mats','carpets'=>'Carpets'
];
?>
<?php include 'admin_header.php'; ?>

<?php if($msg): list($t,$text) = explode(':',$msg,2); ?>
<div class="alert alert-<?php echo $t==='success'?'success':'error' ?>"><?php echo htmlspecialchars($text) ?></div>
<?php endif; ?>

<?php if($printCS): ?>
<!-- ── PRINT VIEW ── -->
<div class="print-area">
    <div class="invoice-doc">
        <div class="inv-header">
            <div>
                <img src="logo.png.jpg" alt="Fenix" style="height:48px;margin-bottom:6px"><br>
                <strong style="font-size:18px;color:#1e3a8a">FENIX CAR HIRE</strong><br>
                <small>P.O. Box 7909 Mbabane | Cell: (+268) 76829797</small>
            </div>
            <div class="inv-meta-table">
                <table>
                    <tr><td>Vehicle</td><td><?php echo htmlspecialchars(isset($printCS['vname']) ? $printCS['vname'] : $printCS['plate']) ?></td></tr>
                    <tr><td>Plate</td><td><?php echo htmlspecialchars($printCS['plate']) ?></td></tr>
                    <tr><td>Customer</td><td><?php echo htmlspecialchars($printCS['customer_name']) ?></td></tr>
                    <tr><td>Date Out</td><td><?php echo $printCS['date_out'] ?></td></tr>
                    <tr><td>Date In</td><td><?php echo $printCS['date_in'] ?></td></tr>
                </table>
            </div>
        </div>
        <h2 style="text-align:center;
            letter-spacing:.2em;
            margin:20px 0">VEHICLE CHECK SHEET</h2>

        <div style="display:grid;
             grid-template-columns:1fr 1fr;
             gap:20px;
             margin-bottom:20px">
            <!-- PRE RENTAL -->
            <div>
                <h3 style="background:#1e3a8a;
                    color:white;
                    padding:8px 12px;
                    border-radius:6px;
                    margin:0 0 10px">PRE-RENTAL</h3>
                <table class="inv-table" style="font-size:13px">
                    <thead><tr><th>Item</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach($checkItems as $key=>$label): ?>
                    <tr><td><?php echo $label ?></td><td style="text-align:center;color:<?php echo $printCS["pre_$key"] ? 'green' : 'red' ?>"><?php echo $printCS["pre_$key"] ? '✓' : '✗' ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="font-size:13px;margin-top:10px"><strong>Mileage Out:</strong> <?php echo number_format($printCS['mileage_out']) ?> km</p>
                <p style="font-size:13px"><strong>Fuel Level:</strong> <?php echo $printCS['fuel_out'] ?></p>
                <p style="font-size:13px"><strong>Checked by:</strong> <?php echo htmlspecialchars($printCS['checked_by_out']) ?></p>
                <div style="margin-top:30px;border-top:1px solid #000;padding-top:4px;font-size:12px">Renter Signature: <?php echo htmlspecialchars($printCS['renter_name']) ?></div>
            </div>
            <!-- POST RENTAL -->
            <div>
                <h3 style="background:#166534;
                    color:white;
                    padding:8px 12px;
                    border-radius:6px;
                    margin:0 0 10px">POST-RENTAL</h3>
                <table class="inv-table" style="font-size:13px">
                    <thead><tr><th>Item</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach($checkItems as $key=>$label): ?>
                    <tr><td><?php echo $label ?></td><td style="text-align:center;color:<?php echo $printCS["post_$key"] ? 'green' : 'red' ?>"><?php echo $printCS["post_$key"] ? '✓' : '✗' ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="font-size:13px;margin-top:10px"><strong>Mileage In:</strong> <?php echo number_format($printCS['mileage_in']) ?> km</p>
                <p style="font-size:13px"><strong>Excess KMs:</strong> <?php echo max(0, $printCS['mileage_in'] - $printCS['mileage_out']) ?> km</p>
                <p style="font-size:13px"><strong>Fuel Level:</strong> <?php echo $printCS['fuel_in'] ?></p>
                <p style="font-size:13px"><strong>Checked by:</strong> <?php echo htmlspecialchars($printCS['checked_by_in']) ?></p>
                <div style="margin-top:30px;border-top:1px solid #000;padding-top:4px;font-size:12px">Renter Signature: ___________________</div>
            </div>
        </div>

        <?php if($printCS['damage_notes']): ?>
        <div style="background:#fff3cd;padding:12px;border-radius:8px;font-size:13px;margin-bottom:16px">
            <strong>Damage Notes:</strong> <?php echo htmlspecialchars($printCS['damage_notes']) ?>
        </div>
        <?php endif; ?>

        <div style="font-size:11px;
             color:#555;
             border-top:1px solid #ddd;
             padding-top:10px">
            IT IS THE CUSTOMER'S RESPONSIBILITY TO CONTACT THE RENTING BRANCH BEFORE ACCEPTING THE VEHICLE. IF NOTED DAMAGE IS DISPUTED, IT SHOULD BE REMEMBERED THAT CHARGEABLE DAMAGE INCLUDES TYRES, STONECHIPS TO BODYWORK AND GLASS.
        </div>
    </div>
    <div style="margin-top:20px;
         display:flex;
         gap:12px" class="no-print">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Check Sheet</button>
        <a href="checksheets.php" class="btn btn-ghost">← Back</a>
    </div>
</div>

<?php else: ?>

<!-- ── CREATE FORM ── -->
<div class="admin-card mb-20">
    <div class="card-header"><h3>➕ New Vehicle Check Sheet</h3></div>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="form-grid-3">
            <div class="form-group">
                <label>Vehicle *</label>
                <select name="vehicle_id" required onchange="fillPlate(this)">
                    <option value="">-- Select Vehicle --</option>
                    <?php while($v = mysqli_fetch_assoc($vehicles)): ?>
                    <option value="<?php echo $v['id'] ?>" data-plate="<?php echo htmlspecialchars($v['plate']) ?>" data-mileage="<?php echo $v['mileage'] ?>">
                        <?php echo htmlspecialchars($v['name']) ?> (<?php echo $v['plate'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Number Plate</label><input type="text" name="plate" id="plateInput" placeholder="Auto-filled"></div>
            <div class="form-group"><label>Customer Name *</label><input type="text" name="customer_name" required></div>
            <div class="form-group"><label>Mileage Out</label><input type="number" name="mileage_out" id="milOutInput" value="0"></div>
            <div class="form-group"><label>Mileage In</label><input type="number" name="mileage_in" value="0"></div>
            <div class="form-group"><label>Renter Name</label><input type="text" name="renter_name"></div>
            <div class="form-group">
                <label>Fuel Level Out</label>
                <select name="fuel_out">
                    <?php foreach(['Full','3/4','1/2','1/4','E'] as $f): ?><option><?php echo $f ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Fuel Level In</label>
                <select name="fuel_in">
                    <?php foreach(['Full','3/4','1/2','1/4','E'] as $f): ?><option><?php echo $f ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Date Out</label><input type="date" name="date_out" value="<?php echo date('Y-m-d') ?>"></div>
            <div class="form-group"><label>Date In</label><input type="date" name="date_in"></div>
            <div class="form-group"><label>Checked By (Out)</label><input type="text" name="checked_by_out"></div>
            <div class="form-group"><label>Checked By (In)</label><input type="text" name="checked_by_in"></div>
        </div>

        <!-- CHECKBOX GRID -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:20px 0">
            <div>
                <h4 style="margin-bottom:12px;color:#60a5fa">Pre-Rental Inspection</h4>
                <div class="check-grid">
                    <?php foreach($checkItems as $key=>$label): ?>
                    <label class="check-item">
                        <input type="checkbox" name="pre_<?php echo $key ?>" checked>
                        <span><?php echo $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h4 style="margin-bottom:12px;color:#22c55e">Post-Rental Inspection</h4>
                <div class="check-grid">
                    <?php foreach($checkItems as $key=>$label): ?>
                    <label class="check-item">
                        <input type="checkbox" name="post_<?php echo $key ?>" checked>
                        <span><?php echo $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Damage Notes (if any)</label>
            <textarea name="damage_notes" rows="3" placeholder="Describe any damage found..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Check Sheet</button>
    </form>
</div>

<!-- ── LIST ── -->
<div class="admin-card">
    <div class="card-header"><h3>🔍 All Check Sheets</h3></div>
    <table class="admin-table">
        <thead><tr><th>Date</th><th>Customer</th><th>Vehicle</th><th>Plate</th><th>KM Out</th><th>KM In</th><th>Excess</th><th>Fuel Out</th><th>Fuel In</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($cs = mysqli_fetch_assoc($sheets)): ?>
        <tr>
            <td><?php echo $cs['date_out'] ?></td>
            <td><?php echo htmlspecialchars($cs['customer_name']) ?></td>
            <td><?php echo htmlspecialchars(isset($cs['vname']) ? $cs['vname'] : '—') ?></td>
            <td><?php echo htmlspecialchars($cs['plate']) ?></td>
            <td><?php echo number_format($cs['mileage_out']) ?></td>
            <td><?php echo $cs['mileage_in'] ? number_format($cs['mileage_in']) : '—' ?></td>
            <td><?php echo $cs['mileage_in'] ? number_format(max(0,$cs['mileage_in']-$cs['mileage_out'])).' km' : '—' ?></td>
            <td><?php echo $cs['fuel_out'] ?></td>
            <td><?php echo $cs['fuel_in'] ?></td>
            <td>
                <a href="?print=<?php echo $cs['id'] ?>" class="btn btn-ghost btn-xs">🖨️ Print</a>
                <a href="?delete=<?php echo $cs['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function fillPlate(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('plateInput').value  = opt.dataset.plate   || '';
    document.getElementById('milOutInput').value = opt.dataset.mileage || 0;
}
</script>
<?php include 'admin_footer.php'; ?>
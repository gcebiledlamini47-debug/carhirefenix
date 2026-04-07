<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Bookings';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['booking_id'];
    if ($_POST['action'] === 'update') {
        $status  = clean($conn, $_POST['status']);
        $milOut  = (int)$_POST['mileage_out'];
        $milIn   = (int)$_POST['mileage_in'];
        $paid    = (float)$_POST['amount_paid'];
        $pending = (float)$_POST['amount_pending'];
        $debt    = (float)$_POST['bad_debt'];
        $notes   = clean($conn, $_POST['notes']);
        mysqli_query($conn, "UPDATE bookings SET status='$status',mileage_out=$milOut,mileage_in=$milIn,amount_paid=$paid,amount_pending=$pending,bad_debt=$debt,notes='$notes' WHERE id=$id");
        $vidRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT vehicle_id FROM bookings WHERE id=$id"));
        if ($vidRow && ($status === 'completed' || $status === 'cancelled')) {
            mysqli_query($conn, "UPDATE vehicles SET status='available' WHERE id=" . $vidRow['vehicle_id']);
        }
        $msg = 'success:Booking updated.';
    }
    if ($_POST['action'] === 'delete') {
        $vidRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT vehicle_id FROM bookings WHERE id=$id"));
        mysqli_query($conn, "DELETE FROM bookings WHERE id=$id");
        if ($vidRow) mysqli_query($conn, "UPDATE vehicles SET status='available' WHERE id=" . $vidRow['vehicle_id']);
        $msg = 'success:Booking deleted.';
    }
    if ($_POST['action'] === 'add') {
        $ref    = generateRef('BK');
        $name   = clean($conn, $_POST['customer_name']);
        $phone  = clean($conn, $_POST['customer_phone']);
        $email  = clean($conn, $_POST['customer_email']);
        $vid    = (int)$_POST['vehicle_id'];
        $pickup = clean($conn, $_POST['pickup_date']);
        $ret    = clean($conn, $_POST['return_date']);
        $milOut = (int)$_POST['mileage_out'];
        $notes  = clean($conn, $_POST['notes']);
        mysqli_query($conn, "INSERT INTO bookings (booking_ref,customer_name,customer_phone,customer_email,vehicle_id,pickup_date,return_date,mileage_out,notes,status) VALUES ('$ref','$name','$phone','$email',$vid,'$pickup','$ret',$milOut,'$notes','active')");
        mysqli_query($conn, "UPDATE vehicles SET status='booked' WHERE id=$vid");
        $msg = 'success:Booking added — Ref: ' . $ref;
    }
}

$filter   = isset($_GET['status']) ? clean($conn, $_GET['status']) : 'all';
$where    = ($filter !== 'all') ? "WHERE b.status='$filter'" : '';
$bookings = mysqli_query($conn, "SELECT b.*,v.name as vname,v.plate FROM bookings b LEFT JOIN vehicles v ON b.vehicle_id=v.id $where ORDER BY b.created_at DESC");
$allVehicles = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY name");
?>
<?php include 'admin_header.php'; ?>

<?php if ($msg): $parts = explode(':', $msg, 2); ?>
<div class="alert alert-<?php echo $parts[0]==='success'?'success':'error'; ?>"><?php echo htmlspecialchars($parts[1]); ?></div>
<?php endif; ?>

<div class="admin-card mb-20">
    <div class="card-header"><h3>Add Manual Booking</h3></div>
    <div style="padding:20px">
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-grid-3">
            <div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" required></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="customer_phone" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="customer_email"></div>
            <div class="form-group"><label>Vehicle</label>
                <select name="vehicle_id" required>
                    <option value="">-- Select --</option>
                    <?php while ($v = mysqli_fetch_assoc($allVehicles)): ?>
                    <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['name']); ?> (<?php echo $v['plate']; ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Pick-up Date</label><input type="date" name="pickup_date" required></div>
            <div class="form-group"><label>Return Date</label><input type="date" name="return_date"></div>
            <div class="form-group"><label>Mileage Out</label><input type="number" name="mileage_out" value="0"></div>
            <div class="form-group"><label>Notes</label><input type="text" name="notes"></div>
        </div>
        <button type="submit" class="btn btn-primary">Add Booking</button>
    </form>
    </div>
</div>

<div class="filter-bar mb-20">
    <?php foreach (array('all','pending','active','completed','cancelled') as $s): ?>
    <a href="?status=<?php echo $s; ?>" class="filter-btn <?php echo $filter===$s?'active':''; ?>"><?php echo ucfirst($s); ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <div class="card-header"><h3>All Bookings</h3></div>
    <div class="table-wrap"><table class="admin-table">
        <thead><tr><th>Ref</th><th>Customer</th><th>Vehicle</th><th>Plate</th><th>Pickup</th><th>Return</th><th>KM Out</th><th>KM In</th><th>Paid</th><th>Pending</th><th>Bad Debt</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($b['booking_ref']); ?></strong></td>
            <td><?php echo htmlspecialchars($b['customer_name']); ?><br><small><?php echo $b['customer_phone']; ?></small></td>
            <td><?php echo htmlspecialchars(isset($b['vname']) ? $b['vname'] : '—'); ?></td>
            <td><?php echo htmlspecialchars(isset($b['plate']) ? $b['plate'] : '—'); ?></td>
            <td><?php echo $b['pickup_date']; ?></td>
            <td><?php echo $b['return_date'] ? $b['return_date'] : '—'; ?></td>
            <td><?php echo $b['mileage_out'] ? $b['mileage_out'] : '—'; ?></td>
            <td><?php echo $b['mileage_in']  ? $b['mileage_in']  : '—'; ?></td>
            <td style="color:#22c55e"><?php echo formatMoney($b['amount_paid']); ?></td>
            <td style="color:#f59e0b"><?php echo formatMoney($b['amount_pending']); ?></td>
            <td style="color:#ef4444"><?php echo formatMoney($b['bad_debt']); ?></td>
            <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
            <td>
                <button class="btn btn-ghost btn-xs" onclick="openEdit(<?php echo htmlspecialchars(json_encode($b)); ?>)">Edit</button>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Delete?')">Del</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="editModal" class="modal" style="display:none">
    <div class="modal-box">
        <div class="modal-header"><h3>Edit Booking</h3><button onclick="document.getElementById('editModal').style.display='none'" class="modal-close">X</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="booking_id" id="edit_id">
            <div class="form-grid-2">
                <div class="form-group"><label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group"><label>Mileage Out</label><input type="number" name="mileage_out" id="edit_mil_out"></div>
                <div class="form-group"><label>Mileage In</label><input type="number" name="mileage_in" id="edit_mil_in"></div>
                <div class="form-group"><label>Amount Paid (E)</label><input type="number" step="0.01" name="amount_paid" id="edit_paid"></div>
                <div class="form-group"><label>Amount Pending (E)</label><input type="number" step="0.01" name="amount_pending" id="edit_pending"></div>
                <div class="form-group"><label>Bad Debt (E)</label><input type="number" step="0.01" name="bad_debt" id="edit_debt"></div>
            </div>
            <div class="form-group"><label>Notes</label><textarea name="notes" id="edit_notes" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEdit(b) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('edit_id').value       = b.id;
    document.getElementById('edit_status').value   = b.status;
    document.getElementById('edit_mil_out').value  = b.mileage_out || '';
    document.getElementById('edit_mil_in').value   = b.mileage_in  || '';
    document.getElementById('edit_paid').value     = b.amount_paid    || 0;
    document.getElementById('edit_pending').value  = b.amount_pending || 0;
    document.getElementById('edit_debt').value     = b.bad_debt       || 0;
    document.getElementById('edit_notes').value    = b.notes || '';
}
</script>

<?php include 'admin_footer.php'; ?>
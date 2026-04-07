<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Fleet Management';
$msg = '';

// ADD VEHICLE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name  = clean($conn, $_POST['name']);
    $cat   = clean($conn, $_POST['category']);
    $plate = clean($conn, $_POST['plate']);
    $seats = (int)$_POST['seats'];
    $trans = clean($conn, $_POST['transmission']);
    $fuel  = clean($conn, $_POST['fuel']);
    $mil   = (int)$_POST['mileage'];
    $notes = clean($conn, $_POST['notes']);
    $imgName = 'default.jpg';
    if (!empty($_FILES['image']['name'])) {
        $ext     = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imgName = strtolower(str_replace(' ', '_', $name)) . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $imgName);
    }
    $sql = "INSERT INTO vehicles (name,category,plate,seats,transmission,fuel,mileage,notes,image)
            VALUES ('$name','$cat','$plate',$seats,'$trans','$fuel',$mil,'$notes','$imgName')";
    if (mysqli_query($conn, $sql)) $msg = 'success:Vehicle added successfully.';
    else $msg = 'error:' . mysqli_error($conn);
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM vehicles WHERE id=$id");
    $msg = 'success:Vehicle deleted.';
}

// TOGGLE STATUS
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $cur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM vehicles WHERE id=$id"))['status'];
    $new = ($cur === 'available') ? 'booked' : 'available';
    mysqli_query($conn, "UPDATE vehicles SET status='$new' WHERE id=$id");
    $msg = 'success:Status updated to ' . $new;
}

// UPDATE MILEAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_mileage') {
    $id  = (int)$_POST['vehicle_id'];
    $mil = (int)$_POST['mileage'];
    mysqli_query($conn, "UPDATE vehicles SET mileage=$mil WHERE id=$id");
    $msg = 'success:Mileage updated.';
}

$vehicles = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY status ASC, name ASC");
?>
<?php include 'admin_header.php'; ?>

<?php if ($msg): 
    $parts = explode(':', $msg, 2);
    $mtype = $parts[0]; $mtext = $parts[1];
?>
<div class="alert alert-<?php echo $mtype === 'success' ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($mtext); ?></div>
<?php endif; ?>

<div class="admin-card mb-20">
    <div class="card-header"><h3>Add New Vehicle</h3></div>
    <div style="padding:20px">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="form-grid-3">
            <div class="form-group"><label>Vehicle Name</label><input type="text" name="name" required placeholder="e.g. Toyota Fortuner"></div>
            <div class="form-group"><label>Number Plate</label><input type="text" name="plate" required placeholder="e.g. MK70HF8P"></div>
            <div class="form-group"><label>Category</label>
                <select name="category" required>
                    <option value="SUV">SUV</option>
                    <option value="Sedan">Sedan</option>
                    <option value="Double Cab">Double Cab</option>
                    <option value="Single Cab">Single Cab</option>
                    <option value="Van">Van</option>
                </select>
            </div>
            <div class="form-group"><label>Seats</label><input type="number" name="seats" value="5" min="1" max="20"></div>
            <div class="form-group"><label>Transmission</label>
                <select name="transmission"><option>Automatic</option><option>Manual</option></select>
            </div>
            <div class="form-group"><label>Fuel</label>
                <select name="fuel"><option>Petrol</option><option>Diesel</option></select>
            </div>
            <div class="form-group"><label>Current Mileage (km)</label><input type="number" name="mileage" value="0"></div>
            <div class="form-group"><label>Vehicle Photo</label><input type="file" name="image" accept="image/*"></div>
            <div class="form-group"><label>Notes</label><input type="text" name="notes" placeholder="Optional notes"></div>
        </div>
        <button type="submit" class="btn btn-primary">Add Vehicle</button>
    </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header"><h3>All Vehicles (<?php echo mysqli_num_rows($vehicles); ?>)</h3></div>
    <table class="admin-table">
        <thead><tr><th>Photo</th><th>Vehicle</th><th>Plate</th><th>Category</th><th>Seats</th><th>Mileage</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($v = mysqli_fetch_assoc($vehicles)): ?>
        <tr>
            <td>
                <?php if ($v['image'] && $v['image'] !== 'default.jpg' && file_exists($v['image'])): ?>
                <img src="<?php echo htmlspecialchars($v['image']); ?>" style="width:60px;height:40px;object-fit:cover;border-radius:6px;">
                <?php else: ?>
                <div style="width:60px;height:40px;background:rgba(30,58,138,0.2);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;">🚗</div>
                <?php endif; ?>
            </td>
            <td><strong><?php echo htmlspecialchars($v['name']); ?></strong><br><small style="color:#888"><?php echo $v['fuel']; ?> &middot; <?php echo $v['transmission']; ?></small></td>
            <td><?php echo htmlspecialchars($v['plate']); ?></td>
            <td><?php echo $v['category']; ?></td>
            <td><?php echo $v['seats']; ?></td>
            <td>
                <form method="POST" style="display:flex;gap:6px;align-items:center;">
                    <input type="hidden" name="action" value="update_mileage">
                    <input type="hidden" name="vehicle_id" value="<?php echo $v['id']; ?>">
                    <input type="number" name="mileage" value="<?php echo $v['mileage']; ?>" style="width:90px;padding:4px 8px;border-radius:6px;border:1px solid #333;background:#0d1f42;color:white;font-size:13px">
                    <button type="submit" class="btn btn-ghost btn-xs">Save</button>
                </form>
            </td>
            <td><span class="badge badge-<?php echo $v['status']; ?>"><?php echo ucfirst($v['status']); ?></span></td>
            <td>
                <a href="?toggle=<?php echo $v['id']; ?>" class="btn btn-ghost btn-xs">Toggle</a>
                <a href="?delete=<?php echo $v['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this vehicle?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'admin_footer.php'; ?>
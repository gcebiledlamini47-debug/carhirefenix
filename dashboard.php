<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
$pageTitle = 'Dashboard';

$totalRevenueRes = mysqli_query($conn, "SELECT SUM(amount_paid) as t FROM bookings");
$totalRevenueRow = mysqli_fetch_assoc($totalRevenueRes);
$totalRevenue    = $totalRevenueRow['t'] ? $totalRevenueRow['t'] : 0;

$activeRow       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE status='active'"));
$activeBookings  = $activeRow['c'];

$pendingRow      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings WHERE status='pending'"));
$pendingBookings = $pendingRow['c'];

$availRow        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM vehicles WHERE status='available'"));
$availableCars   = $availRow['c'];

$totalCarsRow    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM vehicles"));
$totalCars       = $totalCarsRow['c'];

$badDebtRow      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(bad_debt) as t FROM bookings"));
$totalBadDebt    = $badDebtRow['t'] ? $badDebtRow['t'] : 0;

$pendingPayRow   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount_pending) as t FROM bookings"));
$totalPending    = $pendingPayRow['t'] ? $pendingPayRow['t'] : 0;

$recentBookings  = mysqli_query($conn, "SELECT b.*,v.name as vname FROM bookings b LEFT JOIN vehicles v ON b.vehicle_id=v.id ORDER BY b.created_at DESC LIMIT 5");
$recentInvoices  = mysqli_query($conn, "SELECT * FROM invoices ORDER BY created_at DESC LIMIT 5");
?>
<?php include 'admin_header.php'; ?>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value"><?php echo formatMoney($totalRevenue); ?></div>
        <div class="stat-sub">All time collected</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Active Bookings</div>
        <div class="stat-value"><?php echo $activeBookings; ?></div>
        <div class="stat-sub"><?php echo $pendingBookings; ?> pending approval</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Available Vehicles</div>
        <div class="stat-value"><?php echo $availableCars; ?> / <?php echo $totalCars; ?></div>
        <div class="stat-sub">Fleet availability</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Pending Payments</div>
        <div class="stat-value"><?php echo formatMoney($totalPending); ?></div>
        <div class="stat-sub">Bad debt: <?php echo formatMoney($totalBadDebt); ?></div>
    </div>
</div>

<div class="dash-grid">
    <div class="admin-card">
        <div class="card-header">
            <h3>Recent Bookings</h3>
            <a href="bookings.php" class="card-link">View All</a>
        </div>
        <div class="table-wrap"><table class="admin-table">
            <thead><tr><th>Ref</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($b = mysqli_fetch_assoc($recentBookings)): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($b['booking_ref']); ?></strong></td>
                <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                <td><?php echo htmlspecialchars(isset($b['vname']) ? $b['vname'] : '—'); ?></td>
                <td><?php echo $b['pickup_date']; ?></td>
                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="admin-card">
        <div class="card-header">
            <h3>Recent Invoices</h3>
            <a href="invoices.php" class="card-link">View All</a>
        </div>
        <div class="table-wrap"><table class="admin-table">
            <thead><tr><th>No.</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($inv = mysqli_fetch_assoc($recentInvoices)): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($inv['invoice_no']); ?></strong></td>
                <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                <td><?php echo formatMoney($inv['total']); ?></td>
                <td><span class="badge badge-<?php echo $inv['status']; ?>"><?php echo ucfirst($inv['status']); ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
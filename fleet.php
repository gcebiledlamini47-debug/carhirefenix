<?php
// ============================================================
// FENIX CAR HIRE - Fleet Page
// File: fleet.php (flat structure)
// ============================================================
require_once 'db.php';
$pageTitle = 'Our Fleet';

$filter = isset($_GET['category']) ? clean($conn, $_GET['category']) : 'All';
$cats   = array('All','SUV','Sedan','Double Cab','Single Cab','Van');

$sql = "SELECT * FROM vehicles";
if ($filter !== 'All') {
    $sql .= " WHERE category = '$filter'";
}
$sql .= " ORDER BY status ASC, name ASC";
$vehicles = mysqli_query($conn, $sql);
?>
<?php include 'header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>Our Fleet</h1>
        <p>Choose from our range of well-maintained vehicles</p>
    </div>
</div>

<section class="section bg-white">
    <div class="container">

        <!-- Category Filter -->
        <div class="filter-bar">
            <?php foreach ($cats as $cat): ?>
            <?php
            $catUrl     = ($cat !== 'All') ? 'fleet.php?category=' . urlencode($cat) : 'fleet.php';
            $activeClass = ($filter === $cat) ? 'active' : '';
            ?>
            <a href="<?php echo $catUrl; ?>" class="filter-btn <?php echo $activeClass; ?>">
                <?php echo $cat; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="fleet-grid">
            <?php while ($car = mysqli_fetch_assoc($vehicles)): ?>
            <?php
            $cardClass  = ($car['status'] !== 'available') ? 'car-card booked' : 'car-card';
            $badgeClass = ($car['status'] === 'available')  ? 'badge-available' : 'badge-booked';
            $badgeText  = ($car['status'] === 'available')  ? 'Available' : ucfirst($car['status']);

            // IMAGE: check file exists in same folder (flat structure)
            $imgFile   = $car['image'];
            $hasImage  = (!empty($imgFile) && $imgFile !== 'default.jpg' && file_exists($imgFile));
            ?>
            <div class="<?php echo $cardClass; ?>" id="car-<?php echo $car['id']; ?>">
                <div class="car-img-wrap">
                    <?php if ($hasImage): ?>
                        <img src="<?php echo htmlspecialchars($imgFile); ?>"
                             alt="<?php echo htmlspecialchars($car['name']); ?>"
                             class="car-img">
                    <?php else: ?>
                        <div class="car-placeholder">🚗</div>
                    <?php endif; ?>
                    <span class="car-badge <?php echo $badgeClass; ?>">
                        <?php echo $badgeText; ?>
                    </span>
                </div>
                <div class="car-info">
                    <h3 class="car-name"><?php echo htmlspecialchars($car['name']); ?></h3>
                    <div class="car-meta">
                        <?php echo $car['category']; ?> &middot;
                        <?php echo $car['seats']; ?> Seats &middot;
                        <?php echo $car['transmission']; ?> &middot;
                        <?php echo $car['fuel']; ?>
                    </div>
                    <div class="car-specs">
                        <div class="spec"><span>&#128273;</span> <?php echo htmlspecialchars($car['plate']); ?></div>
                        <div class="spec"><span>&#128205;</span> <?php echo number_format($car['mileage']); ?> km</div>
                    </div>
                    <div class="car-actions">
                        <?php if ($car['status'] === 'available'): ?>
                        <a href="booking.php?vehicle_id=<?php echo $car['id']; ?>" class="btn btn-gold btn-sm">Book Now</a>
                        <?php else: ?>
                        <span class="btn btn-disabled btn-sm">Unavailable</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>
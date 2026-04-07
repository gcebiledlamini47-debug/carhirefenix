<?php
require_once 'db.php';
$isHomePage = true;
$pageTitle = 'Home';
$totalRow  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM vehicles"));
$total     = $totalRow['cnt'];
$fleet     = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY status ASC, id ASC LIMIT 6");

// Check which slide images exist in this folder
$slides = array();
for ($i = 1; $i <= 5; $i++) {
    $f = 'slide' . $i . '.jpg';
    if (file_exists($f)) {
        $slides[] = $f;
    }
}
// Fallback if no images found
if (empty($slides)) {
    $slides = array('slide1.jpg','slide2.jpg','slide3.jpg','slide4.jpg','slide5.jpg');
}
$slideCount = count($slides);
?>
<?php include 'header.php'; ?>

<!-- ══ HERO SLIDESHOW ══ -->
<section class="hero" id="home">

    <!-- BACKGROUND SLIDESHOW -->
    <div class="hero-slides" id="heroSlides">
        <?php foreach ($slides as $i => $img): ?>
        <div class="hero-slide <?php echo $i === 0 ? 'active' : ''; ?>"
             style="background-image: url('<?php echo $img; ?>');">
        </div>
        <?php endforeach; ?>
    </div>

    <!-- DARK OVERLAY -->

    <!-- ARROWS -->
    <button class="hero-arrow hero-prev" onclick="changeSlide(-1)">&#10094;</button>
    <button class="hero-arrow hero-next" onclick="changeSlide(1)">&#10095;</button>

    <!-- TEXT CONTENT -->
    <div class="hero-content">
        <div class="hero-badge">&#10022; Trusted Car Hire in Eswatini</div>
        <h1 class="hero-title">
            Your Journey,<br>
            <span class="hero-accent">Our Wheels.</span>
        </h1>
        <p class="hero-sub">
            Premium vehicle rental across Eswatini &amp; South Africa.
            From Fortuners to Quantums &mdash; we have the perfect ride for every occasion.
        </p>
        <div class="hero-actions">
            <a href="fleet.php" class="btn btn-primary btn-lg">Browse Our Fleet</a>
            <a href="https://wa.me/26876829797" target="_blank" class="btn btn-whatsapp btn-lg">WhatsApp Us</a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <span class="stat-num"><?php echo $total; ?>+</span>
                <span class="stat-label">Fleet Vehicles</span>
            </div>
            <div class="stat">
                <span class="stat-num">500+</span>
                <span class="stat-label">Happy Clients</span>
            </div>
            <div class="stat">
                <span class="stat-num">5 Star</span>
                <span class="stat-label">Service Rating</span>
            </div>

        </div>
    </div>

    <!-- DOTS -->
    <div class="hero-dots" id="heroDots">
        <?php for ($i = 0; $i < $slideCount; $i++): ?>
        <span class="hero-dot <?php echo $i === 0 ? 'active' : ''; ?>"
              onclick="goSlide(<?php echo $i; ?>)"></span>
        <?php endfor; ?>
    </div>

</section>

<script>
var cur    = 0;
var slides = document.querySelectorAll('.hero-slide');
var dots   = document.querySelectorAll('.hero-dot');
var timer  = null;

function showSlide(n) {
    var len = slides.length;
    if (n >= len) n = 0;
    if (n < 0)    n = len - 1;
    cur = n;
    for (var i = 0; i < len; i++) {
        slides[i].className = 'hero-slide';
        if (dots[i]) dots[i].className = 'hero-dot';
    }
    slides[cur].className = 'hero-slide active';
    if (dots[cur]) dots[cur].className = 'hero-dot active';
}

function changeSlide(dir) {
    showSlide(cur + dir);
    restart();
}

function goSlide(n) {
    showSlide(n);
    restart();
}

function restart() {
    clearInterval(timer);
    timer = setInterval(function(){ showSlide(cur + 1); }, 5000);
}

timer = setInterval(function(){ showSlide(cur + 1); }, 5000);
</script>

<!-- ══ ABOUT ══ -->
<section class="section bg-grey" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <div class="section-label">About Fenix Car Hire</div>
                <h2 class="section-title">Eswatini's Premier<br>Vehicle Rental Service</h2>
                <p>Based in the heart of Mbabane, Fenix Car Hire has been serving individuals, corporates, and organisations across Eswatini and South Africa. We pride ourselves on a modern, well-maintained fleet and exceptional customer service.</p>
                <p>Located at Lilanga Complex, Litsemba Street, Sidwashini — we make vehicle rental seamless, reliable, and affordable.</p>
                <div class="feature-tags">
                    <span class="feature-tag">Transparent Pricing</span>
                    <span class="feature-tag">Flexible Rentals</span>
                    <span class="feature-tag">Cross-Border Trips</span>
                    <span class="feature-tag">Corporate Accounts</span>
                </div>
            </div>
            <div class="about-cards">
                <?php
                $cards = array(
                    array('icon'=>'&#127970;','title'=>'Location', 'val'=>'Lilanga Complex, Sidwashini, Mbabane'),
                    array('icon'=>'&#128222;','title'=>'Phone',    'val'=>'(+268) 2422 1045'),
                    array('icon'=>'&#128241;','title'=>'WhatsApp', 'val'=>'(+268) 76829797'),
                    array('icon'=>'&#9993;',  'title'=>'Email',    'val'=>'reception@fenix.co.sz'),
                );
                foreach ($cards as $card):
                ?>
                <div class="info-card">
                    <div class="info-icon"><?php echo $card['icon']; ?></div>
                    <div class="info-label"><?php echo $card['title']; ?></div>
                    <div class="info-val"><?php echo $card['val']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ══ FLEET ══ -->
<section class="section" id="fleet">
    <div class="container">
        <div class="section-header">
            <div class="section-label">Our Fleet</div>
            <h2 class="section-title">Choose Your Perfect Ride</h2>
            <p class="section-sub">Browse our diverse fleet of well-maintained vehicles for any occasion</p>
        </div>
        <div class="fleet-grid">
            <?php while ($car = mysqli_fetch_assoc($fleet)): ?>
            <?php
            $cardClass  = ($car['status'] !== 'available') ? 'car-card booked' : 'car-card';
            $badgeClass = ($car['status'] === 'available')  ? 'badge-available' : 'badge-booked';
            $badgeText  = ($car['status'] === 'available')  ? 'Available' : ucfirst($car['status']);
            $imgFile    = $car['image'];
            $hasImage   = (!empty($imgFile) && $imgFile !== 'default.jpg' && file_exists($imgFile));
            ?>
            <div class="<?php echo $cardClass; ?>">
                <div class="car-img-wrap">
                    <?php if ($hasImage): ?>
                    <img src="<?php echo htmlspecialchars($imgFile); ?>"
                         alt="<?php echo htmlspecialchars($car['name']); ?>"
                         class="car-img">
                    <?php else: ?>
                    <div class="car-placeholder">&#128663;</div>
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
                    <div class="car-actions">
                        <?php if ($car['status'] === 'available'): ?>
                        <a href="booking.php?vehicle_id=<?php echo $car['id']; ?>" class="btn btn-gold btn-sm">Book Now</a>
                        <?php else: ?>
                        <span class="btn btn-disabled btn-sm">Unavailable</span>
                        <?php endif; ?>
                        <a href="fleet.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-40">
            <a href="fleet.php" class="btn btn-outline btn-lg">View Full Fleet &rarr;</a>
        </div>
    </div>
</section>

<!-- ══ SERVICES ══ -->
<section class="section bg-grey" id="services">
    <div class="container">
        <div class="section-header">
            <div class="section-label">Services</div>
            <h2 class="section-title">What We Offer</h2>
        </div>
        <div class="services-grid">
            <?php
            $services = array(
                array('icon'=>'&#128663;','title'=>'Daily Rentals',        'desc'=>'Short-term vehicle hire for business or leisure trips.'),
                array('icon'=>'&#128197;','title'=>'Long-Term Hire',       'desc'=>'Weekly and monthly rental packages tailored to your budget.'),
                array('icon'=>'&#127758;','title'=>'Cross-Border',         'desc'=>'Travel to South Africa and neighbouring countries with ease.'),
                array('icon'=>'&#127970;','title'=>'Corporate Accounts',   'desc'=>'Dedicated accounts and invoicing for businesses.'),
                array('icon'=>'&#9889;',  'title'=>'Airport Transfers',    'desc'=>'Reliable pickup and drop-off at Matsapha Airport.'),
                array('icon'=>'&#128295;','title'=>'Well-Maintained Fleet','desc'=>'All vehicles regularly serviced and roadworthy.'),
            );
            foreach ($services as $svc):
            ?>
            <div class="service-card">
                <div class="service-icon"><?php echo $svc['icon']; ?></div>
                <h3><?php echo $svc['title']; ?></h3>
                <p><?php echo $svc['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Ready to Hit the Road?</h2>
        <p>Book your vehicle today &mdash; quick, easy, and reliable.</p>
        <div class="hero-actions">
            <a href="booking.php" class="btn btn-primary btn-lg">Book Now</a>
            <a href="contact.php" class="btn btn-outline btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<?php
// ============================================================
// FENIX CAR HIRE - Booking Page
// Sends email to reception@fenix.co.sz on submission
// Admin can reply directly to customer via Reply-To header
// ============================================================
require_once 'db.php';
$pageTitle = 'Book a Vehicle';

$success    = false;
$error      = '';
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

// Fetch all available vehicles for dropdown
$vehicles = mysqli_query($conn, "SELECT * FROM vehicles WHERE status='available' ORDER BY name");

// Pre-selected vehicle
$selected = null;
if ($vehicle_id) {
    $res      = mysqli_query($conn, "SELECT * FROM vehicles WHERE id=$vehicle_id AND status='available'");
    $selected = mysqli_fetch_assoc($res);
}

// ── HANDLE FORM SUBMISSION ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = isset($_POST['customer_name'])  ? clean($conn, $_POST['customer_name'])  : '';
    $email      = isset($_POST['customer_email']) ? clean($conn, $_POST['customer_email']) : '';
    $phone      = isset($_POST['customer_phone']) ? clean($conn, $_POST['customer_phone']) : '';
    $vid        = isset($_POST['vehicle_id'])     ? (int)$_POST['vehicle_id']              : 0;
    $pickup     = isset($_POST['pickup_date'])    ? clean($conn, $_POST['pickup_date'])    : '';
    $returnDate = isset($_POST['return_date'])    ? clean($conn, $_POST['return_date'])    : '';
    $notes      = isset($_POST['notes'])          ? clean($conn, $_POST['notes'])          : '';

    if (!$name || !$phone || !$pickup || !$vid) {
        $error = 'Please fill in all required fields.';
    } else {
        // Get vehicle name
        $vRes  = mysqli_query($conn, "SELECT name, plate FROM vehicles WHERE id=$vid");
        $vRow  = mysqli_fetch_assoc($vRes);
        $vname = $vRow ? $vRow['name'] . ' (' . $vRow['plate'] . ')' : 'Unknown Vehicle';

        // Generate booking reference
        $ref = generateRef('BK');

        // Save booking to database
        $sql = "INSERT INTO bookings
                    (booking_ref, customer_name, customer_email, customer_phone,
                     vehicle_id, pickup_date, return_date, notes, status)
                VALUES
                    ('$ref','$name','$email','$phone',$vid,'$pickup','$returnDate','$notes','pending')";

        if (mysqli_query($conn, $sql)) {

            // Mark vehicle as booked
            mysqli_query($conn, "UPDATE vehicles SET status='booked' WHERE id=$vid");

            // ── SEND EMAIL TO ADMIN ──────────────────────────────
            $to      = 'reception@fenix.co.sz';
            $subject = '[Fenix Car Hire] New Booking Request — ' . $ref;

            $emailBody  = "==============================================\n";
            $emailBody .= "  NEW BOOKING REQUEST — FENIX CAR HIRE\n";
            $emailBody .= "==============================================\n\n";
            $emailBody .= "Booking Reference : " . $ref . "\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "CUSTOMER DETAILS\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "Full Name        : " . $name . "\n";
            $emailBody .= "Phone Number     : " . $phone . "\n";
            $emailBody .= "Email Address    : " . ($email ? $email : 'Not provided') . "\n\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "BOOKING DETAILS\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "Vehicle          : " . $vname . "\n";
            $emailBody .= "Pick-up Date     : " . $pickup . "\n";
            $emailBody .= "Return Date      : " . ($returnDate ? $returnDate : 'Not specified') . "\n";
            $emailBody .= "Additional Notes : " . ($notes ? $notes : 'None') . "\n\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "HOW TO RESPOND\n";
            $emailBody .= "----------------------------------------------\n";
            $emailBody .= "- Reply to this email to contact the customer directly\n";
            if ($email) {
                $emailBody .= "- Customer email: " . $email . "\n";
            }
            $emailBody .= "- Call customer: " . $phone . "\n";
            $emailBody .= "- WhatsApp: https://wa.me/" . preg_replace('/[^0-9]/', '', '268' . $phone) . "\n\n";
            $emailBody .= "==============================================\n";
            $emailBody .= "This email was sent automatically from\n";
            $emailBody .= "the Fenix Car Hire website booking form.\n";
            $emailBody .= "Admin Dashboard: http://localhost/fenix-car-hire/dashboard.php\n";
            $emailBody .= "==============================================\n";

            // Headers — Reply-To set to customer so admin can reply directly
            $headers  = "From: Fenix Website <noreply@fenix.co.sz>\r\n";
            if ($email) {
                $headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
            } else {
                $headers .= "Reply-To: reception@fenix.co.sz\r\n";
            }
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($to, $subject, $emailBody, $headers);

            // ── ALSO SEND CONFIRMATION TO CUSTOMER ──────────────
            if ($email) {
                $custSubject = 'Your Booking Request — ' . $ref . ' | Fenix Car Hire';
                $custBody    = "Dear " . $name . ",\n\n";
                $custBody   .= "Thank you for your booking request at Fenix Car Hire!\n\n";
                $custBody   .= "Your booking reference is: " . $ref . "\n\n";
                $custBody   .= "BOOKING SUMMARY\n";
                $custBody   .= "----------------------------------------------\n";
                $custBody   .= "Vehicle    : " . $vname . "\n";
                $custBody   .= "Pick-up    : " . $pickup . "\n";
                $custBody   .= "Return     : " . ($returnDate ? $returnDate : 'To be confirmed') . "\n\n";
                $custBody   .= "Our team will contact you shortly to confirm your booking and provide pricing.\n\n";
                $custBody   .= "CONTACT US\n";
                $custBody   .= "----------------------------------------------\n";
                $custBody   .= "Phone   : (+268) 2422 1045\n";
                $custBody   .= "Mobile  : (+268) 76829797\n";
                $custBody   .= "Email   : reception@fenix.co.sz\n";
                $custBody   .= "Address : Lilanga Complex, Litsemba Street, Sidwashini, Mbabane\n\n";
                $custBody   .= "Kind regards,\n";
                $custBody   .= "Fenix Car Hire Team\n";
                $custBody   .= "For All Your Rental Needs\n";

                $custHeaders  = "From: Fenix Car Hire <reception@fenix.co.sz>\r\n";
                $custHeaders .= "Reply-To: reception@fenix.co.sz\r\n";
                $custHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

                @mail($email, $custSubject, $custBody, $custHeaders);
            }

            // ── ADD ADMIN NOTIFICATION ────────────────────────────
            $notifMsg = "New booking $ref from $name — $vname (Phone: $phone)";
            mysqli_query($conn, "INSERT INTO notifications (message,type) VALUES ('$notifMsg','booking')");

            $success = true;

        } else {
            $error = 'Booking could not be saved. Please try again or call us on (+268) 76829797.';
        }
    }
}

// Safe POST values for form repopulation
$postName    = isset($_POST['customer_name'])  ? htmlspecialchars($_POST['customer_name'])  : '';
$postEmail   = isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : '';
$postPhone   = isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : '';
$postPickup  = isset($_POST['pickup_date'])    ? htmlspecialchars($_POST['pickup_date'])    : '';
$postReturn  = isset($_POST['return_date'])    ? htmlspecialchars($_POST['return_date'])    : '';
$postNotes   = isset($_POST['notes'])          ? htmlspecialchars($_POST['notes'])          : '';
$postVehicle = isset($_POST['vehicle_id'])     ? (int)$_POST['vehicle_id']                 : 0;
?>
<?php include 'header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>Book a Vehicle</h1>
        <p>Fill in your details and we will confirm your booking</p>
    </div>
</div>

<section class="section bg-white">
    <div class="container" style="max-width:720px">

        <?php if ($success): ?>
        <!-- SUCCESS MESSAGE -->
        <div class="booking-success">
            <div class="success-icon">&#10003;</div>
            <h2>Booking Request Submitted!</h2>
            <p>Thank you, <strong><?php echo htmlspecialchars($name); ?></strong>! Your booking request has been received.</p>
            <div class="success-details">
                <div class="success-row">
                    <span class="success-label">Booking Reference</span>
                    <span class="success-val"><?php echo $ref; ?></span>
                </div>
                <div class="success-row">
                    <span class="success-label">Vehicle</span>
                    <span class="success-val"><?php echo htmlspecialchars($vname); ?></span>
                </div>
                <div class="success-row">
                    <span class="success-label">Pick-up Date</span>
                    <span class="success-val"><?php echo $pickup; ?></span>
                </div>
                <?php if ($returnDate): ?>
                <div class="success-row">
                    <span class="success-label">Return Date</span>
                    <span class="success-val"><?php echo $returnDate; ?></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="success-notice">
                <strong>What happens next?</strong><br>
                Our team will contact you at <strong><?php echo htmlspecialchars($phone); ?></strong>
                <?php if ($email): ?> or <strong><?php echo htmlspecialchars($email); ?></strong><?php endif; ?>
                to confirm your booking and provide pricing.
                <?php if ($email): ?>
                <br><br>A confirmation email has been sent to your inbox.
                <?php endif; ?>
            </div>
            <div class="success-contact">
                <p>Need immediate assistance?</p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:12px">
                    <a href="https://wa.me/26876829797" target="_blank" class="btn btn-whatsapp">WhatsApp Us</a>
                    <a href="tel:+26824221045" class="btn btn-primary">Call (+268) 2422 1045</a>
                    <a href="index.php" class="btn btn-ghost">Back to Home</a>
                </div>
            </div>
        </div>

        <?php else: ?>

        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h2 style="margin-bottom:6px;color:#1e293b">Booking Request Form</h2>
            <p style="color:#64748b;margin-bottom:28px;font-size:14px">
                Complete the form below and our team will contact you to confirm your booking and provide pricing.
            </p>

            <form method="POST" action="">

                <!-- CUSTOMER DETAILS -->
                <div class="form-section-title">Your Details</div>
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="customer_name" required placeholder="Your full name" value="<?php echo $postName; ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="tel" name="customer_phone" required placeholder="e.g. 76012345" value="<?php echo $postPhone; ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="customer_email" placeholder="email@example.com" value="<?php echo $postEmail; ?>">
                        <small style="color:#64748b;font-size:11px">For booking confirmation email</small>
                    </div>
                </div>

                <!-- BOOKING DETAILS -->
                <div class="form-section-title" style="margin-top:8px">Booking Details</div>
                <div class="form-group">
                    <label>Select Vehicle <span class="required">*</span></label>
                    <select name="vehicle_id" required>
                        <option value="">-- Choose a vehicle --</option>
                        <?php
                        mysqli_data_seek($vehicles, 0);
                        while ($v = mysqli_fetch_assoc($vehicles)):
                            $isSel = ($selected && $selected['id'] == $v['id']) || ($postVehicle == $v['id']);
                        ?>
                        <option value="<?php echo $v['id']; ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['name']); ?> — <?php echo $v['category']; ?> (<?php echo $v['plate']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Pick-up Date <span class="required">*</span></label>
                        <input type="date" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $postPickup; ?>">
                    </div>
                    <div class="form-group">
                        <label>Return Date</label>
                        <input type="date" name="return_date" value="<?php echo $postReturn; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Additional Notes</label>
                    <textarea name="notes" rows="4" placeholder="Destination, purpose of hire, special requirements..."><?php echo $postNotes; ?></textarea>
                </div>

                <div class="form-note">
                    &#128274; Prices are not shown online. Our team will provide a full quotation upon confirmation.
                    A refundable deposit may be required.
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
                    Submit Booking Request &rarr;
                </button>

                <p style="text-align:center;margin-top:14px;font-size:13px;color:#64748b">
                    Prefer to call? Reach us on <strong>(+268) 76829797</strong> or <strong>(+268) 2422 1045</strong>
                </p>

            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Booking page extra styles -->
<style>
.form-section-title {
    font-size: 11px; font-weight: 700; color: #1e3a8a;
    text-transform: uppercase; letter-spacing: 0.12em;
    margin-bottom: 14px; padding-bottom: 6px;
    border-bottom: 2px solid #dbeafe;
}
.booking-success {
    background: white; border-radius: 16px; padding: 48px 36px;
    text-align: center; box-shadow: 0 4px 24px rgba(30,58,138,0.1);
    border: 1px solid #e2e8f0;
}
.success-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #1e3a8a; color: white;
    font-size: 36px; line-height: 72px; margin: 0 auto 20px;
    box-shadow: 0 4px 20px rgba(30,58,138,0.3);
}
.booking-success h2 { font-size: 26px; color: #1e293b; margin-bottom: 10px; }
.booking-success > p { color: #475569; margin-bottom: 28px; font-size: 15px; }
.success-details {
    background: #f8faff; border: 1px solid #dbeafe;
    border-radius: 10px; padding: 20px; margin-bottom: 24px; text-align: left;
}
.success-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px;
}
.success-row:last-child { border-bottom: none; }
.success-label { color: #64748b; font-weight: 600; }
.success-val   { color: #1e293b; font-weight: 700; }
.success-notice {
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 10px; padding: 16px 20px; margin-bottom: 24px;
    font-size: 14px; color: #1e3a8a; line-height: 1.6; text-align: left;
}
.success-contact p { color: #64748b; font-size: 14px; }
</style>

<?php include 'footer.php'; ?>
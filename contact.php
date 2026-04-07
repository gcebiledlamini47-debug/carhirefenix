<?php
// ============================================================
// FENIX CAR HIRE - Contact Page
// Sends email to reception@fenix.co.sz with Reply-To customer
// ============================================================
require_once 'db.php';
$pageTitle = 'Contact Us';
$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cname  = isset($_POST['name'])    ? clean($conn, $_POST['name'])    : '';
    $cphone = isset($_POST['phone'])   ? clean($conn, $_POST['phone'])   : '';
    $cemail = isset($_POST['email'])   ? clean($conn, $_POST['email'])   : '';
    $cmsg   = isset($_POST['message']) ? clean($conn, $_POST['message']) : '';

    if (!$cname || !$cmsg) {
        $error = 'Please fill in your name and message.';
    } else {
        // ── EMAIL TO ADMIN ────────────────────────────────────
        $to      = 'reception@fenix.co.sz';
        $subject = '[Fenix Car Hire] New Website Enquiry from ' . $cname;

        $body  = "==============================================\n";
        $body .= "  NEW WEBSITE ENQUIRY — FENIX CAR HIRE\n";
        $body .= "==============================================\n\n";
        $body .= "SENDER DETAILS\n";
        $body .= "----------------------------------------------\n";
        $body .= "Name    : " . $cname  . "\n";
        $body .= "Phone   : " . ($cphone ? $cphone : 'Not provided') . "\n";
        $body .= "Email   : " . ($cemail ? $cemail : 'Not provided') . "\n\n";
        $body .= "MESSAGE\n";
        $body .= "----------------------------------------------\n";
        $body .= $cmsg . "\n\n";
        $body .= "----------------------------------------------\n";
        $body .= "HOW TO RESPOND\n";
        $body .= "----------------------------------------------\n";
        $body .= "- Simply reply to this email to respond directly to the customer\n";
        if ($cemail) {
            $body .= "- Email   : " . $cemail . "\n";
        }
        if ($cphone) {
            $body .= "- Call    : " . $cphone . "\n";
            $body .= "- WhatsApp: https://wa.me/" . preg_replace('/[^0-9]/', '', '268' . $cphone) . "\n";
        }
        $body .= "\n==============================================\n";
        $body .= "Sent from the Fenix Car Hire website contact form\n";
        $body .= "==============================================\n";

        // Reply-To set to customer so admin just hits Reply
        $headers  = "From: Fenix Website <noreply@fenix.co.sz>\r\n";
        if ($cemail) {
            $headers .= "Reply-To: " . $cname . " <" . $cemail . ">\r\n";
        } else {
            $headers .= "Reply-To: reception@fenix.co.sz\r\n";
        }
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        @mail($to, $subject, $body, $headers);

        // Save as notification in admin panel
        $notifMsg = "New enquiry from $cname" . ($cphone ? " (Phone: $cphone)" : '');
        mysqli_query($conn, "INSERT INTO notifications (message,type) VALUES ('$notifMsg','system')");

        $sent = true;
    }
}
?>
<?php include 'header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We are here to help with all your rental needs</p>
    </div>
</div>

<section class="section bg-white">
    <div class="container">
        <div class="contact-grid">
            <!-- LEFT: Contact Info -->
            <div>
                <h2 style="margin-bottom:6px;color:#1e293b">Get In Touch</h2>
                <p style="color:#64748b;margin-bottom:24px;font-size:14px">
                    Fill in the form and our team will get back to you, or reach us directly using the details below.
                </p>
                <div class="contact-cards">
                    <?php
                    $contacts = array(
                        array('icon'=>'📍','title'=>'Our Location',      'val'=>'Lilanga Complex, Litsemba Street, Sidwashini, Mbabane, Eswatini'),
                        array('icon'=>'📞','title'=>'Office Phone',      'val'=>'(+268) 2422 1045'),
                        array('icon'=>'📱','title'=>'Mobile / WhatsApp', 'val'=>'(+268) 76829797 &bull; (+268) 79846935'),
                        array('icon'=>'✉️','title'=>'Email',             'val'=>'reception@fenix.co.sz'),
                        array('icon'=>'🕐','title'=>'Office Hours',      'val'=>'Mon&ndash;Fri: 8:00&ndash;17:00 &bull; Sat: 8:00&ndash;13:00'),
                    );
                    foreach ($contacts as $c):
                    ?>
                    <div class="contact-card">
                        <div class="contact-icon"><?php echo $c['icon']; ?></div>
                        <div>
                            <div class="contact-label"><?php echo $c['title']; ?></div>
                            <div class="contact-value"><?php echo $c['val']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="social-row" style="margin-top:20px">
                    <a href="https://wa.me/26876829797" target="_blank" class="btn btn-whatsapp">&#128172; WhatsApp Us</a>
                    <a href="https://www.facebook.com/profile.php?id=61585823326473" target="_blank" class="btn btn-facebook">&#128140; Facebook</a>
                </div>
            </div>

            <!-- RIGHT: Contact Form -->
            <div class="form-card">
                <?php if ($sent): ?>
                <div style="text-align:center;padding:20px 0">
                    <div style="width:64px;height:64px;border-radius:50%;background:#1e3a8a;color:white;font-size:30px;line-height:64px;margin:0 auto 16px;">&#10003;</div>
                    <h3 style="color:#1e293b;margin-bottom:10px">Message Sent!</h3>
                    <p style="color:#475569;margin-bottom:20px;font-size:14px;line-height:1.6">
                        Thank you for reaching out! Our team will respond to you at:<br>
                        <?php if ($cemail): ?><strong><?php echo htmlspecialchars($cemail); ?></strong> or <?php endif; ?>
                        <strong><?php echo htmlspecialchars($cphone); ?></strong>
                    </p>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;font-size:13px;color:#1e3a8a;margin-bottom:20px">
                        For urgent enquiries call us directly on<br>
                        <strong>(+268) 76829797</strong> or <strong>(+268) 2422 1045</strong>
                    </div>
                    <a href="contact.php" class="btn btn-ghost" style="margin-right:8px">Send Another</a>
                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                </div>
                <?php else: ?>
                <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
                <h3 style="margin-bottom:6px;color:#1e293b">Send a Message</h3>
                <p style="color:#64748b;font-size:13px;margin-bottom:20px">We respond within 1 business hour during office hours.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Your Name <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="Full name"
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="e.g. 76012345"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="your@email.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <small style="color:#64748b;font-size:11px">So we can reply directly to you by email</small>
                    </div>
                    <div class="form-group">
                        <label>Message <span class="required">*</span></label>
                        <textarea name="message" rows="5" required
                                  placeholder="How can we help you? Ask about pricing, availability, cross-border travel..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;justify-content:center">
                        Send Message &rarr;
                    </button>
                    <p style="text-align:center;margin-top:12px;font-size:12px;color:#94a3b8">
                        Your message will be sent directly to <strong>reception@fenix.co.sz</strong>
                    </p>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Google Map -->
        <div class="map-box" style="margin-top:48px">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3580.0!2d31.1367!3d-26.3054!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjbCsDE4JzE5LjQiUyAzMcKwMDgnMTIuMSJF!5e0!3m2!1sen!2ssz"
                width="100%" height="320" style="border:0;border-radius:12px;display:block;" allowfullscreen loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
# Local Development Setup - Fenix Car Hire
## Step-by-Step Guide for XAMPP/LAMP

---

## Prerequisites
- Computer with Windows, macOS, or Linux
- Internet connection to download XAMPP
- At least 500MB free disk space
- Basic understanding of file management

---

## STEP 1: Download & Install XAMPP

### For Windows:
1. Go to https://www.apachefriends.org/download.html
2. Click **"XAMPP Windows"** (PHP 8.2+ recommended)
3. Download the installer (.exe file)
4. Run the installer
5. Choose installation folder (default: C:\xampp)
6. Select components to install:
   - Apache (required)
   - MySQL (required)
   - PHP (required)
   - phpMyAdmin (required)
7. Click **Install**
8. When asked "Do you want to install Perl?", click **No**
9. Installation complete!

### For macOS:
1. Go to https://www.apachefriends.org/download.html
2. Click **"XAMPP macOS"** (PHP 8.2+)
3. Download the .dmg file
4. Open the downloaded file
5. Drag XAMPP folder to Applications
6. Installation complete!

### For Linux:
1. Go to https://www.apachefriends.org/download.html
2. Click **"XAMPP Linux"** (PHP 8.2+)
3. Download the .run file
4. Open Terminal and run:
```bash
chmod +x xampp-linux-x64-8.2.x-installer.run
sudo ./xampp-linux-x64-8.2.x-installer.run
```
5. Follow the installer prompts
6. Installation complete!

---

## STEP 2: Start XAMPP Services

### For Windows:
1. Open XAMPP Control Panel from Start Menu
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Wait for both to show "Running" in green
5. You should see:
   - Apache: Running ✓
   - MySQL: Running ✓

### For macOS:
1. Open Finder → Applications → XAMPP
2. Double-click **XAMPP Control.app**
3. Click **Start** next to Apache
4. Click **Start** next to MySQL
5. Wait for both to show as running

### For Linux:
1. Open Terminal
2. Run:
```bash
sudo /opt/lampp/lampp start
```
3. You should see:
   - Starting Apache web server... already running
   - Starting MySQL database server... already running

---

## STEP 3: Set Up Project Files

### Step 3a: Locate the htdocs Folder
- **Windows**: C:\xampp\htdocs
- **macOS**: /Applications/XAMPP/htdocs
- **Linux**: /opt/lampp/htdocs

### Step 3b: Create Project Folder
1. Open your file manager
2. Navigate to the htdocs folder
3. Create a new folder called **carhirefenix**
4. Path should be: `C:\xampp\htdocs\carhirefenix` (Windows example)

### Step 3c: Copy Your Files
1. Download your project from GitHub (PR #2 code-changes branch)
2. Extract the files
3. Copy ALL files into the **carhirefenix** folder
4. Your structure should look like:
```
htdocs/
├── carhirefenix/
│   ├── index.php
│   ├── booking.php
│   ├── contact.php
│   ├── db.php
│   ├── auth.php
│   ├── login.php
│   ├── dashboard.php
│   ├── classes/
│   │   ├── Database.php
│   │   ├── SecurityHelper.php
│   │   └── Validator.php
│   ├── config/
│   │   └── config.php
│   ├── style.css
│   ├── fenix.sql
│   └── ... (other files)
```

---

## STEP 4: Create MySQL Database

### Step 4a: Open phpMyAdmin
1. Open your web browser
2. Go to: **http://localhost/phpmyadmin**
3. You should see phpMyAdmin login page

### Step 4b: Login to phpMyAdmin
- **Username**: root
- **Password**: (leave blank - press Enter)
- Click **Go**

### Step 4c: Create New Database
1. On the left sidebar, click **New**
2. Enter Database name: **fenix_db**
3. Collation: **utf8mb4_unicode_ci**
4. Click **Create**
5. You should see "fenix_db" created successfully

### Step 4d: Import Database Tables
1. Click on **fenix_db** (on the left)
2. Click the **Import** tab at the top
3. Click **Choose File**
4. Navigate to your project folder
5. Select **fenix.sql**
6. Click **Import**
7. Wait for message: "Import has been completed successfully"

### Step 4e: Verify Tables Created
1. On the left, expand **fenix_db**
2. You should see these tables:
   - admin_users
   - vehicles
   - bookings
   - invoices
   - checksheets
   - notifications

---

## STEP 5: Configure Environment Variables

### Step 5a: Update config.php
1. Open **carhirefenix/config/config.php** in a text editor
2. Verify these settings match your XAMPP setup:
```php
define('DB_HOST', 'localhost');      // Keep as localhost
define('DB_USER', 'root');           // Keep as root
define('DB_PASS', '');               // Empty password
define('DB_NAME', 'fenix_db');       // Database name
```
3. Save the file

### Step 5b: Verify db.php
1. Open **carhirefenix/db.php**
2. Ensure it includes the config file:
```php
require_once __DIR__ . '/config/config.php';
```
3. Save

---

## STEP 6: Access the Application

### Open in Browser
1. Open your web browser (Chrome, Firefox, Safari, Edge)
2. Go to: **http://localhost/carhirefenix**
3. You should see the Fenix Car Hire homepage

### Test the Website
- Click on "Fleet" - should show available vehicles
- Click on "Book a Vehicle" - should show booking form
- Click on "Contact Us" - should show contact form
- Go to "http://localhost/carhirefenix/login.php" - should show admin login

---

## STEP 7: Test Admin Login

### Admin Credentials
- **Username**: admin
- **Password**: fenix2026

### Login Steps
1. Go to: **http://localhost/carhirefenix/login.php**
2. Enter username: **admin**
3. Enter password: **fenix2026**
4. Click **Sign In**
5. You should see the admin dashboard

### Change Password (Important!)
1. After first login, go to admin settings
2. Change password from "fenix2026" to something secure
3. Save the new password

---

## STEP 8: Test Forms (CSRF Protection)

### Test Booking Form
1. Go to: **http://localhost/carhirefenix/booking.php**
2. Fill in the form:
   - Full Name: Test Customer
   - Phone: 76012345
   - Email: test@example.com
   - Select Vehicle: Any available
   - Pick-up Date: Tomorrow's date
3. Click **Submit Booking Request**
4. You should see success message with booking reference
5. Check admin notifications for the booking

### Test Contact Form
1. Go to: **http://localhost/carhirefenix/contact.php**
2. Fill in the form:
   - Name: Your Name
   - Phone: 76012345
   - Email: your@email.com
   - Message: Test message
3. Click **Send Message**
4. You should see success message

---

## STEP 9: Test Security Features

### Test SQL Injection Protection
1. Try to enter malicious input in forms (don't worry, it's protected!)
2. All queries use prepared statements - they are safe

### Test CSRF Protection
1. If you try to submit a form without the CSRF token, it will fail
2. The token is automatically generated for each form

### Test Rate Limiting
1. Try logging in with wrong password 5 times quickly
2. You should get rate-limited and see error message

### Test Session Timeout
1. Login to admin
2. Wait 30 minutes without activity
3. Try to access admin page
4. You'll be logged out for security

---

## STEP 10: Verify Database Operations

### Check Bookings in Database
1. Go to: **http://localhost/phpmyadmin**
2. Click **fenix_db** on the left
3. Click **bookings** table
4. Click **Browse** tab
5. You should see your test booking

### Check Admin Users
1. Click **admin_users** table
2. You should see the admin user with hashed password

---

## Troubleshooting

### Issue: "Cannot connect to database"
**Solution:**
1. Make sure MySQL is running (XAMPP Control Panel)
2. Verify credentials in config.php match XAMPP defaults
3. Check if fenix_db was created in phpMyAdmin

### Issue: "localhost/carhirefenix shows 404 error"
**Solution:**
1. Verify Apache is running
2. Check files are in correct folder: C:\xampp\htdocs\carhirefenix
3. Make sure index.php exists in the folder
4. Try clearing browser cache (Ctrl+Shift+Delete)

### Issue: "CSRF token mismatch error"
**Solution:**
1. This is normal if you're testing the form
2. Each page load generates a new token
3. The error means security is working!
4. Just refresh and try again

### Issue: "fenix.sql import failed"
**Solution:**
1. Make sure MySQL is running
2. Check file encoding is UTF-8
3. Try importing line by line instead
4. Check for file corruption - re-download fenix.sql

### Issue: "Session timeout error on login"
**Solution:**
1. This is a security feature
2. Just login again
3. The session will stay active for 30 minutes of use

---

## Next Steps: Deploy to Production

Once you've tested everything locally and confirmed it works:

1. **Review the changes** - Make sure everything works as expected
2. **Merge PR #2** into main branch on GitHub
3. **Choose hosting** - cPanel or VPS (see DEPLOYMENT_GUIDE.md)
4. **Deploy** - Follow deployment instructions for your hosting

---

## Quick Reference

| Task | URL |
|------|-----|
| Homepage | http://localhost/carhirefenix |
| Book Vehicle | http://localhost/carhirefenix/booking.php |
| Contact Us | http://localhost/carhirefenix/contact.php |
| Admin Login | http://localhost/carhirefenix/login.php |
| Admin Dashboard | http://localhost/carhirefenix/dashboard.php |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## Need Help?

If you encounter issues:
1. Check the Troubleshooting section above
2. Make sure XAMPP is running (Apache + MySQL)
3. Verify database was imported successfully
4. Check file permissions (read/write access)
5. Review error messages in browser console (F12)

Good luck with your local testing!

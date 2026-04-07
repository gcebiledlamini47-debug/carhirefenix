# Fenix Car Hire - Deployment Guide

## Overview
This guide covers deploying your enhanced Fenix Car Hire application to production with all security improvements.

---

## Option 1: Local Testing (XAMPP/LAMP)

### Step 1: Install PHP Server
**Windows/Mac:**
- Download XAMPP from https://www.apachefriends.org/
- Install and start Apache + MySQL

**Linux:**
```bash
sudo apt-get install lamp-server^
sudo systemctl start apache2
sudo systemctl start mysql
```

### Step 2: Copy Files
1. Copy your project to:
   - Windows/Mac: `C:\xampp\htdocs\carhirefenix` or `/Applications/XAMPP/htdocs/carhirefenix`
   - Linux: `/var/www/html/carhirefenix`

2. Ensure folder structure exists:
```
carhirefenix/
├── classes/
│   ├── Database.php
│   ├── SecurityHelper.php
│   └── Validator.php
├── config/
│   └── config.php
├── index.php
├── booking.php
├── contact.php
├── login.php
├── db.php
├── fenix.sql
└── ... (other files)
```

### Step 3: Set Up Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create new database: `fenix_db`
3. Import `fenix.sql`:
   - Click `fenix_db` database
   - Go to Import tab
   - Select `fenix.sql` file
   - Click Import

### Step 4: Configure Environment
Edit `.env` or set system environment variables:
```
MYSQLHOST=localhost
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=
MYSQLDATABASE=fenix_db
BASE_URL=/carhirefenix/
```

### Step 5: Test Locally
- Visit: http://localhost/carhirefenix
- Test booking form
- Admin login: http://localhost/carhirefenix/login.php
  - Username: `admin`
  - Password: `fenix2026` (CHANGE THIS!)

---

## Option 2: cPanel Hosting (Most Common)

### Step 1: Upload Files via FTP
1. Download FileZilla (https://filezilla-project.org/)
2. Connect to your hosting:
   - Host: your-domain.com
   - Username: FTP username from cPanel
   - Password: FTP password from cPanel
   - Port: 21
3. Navigate to `public_html` or your domain folder
4. Upload all files from `carhirefenix/`

### Step 2: Create MySQL Database
1. Log in to cPanel
2. Go to MySQL Databases
3. Create new database: `fenix_db`
4. Create user with full privileges
5. Import `fenix.sql`:
   - Go to phpMyAdmin
   - Select `fenix_db`
   - Import tab → Choose `fenix.sql`
   - Click Import

### Step 3: Update Database Credentials
Edit your `.env` or ask hosting provider to set environment variables:
```
MYSQLHOST=localhost
MYSQLUSER=fenix_user
MYSQLPASSWORD=your_secure_password
MYSQLDATABASE=fenix_db
```

Or edit `config/config.php` directly (less secure):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'fenix_user');
define('DB_PASS', 'your_secure_password');
define('DB_NAME', 'fenix_db');
```

### Step 4: Set File Permissions
- Folders: 755 (rwxr-xr-x)
- Files: 644 (rw-r--r--)
- Writable folders (/logs, /temp): 777

In cPanel File Manager:
1. Select folder → Right-click → Change Permissions
2. Set to 755 for folders, 644 for files

### Step 5: Email Configuration
Edit `config/config.php`:
```php
define('SUPPORT_EMAIL', 'your-email@yourdomain.com');
define('NOREPLY_EMAIL', 'noreply@yourdomain.com');
```

### Step 6: Test Live
- Visit: https://your-domain.com/carhirefenix
- Test booking form
- Admin: https://your-domain.com/carhirefenix/login.php

---

## Option 3: VPS Deployment (Advanced)

### Step 1: SSH into Server
```bash
ssh root@your-server-ip
```

### Step 2: Install LAMP Stack
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install apache2 mysql-server php php-mysql php-mbstring php-xml

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 3: Clone Repository
```bash
cd /var/www/html
git clone https://github.com/gcebiledlamini47-debug/carhirefenix.git
cd carhirefenix
chmod -R 755 .
```

### Step 4: Set Up Database
```bash
mysql -u root -p
CREATE DATABASE fenix_db;
CREATE USER 'fenix_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON fenix_db.* TO 'fenix_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u fenix_user -p fenix_db < fenix.sql
```

### Step 5: Configure Apache
Create `/etc/apache2/sites-available/carhirefenix.conf`:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/carhirefenix

    <Directory /var/www/html/carhirefenix>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/carhirefenix-error.log
    CustomLog ${APACHE_LOG_DIR}/carhirefenix-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite carhirefenix.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

### Step 6: Set Environment Variables
```bash
sudo nano /var/www/html/carhirefenix/.env
```

Add:
```
MYSQLHOST=localhost
MYSQLUSER=fenix_user
MYSQLPASSWORD=strong_password
MYSQLDATABASE=fenix_db
BASE_URL=https://your-domain.com/
```

### Step 7: SSL Certificate (HTTPS)
```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

---

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Admin login works (username: admin)
- [ ] **Change default admin password immediately**
- [ ] Booking form submits without errors
- [ ] Contact form sends emails
- [ ] File permissions are correct (755 folders, 644 files)
- [ ] HTTPS enabled (if applicable)
- [ ] Email notifications working
- [ ] Logs folder writable
- [ ] Backups scheduled

---

## Important Security Notes

### 1. Change Default Admin Password
After first deployment, change the default password:
```bash
# Access database
mysql -u fenix_user -p fenix_db

# Update password (use your own hash)
UPDATE admin_users SET password='$2y$10$YOUR_NEW_HASH_HERE' WHERE username='admin';
```

Or use this PHP script:
```php
<?php
require 'classes/SecurityHelper.php';
$newPassword = 'your-new-secure-password';
$hash = SecurityHelper::hashPassword($newPassword);
echo "New hash: " . $hash;
?>
```

### 2. Environment Variables
Never hardcode sensitive data. Use environment variables:
```php
$db_host = getenv('MYSQLHOST');
$db_user = getenv('MYSQLUSER');
$db_pass = getenv('MYSQLPASSWORD');
```

### 3. File Permissions
- Never use 777 (world-writable)
- Use 755 for directories, 644 for files
- Only writable: `/logs`, `/temp`, `/uploads`

### 4. Database Backups
Set up automated backups:
```bash
# Weekly backup
0 2 * * 0 mysqldump -u fenix_user -p'password' fenix_db > /backup/fenix_$(date +\%Y\%m\%d).sql
```

### 5. Monitor Logs
Check error logs regularly:
```bash
tail -f /var/log/apache2/carhirefenix-error.log
tail -f /path/to/carhirefenix/logs/activity.log
```

---

## Troubleshooting

### "Database connection failed"
- Check MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
- Verify database exists: `mysql -u user -p -e "SHOW DATABASES;"`
- Check MySQL service is running: `sudo systemctl status mysql`

### "Permission denied" errors
- Fix permissions: `chmod -R 755 /path/to/carhirefenix`
- Make logs writable: `chmod 777 /path/to/carhirefenix/logs`

### "Emails not sending"
- Check mail configuration in `config/config.php`
- Verify server has mail() enabled
- Check mail logs: `tail -f /var/log/mail.log`

### "Session timeout issues"
- Check `session.gc_maxlifetime` in php.ini (default 1440 seconds)
- Increase if needed: `session.gc_maxlifetime = 3600`

---

## Support

For issues or questions:
- Email: reception@fenix.co.sz
- Phone: (+268) 76829797
- GitHub Issues: Add to repository

---

**Last Updated:** April 2026
**Version:** 2.0 (Enhanced Security)

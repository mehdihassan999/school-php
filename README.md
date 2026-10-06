# Silver Oak International School — PHP Application

## Hostinger Deployment & Admin Password Setup Guide

### 1. Database Configuration (`includes/config.php`)
Update [`includes/config.php`](file:///c:/xampp/htdocs/school-php/includes/config.php) with your Hostinger database details:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost'); // Usually 'localhost' on Hostinger
define('DB_NAME', getenv('DB_NAME') ?: 'u123456789_school_db');
define('DB_USER', getenv('DB_USER') ?: 'u123456789_school_user');
define('DB_PASS', getenv('DB_PASS') ?: 'YourHostingerDBPassword');
```

---

### 2. Admin Login Credentials

Default Credentials in `database.sql`:
- **Email / Username:** `m.qazim1997@gmail.com` or `admin`
- **Password:** `B4lgh4r1786@#$%`

---

### 3. Resetting / Changing Admin Password on Hostinger

If your database is already imported on Hostinger and you want to update the existing admin user:

Run the following SQL query inside **phpMyAdmin → SQL**:

```sql
UPDATE admins 
SET password_hash = '$2y$10$aYEux01Fc.6TZXLNwpQdlOC1dmrAJurfXyEo8JH6iFiy8VRhRX5u2' 
WHERE email = 'm.qazim1997@gmail.com' OR username = 'admin';
```

---

### 4. Admin Panel Password Change
Once logged into the Admin Panel at `/admin/login.php`:
1. Go to **Settings → Admin Profile** tab (`/admin/settings.php?tab=account`).
2. Enter your current password and set your new password directly from the UI.

# Alumni Connect Portal - Getting Started Guide

## 1. Quick Access URL

Once Apache and MySQL are running in XAMPP, open this link in your browser:

👉 **[http://localhost/Clg%20Project/Alumni-Connect-Portal/](http://localhost/Clg%20Project/Alumni-Connect-Portal/)**

---

## 2. Pre-configured Demo Accounts

All sample accounts are pre-seeded with the password: **`changeme`**

| Role | Email | Password | Access / Features |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@college.edu` | `changeme` | Admin Dashboard, user management, post notices, promote users |
| **Alumni** | `rahul@gmail.com` | `changeme` | Alumni Dashboard, post jobs/referrals, view applications |
| **Alumni** | `priya@gmail.com` | `changeme` | Alumni Dashboard, post jobs/referrals, view applications |
| **Student** | `amit@college.edu` | `changeme` | Student Dashboard, browse alumni directory, apply for jobs |
| **Student** | `sneha@college.edu` | `changeme` | Student Dashboard, browse alumni directory, apply for jobs |

> **Note:** You can also register a brand new account anytime via `/register.php`.

---

## 3. How to Start the Project (XAMPP)

Whenever you reboot your PC or restart XAMPP:

1. Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
2. Click **Start** for:
   - **Apache**
   - **MySQL**
3. Open your browser and navigate to:
   ```
   http://localhost/Clg Project/Alumni-Connect-Portal/
   ```

---

## 4. Database Configuration

- **Database Name:** `alumni-connect-portal`
- **Host:** `127.0.0.1`
- **Username:** `root`
- **Password:** *(empty)*
- **Configuration File:** [`config/db.php`](config/db.php)
- **SQL Schema & Seed Script:** [`sql/schema.sql`](sql/schema.sql)

*(If you ever need to reset the database, open phpMyAdmin at `http://localhost/phpmyadmin/`, select the SQL tab, and execute the contents of `sql/schema.sql`)*

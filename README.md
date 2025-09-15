# Rose Pharmacy Management System

A lightweight pharmacy management system built with **PHP (OOP)**, **MySQL**, and **Bootstrap 4**, designed for small pharmacies to handle medicine inventory, employee access, and secure logins.

---

## 🚀 Features
- Secure login with role-based access control.
- Admin is also the pharmacist/doctor (small-scale setup).
- Medicine stock and sales management.
- Error logging system.
- User-friendly design with Bootstrap 4.
- First-time instructions popup for easy onboarding.

---

## 🖥️ Requirements
- Windows 10 or 11
- [XAMPP](https://www.apachefriends.org/download.html) (PHP 7.4+ recommended)
- Modern web browser (Chrome, Edge, Firefox)

---

## ⚙️ Installation Guide (Windows + XAMPP)

### Step 1: Install XAMPP
1. Download XAMPP from [Apache Friends](https://www.apachefriends.org/download.html).
2. Install it on your `C:\` drive (default path: `C:\xampp`).
3. Open **XAMPP Control Panel** and start:
   - **Apache**
   - **MySQL**

---

### Step 2: Setup the Project
1. Go to `C:\xampp\htdocs\`.
2. Create a new folder named **Rose-Pharmacy**.
3. Copy all project files into this folder.

So your path will look like:
```
C:\xampp\htdocs\Rose-Pharmacy-Management-System\
```

---

### Step 3: Configure the Database
1. Open your browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Click on **Databases** → create a new database:
   - Name: `rose_pharmacy_management_system`
   - Collation: `utf8mb4_general_ci`
3. The system will automatically create tables when first run (thanks to `Database.php`).

---

### Step 4: Configure Environment File
1. Inside the project, find `.env`.
2. Adjust values if needed:
   ```env
   APP_URL=http://localhost/Rose-Pharmacy-Management-System
   DB_DATABASE=rose_pharmacy
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   > ⚠️ By default, XAMPP uses:
   > - Username: `root`
   > - Password: *(leave blank)*

---

### Step 5: Run the Application
1. Open your browser and visit:
   ```
   http://localhost/Rose-Pharmacy-Management-System
   ```
2. You’ll see the login page.

---

### Step 6: First Login
Default admin credentials are:
- **Username:** `admin`
- **Password:** `admin123`

> ✅ You can change the password after logging in.

---

## 🛠️ Troubleshooting
- If **Apache** or **MySQL** won’t start:
  - Close other apps using ports 80 or 3306 (Skype, IIS, MySQL server).
  - Change port in XAMPP if needed.
- If login page looks broken:
  - Make sure you’re visiting `http://localhost/Rose-Pharmacy-Management-System` (not file path).
- If database is empty:
  - Ensure you created the database `rose_pharmacy_management_system` in phpMyAdmin.
  - Reload the page, system auto-creates missing tables.

---

## 📧 Support
For assistance, contact the system developer or check project documentation.

---

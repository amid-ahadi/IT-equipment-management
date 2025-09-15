# IT-equipment-management
> A secure, modern PHP-based inventory management system for printer cartridges — built for local office and repair shop environments.


---

## ✅ Features

| Feature | Description |
|--------|-------------|
| 🔐 **Secure Login** | Password hashing with `password_hash()`, CAPTCHA protection against bots |
| 📊 **Real-time Reporting** | Charts & tables showing full/empty cartridges by date, station & type |
| 🚀 **Bulk Add** | Add multiple cartridges at once with “IT Warehouse” tagging |
| 🛠️ **Manage Departments & Stations** | Add/remove departments, stations, and cartridge types on-the-fly |
| 📅 **Date Filtering** | Filter reports by date range, status, department, or station |
| 🌐 **RTL & Persian Support** | Fully right-to-left UI with Persian labels and input |
| 📱 **Responsive Design** | Works perfectly on desktop and mobile browsers |
| 🗃️ **No Frameworks** | Pure PHP + MySQL — zero dependencies, easy to host anywhere |

---

## 🛠️ Tech Stack

- **Backend**: PHP 8+  
- **Database**: MySQL / MariaDB  
- **Frontend**: HTML5, CSS3, JavaScript (no frameworks)  
- **Security**: PDO Prepared Statements, CSRF-free, Input Sanitization  
- **Hosting**: XAMPP, WAMP, Local Server, or Shared Hosting

---

## 📂 Project Structure
├── db.php # Database connection (PDO)

├── login.php # Secure login with CAPTCHA

├── index.php # Main dashboard

├── add.php # Add single cartridge

├── add_bulk.php # Process bulk additions

├── manage_ajax.php # AJAX handler for adding departments/stations/types

├── get_options.php # Fetch dropdown options (AJAX)

├── recent.php # Get last 10 records (AJAX)

├── report.php # Advanced reporting with filters

├── charts.php # Chart-only view (optional)

├── change_password.php # Change user password securely

├── logout.php # Session destroy

├── generate_captcha.php # Generate 4-digit CAPTCHA

├── style.css # Modern RTL styling

├── script.js # All frontend logic (AJAX, modals, filters)

├── install.sql # Full database schema + sample data

├── README.md # This file!



---

## 🚀 Installation Guide

|1. **Clone the repo**
|   ```bash
|   git clone https://github.com/yourusername/IT-equipment-management.git
   
|Place folder in your web server root
|(e.g., htdocs/Eito-code for XAMPP)
|Create database
|Run install.sql in phpMyAdmin:

CREATE DATABASE IF NOT EXISTS data_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE data_base;
-- Then run all SQL from install.sql

Configure db.php

$host = 'localhost';
$username = 'data_base'; // your DB username
$password = 'your_db_pass'; // your DB password
$dbname = 'data_base';

----------------
Open in browser
Go to: http://localhost/eito-code/login.php
Login
Username: admin
Password: 123456 (change after first login!)
🔒 Security Notes
✅ All inputs sanitized with htmlspecialchars() and PDO
✅ Passwords never stored in plain text
✅ CAPTCHA prevents automated login attempts
✅ No session hijacking — uses user_id and username only
✅ No external libraries — lightweight and secure
⚠️ Never expose this system on public internet without HTTPS and firewall rules. Designed for local/internal use. 

💡 Why This System?
This is not a tutorial — it’s a production-ready tool used in real repair shops and IT departments in Iran.
It was built to replace Excel sheets and paper logs — now tracking thousands of cartridges with zero errors.

📜 License
MIT © [Amid Ahadi] — Feel free to use, modify, and distribute.
Just credit the original author if you redistribute.

👥 Author
Developed by: [Amid Ahadi]
Contact: amid.ahadi@gmail.com
Website: c-security.ir
Location: Iran 🇮🇷
Built with ❤️ for local IT teams




---


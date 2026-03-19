# PHP CRUD Application – Setup Guide

## Project Structure

```
php_crud/
├── index.php          # Redirects to login or dashboard
├── login.php          # Admin login page
├── logout.php         # Session destroy + redirect
├── auth.php           # Session guard (include in every protected page)
├── db.php             # MySQL connection config
├── navbar.php         # Shared navigation bar
├── dashboard.php      # Admin dashboard with stats
├── add_user.php       # Add new user (with image upload)
├── view_users.php     # View/search all users
├── edit_user.php      # Edit existing user
├── delete_user.php    # Delete user + image cleanup
├── database.sql       # SQL to create DB and tables
├── css/
│   └── style.css      # All styles
└── uploads/           # Uploaded profile images (writable)
```

## Setup Instructions

### 1. Import Database
Open phpMyAdmin or MySQL CLI and run:
```sql
SOURCE /path/to/database.sql;
```

### 2. Configure Database Connection
Edit `db.php` and set your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // your MySQL username
define('DB_PASS', '');         // your MySQL password
define('DB_NAME', 'Assignment');
```

### 3. Set Permissions
Make the uploads folder writable:
```bash
chmod 755 uploads/
```

### 4. Place Project
Copy the `php_crud/` folder into your web server root:
- XAMPP: `C:/xampp/htdocs/php_crud/`
- WAMP:  `C:/wamp64/www/php_crud/`
- Linux: `/var/www/html/php_crud/`

### 5. Open in Browser
Navigate to: `http://localhost/php_crud/`

## Default Admin Credentials
| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ Change the password in production using `password_hash()`.

## userinfo Table Fields
| Column         | Type      | Notes              |
|----------------|-----------|--------------------|
| user_id        | INT       | Primary Key, Auto  |
| full_name      | VARCHAR   | Required           |
| email          | VARCHAR   | Required, Unique   |
| phone          | VARCHAR   | Optional           |
| address        | TEXT      | Optional           |
| profile_image  | VARCHAR   | Filename in uploads/ |
| created_at     | TIMESTAMP | Auto               |

## Image Upload Notes
- Allowed formats: JPG, PNG, GIF, WEBP
- Max file size: 2MB
- Stored in: `uploads/` folder
- Old images are automatically deleted when updated or user is deleted

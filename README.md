### PHP - UniKL Financial Aids System (UniFa)

## Setup Instructions

### 1. Database Setup

1. Make sure MySQL/MariaDB is running in Laragon
2. Open your browser and navigate to:
   ```
   http://localhost/unifa/database/setup.php
   ```
3. This will create the database `unifa_db` and all required tables

Alternatively, you can manually import the SQL file:
- Open phpMyAdmin or your MySQL client
- Import `database/schema.sql`

### 2. Database Configuration

The database configuration is in `config.php`. Default settings for Laragon:
- Host: `localhost`
- User: `root`
- Password: `` (empty)
- Database: `unifa_db`

If your MySQL credentials are different, update `config.php` accordingly.

### 3. Registration & Login

1. Navigate to the registration page: `http://localhost/unifa/pages/register.php`
2. Fill in the registration form
3. After successful registration, you'll be redirected to the Student Dashboard
4. You can login at: `http://localhost/unifa/pages/login.php`

### 4. Features

- User registration with validation
- Session management
- Student dashboard
- Database integration with MySQL
- Secure password hashing

## File Structure

```
unifa/
├── config.php                 # Database configuration
├── database/
│   ├── schema.sql            # Database schema
│   └── setup.php             # Database setup script
├── pages/
│   ├── register.php          # Registration page
│   ├── login.php             # Login page
│   ├── logout.php            # Logout handler
│   └── student/
│       └── StudentDashboard.php  # Student dashboard
└── ...
```

## Notes

- Make sure PHP session support is enabled
- The application uses prepared statements to prevent SQL injection
- Passwords are hashed using PHP's `password_hash()` function
- Default user role is 'student' for new registrations
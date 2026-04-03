# WIET Library Management System

Production-grade, database-driven library platform for admin operations, student self-service, footfall tracking, and public OPAC search.

## Table Of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Quick Start (Windows/XAMPP)](#quick-start-windowsxampp)
- [Configuration](#configuration)
- [Running The System](#running-the-system)
- [API Endpoints](#api-endpoints)
- [Testing And Quality](#testing-and-quality)
- [Security Checklist](#security-checklist)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Overview

Core modules:

- Admin portal: catalog, members, circulation, fines, reports, events, audit activity.
- Student portal: borrowed books, profile, digital ID, recommendations, notifications.
- Footfall system: QR scanner check-in/check-out, active visitors, analytics.
- Public OPAC: search and availability for catalog discovery.
- Mobile app: React Native/Expo student companion app.

## Tech Stack

- Backend: PHP 8.x, PDO, MySQL
- Frontend: HTML, CSS, JavaScript
- Mobile: React Native, Expo, TypeScript
- Server: Apache (XAMPP on Windows)
- QR/Media: GD + QR generation libraries

## Project Structure

```text
wiet_lib/
|- admin/
|  |- api/
|  |  |- analytics.php
|  |  |- books.php
|  |  |- circulation.php
|  |  |- dashboard.php
|  |  |- members.php
|  |  `- reports.php
|  |- dashboard.php
|  |- books-management.php
|  |- circulation.php
|  `- footfall-analytics.php
|- student/
|- footfall/
|- includes/
|  |- db_connect.php
|  `- functions.php
|- database/
|  |- schema.sql
|  `- import_data.php
|- student-mobile-app/
`- opac.php
```

## Quick Start (Windows/XAMPP)

1. Install XAMPP and start Apache + MySQL.
2. Place project at `C:\xampp\htdocs\wiet_lib`.
3. Create DB and import schema:

```sql
CREATE DATABASE IF NOT EXISTS wiet_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wiet_library;
SOURCE C:/xampp/htdocs/wiet_lib/database/schema.sql;
```

4. (Optional) import sample data:

```text
http://localhost/wiet_lib/database/import_data.php
```

5. Open application:

- Admin login: `http://localhost/wiet_lib/admin/admin_login.php`
- Student login: `http://localhost/wiet_lib/student/student_login.php`
- OPAC: `http://localhost/wiet_lib/opac.php`
- Footfall scanner: `http://localhost/wiet_lib/footfall/scanner.php`

## Configuration

Edit `includes/db_connect.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wiet_library');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

Production recommendations:

- Use a dedicated DB user (not root).
- Set a strong DB password.
- Restrict DB host/network access.

## Running The System

Web app:

- Ensure Apache/MySQL are running in XAMPP.
- Browse endpoints listed above.

Mobile app:

```bash
cd student-mobile-app
npm install
npx tsc --noEmit
npm start
```

## API Endpoints

Base path: `/wiet_lib/admin/api`

Key endpoints:

- `books.php?action=list`
- `books.php?action=search&q=<query>`
- `members.php?action=list`
- `circulation.php?action=issue`
- `circulation.php?action=return`
- `dashboard.php`
- `analytics.php` (implemented and live)

Footfall APIs:

- `/wiet_lib/footfall/api/footfall-records.php`
- `/wiet_lib/footfall/api/footfall-stats.php`
- `/wiet_lib/footfall/api/checkin.php`

Example:

```bash
curl "http://localhost/wiet_lib/admin/api/analytics.php"
```

Expected response shape:

```json
{
  "success": true,
  "message": "Analytics fetched successfully",
  "data": {
    "summary": {},
    "footfall_trend": [],
    "circulation_trend": [],
    "top_books": []
  }
}
```

## Testing And Quality

Comprehensive testing assets are in `Automated-Test/`.

Run PHP E2E suites:

```bash
cd Automated-Test
C:\xampp\php\php.exe advanced-e2e-tests.php
C:\xampp\php\php.exe e2e-test-runner.php
```

Run mobile checks:

```bash
cd student-mobile-app
npx tsc --noEmit
```

Reference reports:

- `Automated-Test/COMPREHENSIVE_SYSTEM_TESTING_FINAL_REPORT.md`
- `Automated-Test/MASTER_E2E_TEST_REPORT.md`
- `Automated-Test/E2E_TESTING_EXECUTION_SUMMARY.md`

## Security Checklist

Before production:

- Change default admin credentials.
- Enforce HTTPS.
- Disable verbose error display.
- Rotate DB credentials and avoid root.
- Restrict file permissions.
- Back up DB regularly.

PHP production flags:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

## Troubleshooting

Database connection failure:

- Confirm MySQL is running.
- Verify DB credentials in `includes/db_connect.php`.
- Confirm database `wiet_library` exists.

404 on API:

- Confirm endpoint file exists in `admin/api/`.
- Verify URL path includes `/wiet_lib/`.
- Check Apache virtual host/document root.

No data in lists:

- Run `database/import_data.php`.
- Check DB tables and records.

Apache port conflicts:

- Change Apache listen port (for example 8080) and use `http://localhost:8080/wiet_lib`.

## License

Educational project for WIET College Library.

---

Last Updated: April 3, 2026
Version: 2.0.0
Status: Production Ready

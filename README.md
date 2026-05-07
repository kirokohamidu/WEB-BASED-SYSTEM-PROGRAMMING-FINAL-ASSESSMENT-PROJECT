# Uganda Martyrs University Event Management System

A web-based event management application built for Uganda Martyrs University (UMU) to help students and administrators discover, create, manage, and RSVP to university events.

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Installation & Setup](#installation--setup)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Usage](#usage)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

The **UMU Event Management System** is a full-stack PHP web application that allows:

- **Students** to browse upcoming events, view event details, and RSVP to events they wish to attend.
- **Administrators** to create, edit, delete, and manage events, view RSVP statistics, and monitor platform activity.

The system features a responsive design with a branded UMU color scheme (Navy, Gold, and Red).

---

## Features

### For Students
- User registration and login
- Browse all upcoming and past events
- View detailed event information (date, time, location, description)
- RSVP to events with a single click
- View "My RSVPs" dashboard to track registered events
- Cancel RSVPs if needed

### For Administrators
- Secure admin dashboard
- Create new events with image uploads
- Edit existing events
- Delete events
- View all RSVPs per event
- Manage event categories
- View platform statistics (total events, RSVPs, students)

### General
- Responsive design (mobile-friendly)
- Event image support (uploads or external URLs)
- Event category tagging
- Pagination for event listings
- Search and filter events
- Sticky navigation with user menu
- Secure password hashing
- Session-based authentication with role-based access control

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP 8.x |
| **Database** | MySQL 8.x |
| **Server** | Apache (WAMP/XAMPP/LAMP) |
| **Styling** | Custom CSS with CSS Variables |
| **Fonts** | Google Fonts (Open Sans, Merriweather) |

---

## Project Structure

```
final_assessment_project/
│
├── admin/                      # Admin-only pages
│   ├── create_event.php        # Create new event form
│   ├── edit_event.php          # Edit existing event
│   └── events.php              # Admin event management list
│
├── config/                     # Configuration files
│   ├── config.php              # App constants & settings
│   └── database.php            # Database connection handler
│
├── css/                        # Stylesheets
│   └── style.css               # Main stylesheet (UMU branding)
│
├── database/                   # Database files
│   └── umu_event_management.sql # Full database schema & seed data
│
├── includes/                   # Reusable PHP components
│   ├── auth.php                # Authentication helpers
│   ├── footer.php              # Page footer template
│   ├── functions.php           # Utility functions
│   └── header.php              # Page header + navigation template
│
├── js/                         # JavaScript files
│   └── main.js                 # Frontend interactivity
│
├── uploads/                    # Uploaded event images
│
├── create_event.php            # (Legacy) Event creation
├── dashboard.php               # User dashboard (student/admin)
├── delete_event.php            # Event deletion handler
├── event_details.php           # Single event detail page
├── events.php                  # Public events listing
├── get_events.php              # AJAX event fetch endpoint
├── get_rsvps.php               # AJAX RSVP fetch endpoint
├── index.php                   # Homepage
├── login.php                   # User login
├── logout.php                  # Logout handler
├── my_rsvps.php                # Student's RSVP list
├── register.php                # User registration
├── rsvp.php                    # RSVP action handler
├── update_event.php            # Event update handler
│
├── fix_css.php                 # CSS debugging utility
├── TODO.md                     # Project task list
└── README.md                   # This file
```

---

## Installation & Setup

### Prerequisites

- [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/), or [MAMP](https://www.mamp.info/) installed
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Modern web browser

### Step 1: Clone or Download the Project

Place the project folder inside your web server's root directory:

```
# For WAMP
c:/wamp64/www/final_assessment_project/

# For XAMPP
c:/xampp/htdocs/final_assessment_project/
```

### Step 2: Configure the Application

Open `config/config.php` and update the base URL if needed:

```php
define('APP_URL', 'http://localhost/final_assessment_project');
```

### Step 3: Set Up the Database

See the [Database Setup](#database-setup) section below.

### Step 4: Access the Application

Open your browser and navigate to:

```
http://localhost/final_assessment_project/
```

---

## Database Setup

### Option 1: Import via phpMyAdmin

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Create a new database named: `umu_event_management`
3. Go to the **Import** tab
4. Select the file: `database/umu_event_management.sql`
5. Click **Go**

### Option 2: Import via MySQL Command Line

```bash
mysql -u root -p
CREATE DATABASE umu_event_management;
EXIT;

mysql -u root -p umu_event_management < database/umu_event_management.sql
```

### Database Configuration

Ensure your database credentials in `config/database.php` match your local MySQL setup:

```php
$host = 'localhost';
$db   = 'umu_event_management';
$user = 'root';      // Update if different
$pass = '';          // Update if you have a password
$charset = 'utf8mb4';
```

---

## Configuration

### Application Settings (`config/config.php`)

| Constant | Description |
|----------|-------------|
| `APP_NAME` | Application name displayed in titles |
| `APP_URL` | Base URL of the application |
| `DB_HOST` | Database host |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASS` | Database password |
|
---

## Usage

### Default Login Credentials

After importing the database, you can log in with the following demo accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@umu.ac.ug | admin123 |
| Student | student@stud.umu.ac.ug | student123 |

> **Note:** Change these credentials in production!

### User Roles

- **Admin**: Can create, edit, delete events and view all RSVPs.
- **Student**: Can browse events, RSVP, and view their own RSVPs.

---

## Screenshots

| Page | Description |
|------|-------------|
| Homepage | Hero banner with stats and upcoming events slider |
| Events Listing | Grid of all events with search/filter |
| Event Details | Full event info with RSVP button |
| Dashboard | Admin/student personalized dashboard |
| Login/Register | Authentication pages with UMU branding |

---

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature-name`)
3. Commit your changes (`git commit -m 'Add feature'`)
4. Push to the branch (`git push origin feature-name`)
5. Open a Pull Request

---

## License

This project is built for educational purposes as part of the Uganda Martyrs University curriculum.

---

## Acknowledgements

- Uganda Martyrs University
- Open Sans & Merriweather fonts by Google Fonts
- Icons and UI inspiration from modern event management platforms

---

## Contact

For questions or support, please contact the project developer.

**Project Status:** Active Development


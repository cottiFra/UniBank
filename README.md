# UniBank

Platform for purchasing and sharing university notes and materials.

## Description

UniBank is a web-based platform that allows students to buy, sell, and share university notes, textbooks, and other academic materials. Features include user authentication, material upload, search functionality, payment system with tokens, and admin dashboard.

## Features

- Upload and share university materials
- Purchase dispenses with UniToken currency
- Search and browse materials by faculty and subject
- User profiles and balance management
- Admin dashboard for content moderation
- Contact form for user support
- User authentication and authorization

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP or similar local server

## Installation

1. Start XAMPP services:
   - Start Apache
   - Start MySQL
   - Open http://127.0.0.1/phpmyadmin

2. Open the installer:
   - Navigate to http://localhost/install/install.html
   - Fill in the form with your database credentials
   - If using default XAMPP settings, use username `root` with no password
   - Click Install

3. Access the application:
   - You will be redirected to the login page at http://localhost/UniBank-main/

## Testing the Application

### Admin Account
Email: `admin@unibank.it`
Password: `admin123`
Password hash: `$2y$10$BD5U4OmkC5XNsdreEPHIWeY2L0Sm9Q6S9PnbEcm3lfy0UturghFum`

### Regular User Account
Email: `utente@email.it`
Password: `utente123`
Password hash: `$2y$10$TlFlxinOGhp9pdSOhh75/On.06pm7RcTi48.eMdRSRmKW9Cq8pi1C`

### Registration
Click the "Registrati" button on the login page to create a new account and test the registration flow.

### Note on Password Reset
If the database does not recognize the passwords, reset them directly in phpMyAdmin (http://127.0.0.1/phpmyadmin) using the password hashes provided above.

## Project Structure
UniBank/
├── config.php # Database configuration
├── index.php # Main entry point
├── src/ # Main application code
│ ├── admin/ # Admin dashboard
│ ├── authentication/ # Login/Signup
│ ├── contactus/ # Contact form
│ ├── profile/ # User profiles
│ ├── upload/ # Material upload
│ └── funzioniUtenti/ # User functions
├── database(sql)/ # SQL files
├── dispense/ # Uploaded materials
├── install/ # Installation setup
└── assets/ # Images and styling


## Usage

- Register a new account or login with test credentials
- Upload materials to share with other students
- Search for and purchase materials
- Access admin panel for content moderation (admin only)
- Use the contact form for support requests

## License

MIT License - See LICENSE file for details

## Author

cottiFra
@albydaddy22

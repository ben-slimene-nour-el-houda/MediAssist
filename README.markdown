# Medicament Project

## Overview
This project is a web-based application developed to manage medical-related data, including appointments, emergency contacts, medications, password resets, prescriptions, and user profiles. The application is built using PHP with MySQL as the database, running on XAMPP for local development.
 
## Project Structure
The project directory is organized as follows:
- `accueil/` - Contains the main welcome page files.
- `appointment/` - Files related to appointment management.
- `emergency/` - Files for emergency contact information.
- `medications/` - Files for managing medication details.
- `notification/` - Notification-related files.
- `prescription/` - Files for prescription management.
- `profil/` - User profile management files.
- `uploads/` - Directory for uploaded files.
- `accueil.php` - Main entry point for the welcome page.
- `session_config.php` - Configuration file for session management.
- `forget_password.php` - File for password recovery initiation.
- `reset_password.php` - File for password reset functionality.
- `login.php` - Login page.
- `loginout.php` - Logout functionality.
- `logout.php` - Logout page.
- `signup.php` - User registration page.
- `vendor/` - Directory for third-party dependencies.
- `composer.json` - Composer configuration file.
- `composer.lock` - Lock file for Composer dependencies.
- `db_connect.php` - Database connection file.
- `first.php` - Initial PHP script.
- `logo.png` - Project logo image.

## Database
The project uses a MySQL database with the following tables:
- `appointments`
- `emergency_contacts`
- `medications`
- `password_resets`
- `prescriptions`
- `users`

## How to Download and Set Up the Project

### Prerequisites
- XAMPP (with Apache, MySQL, and PHP)
- Composer
- Web browser

### Steps to Download and Install
1. **Clone the Repository**
   - Clone the project repository to your local machine using:
     ```
     git clone https://github.com/ben-slimene-nour-el-houda/MediAssist
     ```

2. **Install Dependencies**
   - Navigate to the project directory:
     ```
     cd mediassist
     ```
   - Install the required PHP dependencies using Composer:
     ```
     composer install
     ```

3. **Database Setup with XAMPP**
   - Start XAMPP and ensure the Apache and MySQL modules are running.
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`) and create a new database named `mediassist`.
   - Import the database schema by selecting the `mediassist` database and using the "Import" tab to load `database.sql` .

4. **Configure Database Connection**
   - Open `db_connect.php` and update the database credentials to match your XAMPP MySQL setup:
     - Host: `localhost`
     - Username: `root` (default XAMPP MySQL username)
     - Password: (leave empty unless you set a password)
     - Database: `mediassist`

5. **Run the Project**
   - Place the project folder inside the XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\medicament`).
   - Access the project in your browser at `http://localhost/medicament/first.php`.

## Usage
- Register a new user via `signup.php`.
- Log in using `login.php`.
- Manage appointments, medications, prescriptions, and emergency contacts through the respective sections.
- Reset password via `forget_password.php` and `reset_password.php` if needed.


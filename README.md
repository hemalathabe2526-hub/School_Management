# Student Management System

A comprehensive PHP and MySQL based system for managing student records, attendance, grades, and school administration.

## Features

### Core Features
- **Student Management**: View, add, edit, delete student records
- **User Authentication**: Login system with role-based access
- **Dashboard**: Analytics and statistics with charts
- **Attendance Tracking**: Mark and track student attendance with reports
- **Grades Management**: Record and manage student grades by subject
- **Notifications**: Send notifications to students, parents, and staff
- **Calendar**: Student birthday calendar
- **Messaging**: Internal messaging between users
- **Export**: Export student data to CSV
- **Dark Mode**: Toggle between light and dark themes

### Advanced Features
- Live student count updates
- Search functionality
- Photo uploads for students
- Medical information tracking
- Parent contact management
- Blood group and emergency contact details
- Attendance reports with charts
- Grade tracking by semester and year

  ## Live Project View

  https://school-management-blush-five.vercel.app/

## Setup

1. **Install PHP and MySQL**
   - PHP 8.3+ recommended
   - MySQL 5.7+ or MariaDB

2. **Database Setup**
   - Create a database named `school_db`
   - The system will automatically create necessary tables
   - Default login: admin / password

3. **Running the Application**
   - Use the provided `start_server.bat` or `start_server.ps1` scripts
   - Or manually run: `php -S localhost:8000` in the project directory
   - Access at http://localhost:8000

## Database Tables

The system creates the following tables automatically:
- `students`: Student information
- `users`: User accounts for authentication
- `attendance`: Daily attendance records
- `grades`: Academic grades
- `messages`: Internal messaging
- `notifications`: System notifications

## File Structure

- `index.php`: Student list and management
- `add.php`: Add new student
- `edit.php`: Edit student details
- `delete.php`: Delete student
- `dashboard.php`: Analytics dashboard
- `attendance.php`: Attendance management
- `attendance_report.php`: Attendance reports
- `grades.php`: Grades management
- `notifications.php`: Notification system
- `calendar.php`: Birthday calendar
- `messages.php`: Internal messaging
- `login.php`: User authentication
- `api.php`: REST API endpoints
- `export.php`: Data export functionality
- `db.php`: Database connection
- `styles.css`: Styling
- `script.js`: JavaScript functionality
- `start_server.bat`: Windows batch script to start server
- `start_server.ps1`: PowerShell script to start server

## Security Features

- Password hashing
- Session-based authentication
- Role-based access control
- Input sanitization
- SQL injection prevention

## Making it Run Always

To keep the server running persistently on Windows:

1. **Using Task Scheduler**
   - Open Task Scheduler
   - Create a new task
   - Set trigger to "At log on" or "At startup"
   - Set action to "Start a program"
   - Program: `C:\Windows\System32\cmd.exe`
   - Arguments: `/c "C:\MY PROJECTS\Student Management\start_server.bat"`
   - Run with highest privileges

2. **Using Windows Services (Advanced)**
   - Use tools like NSSM (Non-Sucking Service Manager)
   - Download NSSM and create a service pointing to the batch file

3. **Background Process**
   - The PHP server runs in the background when started with the scripts
   - Keep the terminal window open or minimized

For production deployment, consider using Apache/Nginx with PHP-FPM.

## Future Enhancements

- Email integration for notifications
- AI Integration
- Chat Support
- Voice Action
- SMS alerts for parents
- Advanced reporting and analytics
- Mobile app companion
- API for third-party integrations
- Backup and restore functionality

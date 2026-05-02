# Student Feedback System - Backend

A complete PHP backend for the Student Feedback System with RESTful API endpoints, user authentication, and database management.

## 🚀 Features

- **User Authentication**: Login, registration, and session management
- **Role-Based Access Control**: Student, Teacher, and Admin roles
- **Feedback Management**: Submit, view, update, and delete feedback
- **User Management**: CRUD operations for students and teachers
- **Course Management**: Manage subjects and course assignments
- **Database Integration**: MySQL database with PDO
- **Security**: Password hashing, input sanitization, and validation
- **RESTful API**: Clean and consistent API endpoints
- **CORS Support**: Cross-origin resource sharing enabled

## 📁 Project Structure

```
backend/
├── config/
│   └── database.php          # Database configuration and connection
├── database/
│   └── schema.sql            # Database schema and sample data
├── includes/
│   └── functions.php         # Utility functions and helpers
├── api/
│   ├── auth.php              # Authentication endpoints
│   ├── feedback.php          # Feedback management endpoints
│   ├── users.php             # User management endpoints
│   └── courses.php           # Course management endpoints
├── index.php                 # Main entry point and routing
└── README.md                 # This file
```

## 🛠️ Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache/Nginx with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL, JSON

## ⚙️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd student-feedback-system/backend
```

### 2. Configure Database
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'feedback_system');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Setup Database
Visit `/backend/database/setup` in your browser to automatically create the database schema and sample data.

### 4. Configure Web Server
Ensure your web server is configured to handle the backend routes properly.

## 🔌 API Endpoints

### Authentication
- `POST /api/auth.php` - Login, Register, Logout
- `GET /api/auth.php?action=check` - Check authentication status

### Feedback Management
- `GET /api/feedback.php?action=list` - Get feedback list (admin)
- `GET /api/feedback.php?action=student` - Get student feedback
- `GET /api/feedback.php?action=teacher` - Get teacher feedback
- `POST /api/feedback.php` - Submit feedback
- `PUT /api/feedback.php` - Update feedback (admin)
- `DELETE /api/feedback.php?id={id}` - Delete feedback (admin)

### User Management
- `GET /api/users.php?action=list` - Get user list (admin)
- `GET /api/users.php?action=departments` - Get departments
- `GET /api/users.php?action=profile` - Get user profile
- `POST /api/users.php` - Create user (admin)
- `PUT /api/users.php` - Update user (admin)
- `DELETE /api/users.php?id={id}&type={type}` - Delete user (admin)

### Course Management
- `GET /api/courses.php?action=list` - Get course list (admin)
- `GET /api/courses.php?action=student` - Get student courses
- `GET /api/courses.php?action=teacher` - Get teacher courses
- `GET /api/courses.php?action=departments` - Get departments
- `GET /api/courses.php?action=teachers` - Get teachers
- `POST /api/courses.php` - Create course (admin)
- `PUT /api/courses.php` - Update course (admin)
- `DELETE /api/courses.php?id={id}` - Delete course (admin)

## 🔐 Authentication

The system uses session-based authentication with JWT token support.

### Login Request
```json
{
  "action": "login",
  "email": "user@example.com",
  "password": "password123",
  "userType": "student"
}
```

### Login Response
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user_id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "user_type": "student",
    "department": "Computer Science",
    "token": "jwt_token_here"
  }
}
```

## 📊 Database Schema

### Tables
- **departments**: Department information
- **students**: Student user accounts
- **teachers**: Teacher user accounts
- **subjects**: Course/subject information
- **feedback**: Student feedback submissions

### Sample Data
The system comes with pre-loaded sample data:
- 6 departments (Computer Science, Mathematics, Physics, etc.)
- 7 teachers (including 1 admin)
- 6 students
- 6 subjects
- 6 sample feedback entries

**Default Password**: `password123` for all sample users

## 🚦 Usage Examples

### Submit Feedback (Student)
```javascript
fetch('/backend/api/feedback.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'submit',
    teacherId: 1,
    subjectId: 1,
    rating: 5,
    comment: 'Excellent teaching methods!'
  })
});
```

### Get Feedback List (Admin)
```javascript
fetch('/backend/api/feedback.php?action=list&page=1&limit=20');
```

### Create User (Admin)
```javascript
fetch('/backend/api/users.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'create',
    name: 'New Student',
    email: 'newstudent@example.com',
    password: 'password123',
    userType: 'student',
    department: 'Computer Science'
  })
});
```

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` function
- **Input Sanitization**: All user inputs are sanitized
- **SQL Injection Prevention**: Uses prepared statements with PDO
- **Session Security**: Secure session handling
- **Role-Based Access**: Different endpoints require different user roles

## 🐛 Error Handling

All API endpoints return consistent error responses:

```json
{
  "error": "Error message here"
}
```

Common HTTP status codes:
- `200`: Success
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `405`: Method Not Allowed
- `500`: Internal Server Error

## 📝 Logging

The system logs user activities for audit purposes:
- User login/logout
- Feedback submissions
- User creation/deletion
- Course management operations

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to modify:
- Database connection settings
- Connection pooling
- Error handling

### CORS Settings
Modify CORS headers in `includes/functions.php` if needed.

## 🚀 Deployment

### Production Considerations
1. Disable error reporting in production
2. Use HTTPS
3. Configure proper database credentials
4. Set up proper file permissions
5. Enable error logging instead of display

### Environment Variables
Consider using environment variables for sensitive configuration:
```php
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the API documentation
- Review the error logs

## 🔄 Updates

Check for updates regularly and ensure compatibility with your PHP version and database system.

# 🎓 Student Feedback System

> A modern, full-stack student feedback management platform built with HTML, CSS (Tailwind), JavaScript, and PHP.

<div align="center">

[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue?style=flat&logo=php)](https://www.php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-v3-06B6D4?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![Status](https://img.shields.io/badge/Status-Active-success?style=flat)](https://github.com)
[![Contributions Welcome](https://img.shields.io/badge/Contributions-Welcome-brightgreen)](CONTRIBUTING.md)

</div>

---

## ✨ Overview

The **Student Feedback System** is a comprehensive platform designed to streamline the collection, management, and analysis of student feedback. With role-based access control, intuitive dashboards, and powerful analytics, this system empowers educational institutions to make data-driven decisions and improve their academic offerings.

---

## 🎯 Key Features

- 🔐 **Role-Based Access Control** - Dedicated interfaces for Students, Teachers, and Administrators
- 📊 **Interactive Dashboards** - Real-time feedback analytics and visualization
- 💾 **Robust Database Backend** - Secure data persistence with PHP/MySQL
- 🎨 **Modern UI/UX** - Responsive design with Tailwind CSS
- 📱 **Mobile Responsive** - Seamless experience across all devices
- ⚡ **Fast & Performant** - Optimized for speed and reliability
- 🔄 **Easy Integration** - Well-documented APIs for extensibility
- 📈 **Advanced Reporting** - Export and analyze feedback data

---

## 🛠️ Tech Stack

<table>
<tr>
<td><strong>Frontend</strong></td>
<td>HTML5, JavaScript (ES6+), Tailwind CSS</td>
</tr>
<tr>
<td><strong>Backend</strong></td>
<td>PHP 7.4+</td>
</tr>
<tr>
<td><strong>Database</strong></td>
<td>MySQL/MariaDB</td>
</tr>
<tr>
<td><strong>Server</strong></td>
<td>Apache (XAMPP)</td>
</tr>
</table>

---

## 📁 Project Structure

```
WD Master Project/
├── 📄 README.md                          # Project documentation
├── 
├── frontend/                             # Client-side application
│   ├── index.html                        # Landing page
│   ├── 
│   ├── components/
│   │   └── navbar.html                   # Navigation component
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   └── styles.css                # Custom Tailwind styles
│   │   ├── js/
│   │   │   ├── api-config.js             # API configuration & calls
│   │   │   └── scripts.js                # Utility functions
│   │   └── img/                          # Images & media
│   │
│   └── pages/
│       ├── login.html                    # User login page
│       ├── register.html                 # User registration page
│       │
│       ├── admin/                        # Admin dashboard
│       │   ├── dashboard.php             # Admin overview
│       │   ├── user_management.php       # User management interface
│       │   ├── course_management.php     # Course management
│       │   ├── feedback_overview.php     # Feedback analytics
│       │   ├── delete_feedback.php       # Delete feedback handler
│       │   ├── export_courses.php        # Export course data
│       │   ├── get_course_details.php    # Course details API
│       │   ├── get_feedback_details.php  # Feedback details API
│       │   ├── get_user_details.php      # User details API
│       │   └── test_database.php         # Database testing utility
│       │
│       ├── student/                      # Student interface
│       │   ├── dashboard.php             # Student dashboard
│       │   ├── feedback_form.php         # Feedback submission form
│       │   ├── history.php               # Feedback history
│       │   └── submit_feedback.php       # Feedback submission handler
│       │
│       └── teacher/                      # Teacher interface
│           ├── dashboard.php             # Teacher overview
│           ├── feedback_list.php         # Feedback management
│           └── get_feedback_details.php  # Feedback details API
│
└── backend/                              # Server-side logic
    ├── database.php                      # Database connection
    ├── database_schema.sql               # Database structure
    ├── create_admin_user.sql             # Admin user setup
    ├── login.php                         # Authentication handler
    ├── logout.php                        # Session termination
    ├── register.php                      # User registration handler
    ├── error_message.php                 # Error handling
    └── success.php                       # Success responses
```

---

## 🚀 Installation

### Prerequisites

- **XAMPP** (Apache, MySQL, PHP) or similar local server environment
- **PHP 7.4+** installed and configured
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)
- **Git** (optional, for version control)

### Setup Steps

1. **Clone or Download the Repository**
   ```bash
   git clone https://github.com/rudratripathi289/studentfeedbacksystem.git
   cd "studentfeedbacksystem"
   ```

2. **Place in Web Root**
   ```bash
   # For XAMPP on Windows
   Copy the project to: C:\xampp\htdocs\studentfeedbacksystem
   ```

3. **Setup Database**
   ```bash
   # Open phpMyAdmin (http://localhost/phpmyadmin)
   # Create a new database: feedback_system
   # Import the schema: backend/database_schema.sql
   # Run admin setup: backend/create_admin_user.sql
   ```

4. **Configure Database Connection**
   - Edit `backend/database.php`
   - Update database credentials (host, user, password)
   - Ensure database name matches your setup

5. **Start Server**
   ```bash
   # Start XAMPP (Apache & MySQL)
   # Access the application at: http://localhost/studentfeedbacksystem
   ```

---

## 📖 Usage Guide

### Accessing the System

1. **Landing Page**: Navigate to `http://localhost/studentfeedbacksystem`
2. **Login**: Use demo credentials or create a new account
3. **Role Selection**: System automatically routes based on user role

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| 👨‍🎓 Student | `john.doe@university.edu` | `password123` |
| 👨‍🏫 Teacher | `chen.wei@university.edu` | `password123` |
| 👨‍💼 Admin | `admin@university.edu` | `password123` |

### User Roles & Capabilities

#### 👨‍🎓 Student Dashboard
- Submit feedback for courses
- View feedback history
- Track submission status
- Download feedback confirmations

#### 👨‍🏫 Teacher Dashboard
- View student feedback
- Analyze feedback statistics
- Generate performance reports
- Respond to student feedback

#### 👨‍💼 Admin Dashboard
- Manage users (Create, Edit, Delete)
- Manage courses and subjects
- View comprehensive feedback analytics
- Export data and reports
- System configuration

---

## 🔧 API Endpoints

### Authentication
```
POST   /backend/login.php          - User login
POST   /backend/register.php       - User registration
GET    /backend/logout.php         - User logout
```

### Feedback Management
```
POST   /frontend/pages/student/submit_feedback.php     - Submit feedback
GET    /frontend/pages/student/history.php             - Get feedback history
GET    /frontend/pages/admin/get_feedback_details.php  - Get feedback details
DELETE /frontend/pages/admin/delete_feedback.php       - Delete feedback
```

### User Management
```
GET    /frontend/pages/admin/get_user_details.php      - Get user information
POST   /frontend/pages/admin/user_management.php       - Manage users
```

### Course Management
```
POST   /frontend/pages/admin/course_management.php     - Manage courses
GET    /frontend/pages/admin/get_course_details.php    - Get course details
GET    /frontend/pages/admin/export_courses.php        - Export courses
```

---

## 🎨 Customization

### Styling
- Main styles: [frontend/assets/css/styles.css](frontend/assets/css/styles.css)
- Uses **Tailwind CSS** for utility-based styling
- Easy to customize colors, fonts, and layouts

### Configuration
- Database settings: [backend/database.php](backend/database.php)
- API endpoints: [frontend/assets/js/api-config.js](frontend/assets/js/api-config.js)
- Global functions: [frontend/assets/js/scripts.js](frontend/assets/js/scripts.js)

---

## 🔒 Security Features

- ✅ Password hashing with PHP's `password_hash()`
- ✅ SQL prepared statements to prevent injection
- ✅ Session management and timeout controls
- ✅ CSRF token validation
- ✅ Input validation and sanitization
- ✅ Role-based access control (RBAC)

> **⚠️ Note**: For production deployment, implement additional security measures such as HTTPS, rate limiting, and WAF.

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection fails | Check credentials in `backend/database.php` |
| Pages show blank | Verify PHP is enabled and MySQL is running |
| Styles not loading | Clear browser cache (Ctrl+Shift+Delete) |
| Session issues | Check PHP `session.save_path` permissions |
| CORS errors | Ensure requests are from same domain |

For more help, check the browser console (F12) for error messages.

---

## 📊 Database Schema

Key tables:
- **users** - User accounts and authentication
- **courses** - Course information
- **feedback** - Student feedback submissions
- **submissions** - Feedback submission records

Run `backend/database_schema.sql` to setup all tables automatically.

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### Contribution Guidelines
- Follow PSR-12 coding standards for PHP
- Use meaningful commit messages
- Test all changes before submitting
- Update documentation as needed

---

## 📝 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 🗺️ Roadmap

- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Email notifications
- [ ] Multi-language support
- [ ] API documentation (OpenAPI/Swagger)
- [ ] Docker containerization
- [ ] CI/CD pipeline
- [ ] Unit & integration tests

---

## ⭐ Show Your Support

If this project helped you, please consider:
- ⭐ Starring the repository
- 🍴 Forking the project
- 💬 Sharing your feedback
- 🤝 Contributing to the project

---

<div align="center">

[⬆ Back to top](#-student-feedback-system)

</div>

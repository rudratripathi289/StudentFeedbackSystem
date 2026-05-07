# Student Feedback System - Frontend Only

This is a **frontend-only version** of the Student Feedback System that provides a complete user interface without requiring a backend server.

## 🚀 Features

- **Complete User Interface**: All pages and functionality are fully functional
- **Mock Data System**: Simulates backend functionality with local data
- **No Backend Required**: Works entirely in the browser
- **Responsive Design**: Modern UI built with Tailwind CSS
- **Role-Based Access**: Student, Teacher, and Admin interfaces

## 📁 Project Structure

```
frontend/
├── assets/
│   ├── css/
│   │   └── styles.css          # Custom styles
│   ├── js/
│   │   ├── api-config.js       # Mock API system
│   │   └── scripts.js          # Utility functions
│   └── img/                    # Images
├── components/
│   └── navbar.html             # Navigation component
├── pages/
│   ├── admin/                  # Admin interface
│   ├── student/                # Student interface
│   ├── teacher/                # Teacher interface
│   ├── login.html              # Login page
│   └── register.html           # Registration page
└── index.html                  # Landing page
```

## 🛠️ Requirements

- **Web Browser**: Modern browser with JavaScript enabled
- **No Server Required**: Works entirely client-side
- **No Database**: All data is stored in browser memory

## 🚀 Getting Started

1. **Clone or Download** the project files
2. **Open** `frontend/index.html` in your web browser
3. **Start Using** the system immediately!

## 🔐 Demo Credentials

The system comes with pre-configured demo accounts:

- **Student**: `john.doe@university.edu` / `password123`
- **Teacher**: `chen.wei@university.edu` / `password123`
- **Admin**: `admin@university.edu` / `password123`

## 💾 Data Persistence

- **Session Data**: User login state persists during browser session
- **Mock Data**: Sample data is loaded when the page loads
- **Local Storage**: Some user preferences may be saved locally

## 🔧 How It Works

### Mock API System
The `api-config.js` file provides mock implementations of all backend functions:

- **Authentication**: Login, logout, user verification
- **Feedback Management**: Submit, view, and manage feedback
- **User Management**: Create, update, and delete users
- **Course Management**: Manage courses and subjects

### Data Flow
1. User interacts with the interface
2. Mock API functions simulate backend responses
3. Data is processed and displayed
4. No actual network requests are made

## 🎯 Use Cases

- **Prototyping**: Test UI/UX before backend development
- **Demo Purposes**: Show stakeholders the complete system
- **Learning**: Understand the frontend architecture
- **Offline Development**: Work without server setup

## 🚀 Adding Backend Later

When you're ready to add a backend:

1. **Replace** mock API calls with real HTTP requests
2. **Update** `api-config.js` to point to real endpoints
3. **Implement** server-side logic and database
4. **Test** the integration

## 🔒 Security Note

This is a **demo/prototype system**. In production:

- Implement proper authentication
- Add server-side validation
- Use secure data storage
- Enable HTTPS
- Add rate limiting

## 📱 Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test in multiple browsers
5. Submit a pull request

## 🆘 Support

For questions or issues:
- Check the browser console for errors
- Verify JavaScript is enabled
- Ensure all files are properly loaded
- Test in a different browser

---



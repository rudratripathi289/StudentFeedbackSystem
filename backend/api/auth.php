<?php
/**
 * Authentication API
 * Student Feedback System Backend
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Set CORS headers
setCorsHeaders();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'login':
                    handleLogin($input);
                    break;
                case 'register':
                    handleRegister($input);
                    break;
                case 'logout':
                    handleLogout();
                    break;
                default:
                    sendErrorResponse('Invalid action');
            }
        } else {
            sendErrorResponse('Action not specified');
        }
        break;
        
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            checkAuthStatus();
        } else {
            sendErrorResponse('Invalid action');
        }
        break;
        
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Handle user login
 */
function handleLogin($data) {
    validateRequiredFields($data, ['email', 'password', 'userType']);
    
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $userType = sanitizeInput($data['userType']);
    
    if (!validateEmail($email)) {
        sendErrorResponse('Invalid email format');
    }
    
    try {
        $db = Database::getInstance();
        
        // Check if user exists based on user type
        switch ($userType) {
            case 'student':
                $sql = "SELECT s.*, d.dept_name FROM students s 
                        JOIN departments d ON s.department_id = d.department_id 
                        WHERE s.email = ? AND s.status = 'active'";
                break;
            case 'teacher':
                $sql = "SELECT t.*, d.dept_name FROM teachers t 
                        JOIN departments d ON t.department_id = d.department_id 
                        WHERE t.email = ? AND t.status = 'active'";
                break;
            case 'admin':
                $sql = "SELECT t.*, d.dept_name FROM teachers t 
                        JOIN departments d ON t.department_id = d.department_id 
                        WHERE t.email = ? AND t.role = 'admin' AND t.status = 'active'";
                break;
            default:
                sendErrorResponse('Invalid user type');
        }
        
        $stmt = $db->query($sql, [$email]);
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user || !verifyPassword($password, $user['password'])) {
            sendErrorResponse('Invalid email or password');
        }
        
        // Set session data
        $_SESSION['user_id'] = $user['student_id'] ?? $user['teacher_id'];
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['department'] = $user['dept_name'];
        
        // Generate token
        $token = generateToken($_SESSION['user_id'], $userType, $user['email']);
        
        // Prepare user data for response
        $userData = [
            'user_id' => $_SESSION['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'user_type' => $userType,
            'department' => $user['dept_name'],
            'token' => $token
        ];
        
        if ($userType === 'teacher') {
            $userData['role'] = $user['role'];
        }
        
        logActivity($_SESSION['user_id'], 'login', "User logged in successfully");
        sendSuccessResponse($userData, 'Login successful');
        
    } catch (Exception $e) {
        sendErrorResponse('Login failed: ' . $e->getMessage());
    }
}

/**
 * Handle user registration
 */
function handleRegister($data) {
    validateRequiredFields($data, ['name', 'email', 'password', 'confirmPassword', 'userType']);
    
    $name = sanitizeInput($data['name']);
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $confirmPassword = $data['confirmPassword'];
    $userType = sanitizeInput($data['userType']);
    $department = sanitizeInput($data['department'] ?? '');
    
    // Validate email
    if (!validateEmail($email)) {
        sendErrorResponse('Invalid email format');
    }
    
    // Validate password
    if (strlen($password) < 6) {
        sendErrorResponse('Password must be at least 6 characters long');
    }
    
    if ($password !== $confirmPassword) {
        sendErrorResponse('Passwords do not match');
    }
    
    // Validate department for non-admin users
    if ($userType !== 'admin' && empty($department)) {
        sendErrorResponse('Department is required');
    }
    
    try {
        $db = Database::getInstance();
        
        // Check if email already exists
        $checkSql = "SELECT email FROM students WHERE email = ? 
                     UNION SELECT email FROM teachers WHERE email = ?";
        $checkStmt = $db->query($checkSql, [$email, $email]);
        
        if ($checkStmt->get_result()->fetch_assoc()) {
            sendErrorResponse('Email already registered');
        }
        
        // Get department ID
        $deptStmt = $db->query("SELECT department_id FROM departments WHERE dept_name = ?", [$department]);
        $deptResult = $deptStmt->get_result()->fetch_assoc();
        
        if (!$deptResult) {
            sendErrorResponse('Invalid department');
        }
        
        $departmentId = $deptResult['department_id'];
        $hashedPassword = hashPassword($password);
        
        // Insert user based on type
        switch ($userType) {
            case 'student':
                $sql = "INSERT INTO students (name, email, password, department_id) VALUES (?, ?, ?, ?)";
                $db->query($sql, [$name, $email, $hashedPassword, $departmentId]);
                $userId = $db->lastInsertId();
                break;
                
            case 'teacher':
                $sql = "INSERT INTO teachers (name, email, password, department_id, role) VALUES (?, ?, ?, ?, 'teacher')";
                $db->query($sql, [$name, $email, $hashedPassword, $departmentId]);
                $userId = $db->lastInsertId();
                break;
                
            case 'admin':
                $sql = "INSERT INTO teachers (name, email, password, department_id, role) VALUES (?, ?, ?, ?, 'admin')";
                $db->query($sql, [$name, $email, $hashedPassword, $departmentId]);
                $userId = $db->lastInsertId();
                break;
                
            default:
                sendErrorResponse('Invalid user type');
        }
        
        logActivity($userId, 'register', "New user registered: $userType");
        sendSuccessResponse(['user_id' => $userId], 'Registration successful');
        
    } catch (Exception $e) {
        sendErrorResponse('Registration failed: ' . $e->getMessage());
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    if (isLoggedIn()) {
        logActivity(getCurrentUserId(), 'logout', 'User logged out');
    }
    
    logout();
    sendSuccessResponse(null, 'Logout successful');
}

/**
 * Check authentication status
 */
function checkAuthStatus() {
    if (isLoggedIn()) {
        $userData = [
            'user_id' => getCurrentUserId(),
            'user_type' => getCurrentUserType(),
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? '',
            'department' => $_SESSION['department'] ?? ''
        ];
        
        sendSuccessResponse($userData, 'User is authenticated');
    } else {
        sendErrorResponse('User not authenticated', 401);
    }
}
?>

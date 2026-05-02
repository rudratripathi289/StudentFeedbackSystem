<?php
/**
 * User Management API
 * Student Feedback System Backend
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Set CORS headers
setCorsHeaders();

// Check authentication
if (!isLoggedIn()) {
    sendErrorResponse('Authentication required', 401);
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'list':
                    getUserList();
                    break;
                case 'departments':
                    getDepartments();
                    break;
                case 'profile':
                    getUserProfile();
                    break;
                default:
                    sendErrorResponse('Invalid action');
            }
        } else {
            sendErrorResponse('Action not specified');
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'create':
                    createUser($input);
                    break;
                case 'update':
                    updateUser($input);
                    break;
                default:
                    sendErrorResponse('Invalid action');
            }
        } else {
            sendErrorResponse('Action not specified');
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action']) && $input['action'] === 'update') {
            updateUser($input);
        } else {
            sendErrorResponse('Invalid action');
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id']) && isset($_GET['type'])) {
            deleteUser($_GET['id'], $_GET['type']);
        } else {
            sendErrorResponse('User ID and type required');
        }
        break;
        
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Get user list (admin only)
 */
function getUserList() {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        
        // Get query parameters
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $userType = isset($_GET['userType']) ? sanitizeInput($_GET['userType']) : '';
        $department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        if (!empty($userType)) {
            $whereConditions[] = "u.user_type = ?";
            $params[] = $userType;
        }
        
        if (!empty($department)) {
            $whereConditions[] = "d.dept_name = ?";
            $params[] = $department;
        }
        
        if (!empty($status)) {
            $whereConditions[] = "u.status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM (
                        SELECT s.student_id, s.name, s.email, 'student' as user_type, s.status, d.dept_name
                        FROM students s 
                        JOIN departments d ON s.department_id = d.department_id
                        UNION ALL
                        SELECT t.teacher_id, t.name, t.email, t.role as user_type, t.status, d.dept_name
                        FROM teachers t 
                        JOIN departments d ON t.department_id = d.department_id
                     ) u $whereClause";
        
        $countStmt = $db->query($countSql, $params);
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        
        // Get pagination info
        $pagination = getPaginationInfo($page, $limit, $total);
        
        // Get user data
        $sql = "SELECT u.*, d.dept_name FROM (
                    SELECT s.student_id as user_id, s.name, s.email, 'student' as user_type, s.status, s.department_id, s.created_at
                    FROM students s
                    UNION ALL
                    SELECT t.teacher_id as user_id, t.name, t.email, t.role as user_type, t.status, t.department_id, t.created_at
                    FROM teachers t
                 ) u 
                 JOIN departments d ON u.department_id = d.department_id 
                 $whereClause 
                 ORDER BY u.created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $params[] = $pagination['limit'];
        $params[] = $pagination['offset'];
        
        $stmt = $db->query($sql, $params);
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format user data
        foreach ($users as &$user) {
            $user['formatted_date'] = formatDate($user['created_at']);
            $user['status_label'] = ucfirst($user['status']);
            $user['user_type_label'] = ucfirst($user['user_type']);
        }
        
        $response = [
            'users' => $users,
            'pagination' => $pagination
        ];
        
        sendSuccessResponse($response, 'User list retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve user list: ' . $e->getMessage());
    }
}

/**
 * Get departments list
 */
function getDepartments() {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT department_id, dept_name FROM departments ORDER BY dept_name";
        $stmt = $db->query($sql);
        $departments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        sendSuccessResponse($departments, 'Departments retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve departments: ' . $e->getMessage());
    }
}

/**
 * Get user profile
 */
function getUserProfile() {
    try {
        $db = Database::getInstance();
        $userId = getCurrentUserId();
        $userType = getCurrentUserType();
        
        if ($userType === 'student') {
            $sql = "SELECT s.*, d.dept_name FROM students s 
                    JOIN departments d ON s.department_id = d.department_id 
                    WHERE s.student_id = ?";
        } else {
            $sql = "SELECT t.*, d.dept_name FROM teachers t 
                    JOIN departments d ON t.department_id = d.department_id 
                    WHERE t.teacher_id = ?";
        }
        
        $stmt = $db->query($sql, [$userId]);
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            sendErrorResponse('User not found');
        }
        
        // Remove sensitive data
        unset($user['password']);
        
        sendSuccessResponse($user, 'User profile retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve user profile: ' . $e->getMessage());
    }
}

/**
 * Create new user (admin only)
 */
function createUser($data) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    validateRequiredFields($data, ['name', 'email', 'password', 'userType', 'department']);
    
    $name = sanitizeInput($data['name']);
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $userType = sanitizeInput($data['userType']);
    $department = sanitizeInput($data['department']);
    $status = sanitizeInput($data['status'] ?? 'active');
    
    // Validate email
    if (!validateEmail($email)) {
        sendErrorResponse('Invalid email format');
    }
    
    // Validate password
    if (strlen($password) < 6) {
        sendErrorResponse('Password must be at least 6 characters long');
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
                $sql = "INSERT INTO students (name, email, password, department_id, status) VALUES (?, ?, ?, ?, ?)";
                $db->query($sql, [$name, $email, $hashedPassword, $departmentId, $status]);
                $userId = $db->lastInsertId();
                break;
                
            case 'teacher':
                $role = sanitizeInput($data['role'] ?? 'teacher');
                $sql = "INSERT INTO teachers (name, email, password, department_id, role, status) VALUES (?, ?, ?, ?, ?, ?)";
                $db->query($sql, [$name, $email, $hashedPassword, $departmentId, $role, $status]);
                $userId = $db->lastInsertId();
                break;
                
            default:
                sendErrorResponse('Invalid user type');
        }
        
        logActivity(getCurrentUserId(), 'create_user', "Created $userType user ID: $userId");
        sendSuccessResponse(['user_id' => $userId], 'User created successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to create user: ' . $e->getMessage());
    }
}

/**
 * Update user (admin only)
 */
function updateUser($data) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    validateRequiredFields($data, ['userId', 'userType']);
    
    $userId = intval($data['userId']);
    $userType = sanitizeInput($data['userType']);
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $department = sanitizeInput($data['department'] ?? '');
    $status = sanitizeInput($data['status'] ?? '');
    
    try {
        $db = Database::getInstance();
        
        // Validate department if provided
        $departmentId = null;
        if (!empty($department)) {
            $deptStmt = $db->query("SELECT department_id FROM departments WHERE dept_name = ?", [$department]);
            $deptResult = $deptStmt->get_result()->fetch_assoc();
            
            if (!$deptResult) {
                sendErrorResponse('Invalid department');
            }
            $departmentId = $deptResult['department_id'];
        }
        
        // Update user based on type
        switch ($userType) {
            case 'student':
                $updateFields = [];
                $params = [];
                
                if (!empty($name)) {
                    $updateFields[] = "name = ?";
                    $params[] = $name;
                }
                
                if (!empty($email)) {
                    $updateFields[] = "email = ?";
                    $params[] = $email;
                }
                
                if ($departmentId !== null) {
                    $updateFields[] = "department_id = ?";
                    $params[] = $departmentId;
                }
                
                if (!empty($status)) {
                    $updateFields[] = "status = ?";
                    $params[] = $status;
                }
                
                if (empty($updateFields)) {
                    sendErrorResponse('No fields to update');
                }
                
                $params[] = $userId;
                $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE student_id = ?";
                break;
                
            case 'teacher':
                $updateFields = [];
                $params = [];
                
                if (!empty($name)) {
                    $updateFields[] = "name = ?";
                    $params[] = $name;
                }
                
                if (!empty($email)) {
                    $updateFields[] = "email = ?";
                    $params[] = $email;
                }
                
                if ($departmentId !== null) {
                    $updateFields[] = "department_id = ?";
                    $params[] = $departmentId;
                }
                
                if (!empty($status)) {
                    $updateFields[] = "status = ?";
                    $params[] = $status;
                }
                
                if (isset($data['role'])) {
                    $updateFields[] = "role = ?";
                    $params[] = sanitizeInput($data['role']);
                }
                
                if (empty($updateFields)) {
                    sendErrorResponse('No fields to update');
                }
                
                $params[] = $userId;
                $sql = "UPDATE teachers SET " . implode(', ', $updateFields) . " WHERE teacher_id = ?";
                break;
                
            default:
                sendErrorResponse('Invalid user type');
        }
        
        $db->query($sql, $params);
        
        logActivity(getCurrentUserId(), 'update_user', "Updated $userType user ID: $userId");
        sendSuccessResponse(null, 'User updated successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to update user: ' . $e->getMessage());
    }
}

/**
 * Delete user (admin only)
 */
function deleteUser($userId, $userType) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    $userId = intval($userId);
    $userType = sanitizeInput($userType);
    
    try {
        $db = Database::getInstance();
        
        // Check if user has associated feedback
        if ($userType === 'student') {
            $checkSql = "SELECT COUNT(*) as count FROM feedback WHERE student_id = ?";
        } else {
            $checkSql = "SELECT COUNT(*) as count FROM feedback WHERE teacher_id = ?";
        }
        
        $checkStmt = $db->query($checkSql, [$userId]);
        $feedbackCount = $checkStmt->get_result()->fetch_assoc()['count'];
        
        if ($feedbackCount > 0) {
            sendErrorResponse('Cannot delete user with associated feedback. Consider deactivating instead.');
        }
        
        // Delete user
        if ($userType === 'student') {
            $sql = "DELETE FROM students WHERE student_id = ?";
        } else {
            $sql = "DELETE FROM teachers WHERE teacher_id = ?";
        }
        
        $db->query($sql, [$userId]);
        
        logActivity(getCurrentUserId(), 'delete_user', "Deleted $userType user ID: $userId");
        sendSuccessResponse(null, 'User deleted successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to delete user: ' . $e->getMessage());
    }
}
?>

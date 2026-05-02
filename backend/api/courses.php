<?php
/**
 * Course Management API
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
                    getCourseList();
                    break;
                case 'student':
                    getStudentCourses();
                    break;
                case 'teacher':
                    getTeacherCourses();
                    break;
                case 'departments':
                    getDepartments();
                    break;
                case 'teachers':
                    getTeachers();
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
                    createCourse($input);
                    break;
                case 'update':
                    updateCourse($input);
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
            updateCourse($input);
        } else {
            sendErrorResponse('Invalid action');
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteCourse($_GET['id']);
        } else {
            sendErrorResponse('Course ID required');
        }
        break;
        
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Get course list (admin only)
 */
function getCourseList() {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        
        // Get query parameters
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
        $department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : '';
        $teacher = isset($_GET['teacher']) ? sanitizeInput($_GET['teacher']) : '';
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(sub.subject_name LIKE ? OR t.name LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        if (!empty($department)) {
            $whereConditions[] = "d.dept_name = ?";
            $params[] = $department;
        }
        
        if (!empty($teacher)) {
            $whereConditions[] = "t.name = ?";
            $params[] = $teacher;
        }
        
        if (!empty($status)) {
            $whereConditions[] = "sub.status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM subjects sub 
                     JOIN teachers t ON sub.teacher_id = t.teacher_id 
                     JOIN departments d ON sub.department_id = d.department_id 
                     $whereClause";
        
        $countStmt = $db->query($countSql, $params);
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        
        // Get pagination info
        $pagination = getPaginationInfo($page, $limit, $total);
        
        // Get course data
        $sql = "SELECT sub.*, t.name as teacher_name, d.dept_name, 
                       (SELECT COUNT(*) FROM feedback f WHERE f.subject_id = sub.subject_id) as feedback_count
                FROM subjects sub 
                JOIN teachers t ON sub.teacher_id = t.teacher_id 
                JOIN departments d ON sub.department_id = d.department_id 
                $whereClause 
                ORDER BY sub.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $pagination['limit'];
        $params[] = $pagination['offset'];
        
        $stmt = $db->query($sql, $params);
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format course data
        foreach ($courses as &$course) {
            $course['formatted_date'] = formatDate($course['created_at']);
            $course['status_label'] = ucfirst($course['status']);
        }
        
        $response = [
            'courses' => $courses,
            'pagination' => $pagination
        ];
        
        sendSuccessResponse($response, 'Course list retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve course list: ' . $e->getMessage());
    }
}

/**
 * Get student's courses
 */
function getStudentCourses() {
    if (!isStudent()) {
        sendErrorResponse('Student access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        $studentId = getCurrentUserId();
        
        $sql = "SELECT sub.*, t.name as teacher_name, d.dept_name,
                       (SELECT COUNT(*) FROM feedback f WHERE f.subject_id = sub.subject_id AND f.student_id = ?) as has_feedback
                FROM subjects sub 
                JOIN teachers t ON sub.teacher_id = t.teacher_id 
                JOIN departments d ON sub.department_id = d.department_id 
                WHERE sub.status = 'active' 
                ORDER BY sub.subject_name";
        
        $stmt = $db->query($sql, [$studentId]);
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        sendSuccessResponse($courses, 'Student courses retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve student courses: ' . $e->getMessage());
    }
}

/**
 * Get teacher's courses
 */
function getTeacherCourses() {
    if (!isTeacher()) {
        sendErrorResponse('Teacher access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        $teacherId = getCurrentUserId();
        
        $sql = "SELECT sub.*, d.dept_name,
                       (SELECT COUNT(*) FROM feedback f WHERE f.subject_id = sub.subject_id) as feedback_count
                FROM subjects sub 
                JOIN departments d ON sub.department_id = d.department_id 
                WHERE sub.teacher_id = ? AND sub.status = 'active' 
                ORDER BY sub.subject_name";
        
        $stmt = $db->query($sql, [$teacherId]);
        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        sendSuccessResponse($courses, 'Teacher courses retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve teacher courses: ' . $e->getMessage());
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
 * Get teachers list
 */
function getTeachers() {
    try {
        $db = Database::getInstance();
        
        $department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : '';
        
        if (!empty($department)) {
            $sql = "SELECT t.teacher_id, t.name, t.email, d.dept_name 
                    FROM teachers t 
                    JOIN departments d ON t.department_id = d.department_id 
                    WHERE t.status = 'active' AND d.dept_name = ? 
                    ORDER BY t.name";
            $stmt = $db->query($sql, [$department]);
        } else {
            $sql = "SELECT t.teacher_id, t.name, t.email, d.dept_name 
                    FROM teachers t 
                    JOIN departments d ON t.department_id = d.department_id 
                    WHERE t.status = 'active' 
                    ORDER BY t.name";
            $stmt = $db->query($sql);
        }
        
        $teachers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        sendSuccessResponse($teachers, 'Teachers retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve teachers: ' . $e->getMessage());
    }
}

/**
 * Create new course (admin only)
 */
function createCourse($data) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    validateRequiredFields($data, ['subjectName', 'teacherId', 'department']);
    
    $subjectName = sanitizeInput($data['subjectName']);
    $teacherId = intval($data['teacherId']);
    $department = sanitizeInput($data['department']);
    $status = sanitizeInput($data['status'] ?? 'active');
    
    try {
        $db = Database::getInstance();
        
        // Get department ID
        $deptStmt = $db->query("SELECT department_id FROM departments WHERE dept_name = ?", [$department]);
        $deptResult = $deptStmt->get_result()->fetch_assoc();
        
        if (!$deptResult) {
            sendErrorResponse('Invalid department');
        }
        
        $departmentId = $deptResult['department_id'];
        
        // Validate teacher
        $teacherStmt = $db->query("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND status = 'active'", [$teacherId]);
        if (!$teacherStmt->get_result()->fetch_assoc()) {
            sendErrorResponse('Invalid teacher');
        }
        
        // Check if course already exists
        $checkSql = "SELECT subject_id FROM subjects WHERE subject_name = ? AND department_id = ?";
        $checkStmt = $db->query($checkSql, [$subjectName, $departmentId]);
        
        if ($checkStmt->get_result()->fetch_assoc()) {
            sendErrorResponse('Course already exists in this department');
        }
        
        // Insert course
        $sql = "INSERT INTO subjects (subject_name, teacher_id, department_id, status) VALUES (?, ?, ?, ?)";
        $db->query($sql, [$subjectName, $teacherId, $departmentId, $status]);
        $courseId = $db->lastInsertId();
        
        logActivity(getCurrentUserId(), 'create_course', "Created course ID: $courseId");
        sendSuccessResponse(['course_id' => $courseId], 'Course created successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to create course: ' . $e->getMessage());
    }
}

/**
 * Update course (admin only)
 */
function updateCourse($data) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    validateRequiredFields($data, ['courseId']);
    
    $courseId = intval($data['courseId']);
    $subjectName = sanitizeInput($data['subjectName'] ?? '');
    $teacherId = isset($data['teacherId']) ? intval($data['teacherId']) : null;
    $department = sanitizeInput($data['department'] ?? '');
    $status = sanitizeInput($data['status'] ?? '');
    
    try {
        $db = Database::getInstance();
        
        // Check if course exists
        $checkSql = "SELECT subject_id FROM subjects WHERE subject_id = ?";
        $checkStmt = $db->query($checkSql, [$courseId]);
        
        if (!$checkStmt->get_result()->fetch_assoc()) {
            sendErrorResponse('Course not found');
        }
        
        // Build update fields
        $updateFields = [];
        $params = [];
        
        if (!empty($subjectName)) {
            $updateFields[] = "subject_name = ?";
            $params[] = $subjectName;
        }
        
        if ($teacherId !== null) {
            // Validate teacher
            $teacherStmt = $db->query("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND status = 'active'", [$teacherId]);
            if (!$teacherStmt->get_result()->fetch_assoc()) {
                sendErrorResponse('Invalid teacher');
            }
            
            $updateFields[] = "teacher_id = ?";
            $params[] = $teacherId;
        }
        
        if (!empty($department)) {
            // Get department ID
            $deptStmt = $db->query("SELECT department_id FROM departments WHERE dept_name = ?", [$department]);
            $deptResult = $deptStmt->get_result()->fetch_assoc();
            
            if (!$deptResult) {
                sendErrorResponse('Invalid department');
            }
            
            $updateFields[] = "department_id = ?";
            $params[] = $deptResult['department_id'];
        }
        
        if (!empty($status)) {
            $updateFields[] = "status = ?";
            $params[] = $status;
        }
        
        if (empty($updateFields)) {
            sendErrorResponse('No fields to update');
        }
        
        $params[] = $courseId;
        $sql = "UPDATE subjects SET " . implode(', ', $updateFields) . " WHERE subject_id = ?";
        
        $db->query($sql, $params);
        
        logActivity(getCurrentUserId(), 'update_course', "Updated course ID: $courseId");
        sendSuccessResponse(null, 'Course updated successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to update course: ' . $e->getMessage());
    }
}

/**
 * Delete course (admin only)
 */
function deleteCourse($courseId) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    $courseId = intval($courseId);
    
    try {
        $db = Database::getInstance();
        
        // Check if course has associated feedback
        $checkSql = "SELECT COUNT(*) as count FROM feedback WHERE subject_id = ?";
        $checkStmt = $db->query($checkSql, [$courseId]);
        $feedbackCount = $checkStmt->get_result()->fetch_assoc()['count'];
        
        if ($feedbackCount > 0) {
            sendErrorResponse('Cannot delete course with associated feedback. Consider deactivating instead.');
        }
        
        // Delete course
        $sql = "DELETE FROM subjects WHERE subject_id = ?";
        $db->query($sql, [$courseId]);
        
        logActivity(getCurrentUserId(), 'delete_course', "Deleted course ID: $courseId");
        sendSuccessResponse(null, 'Course deleted successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to delete course: ' . $e->getMessage());
    }
}
?>

<?php
/**
 * Feedback API
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
                    getFeedbackList();
                    break;
                case 'student':
                    getStudentFeedback();
                    break;
                case 'teacher':
                    getTeacherFeedback();
                    break;
                case 'stats':
                    getFeedbackStats();
                    break;
                case 'rating-distribution':
                    getRatingDistribution();
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
        
        if (isset($input['action']) && $input['action'] === 'submit') {
            submitFeedback($input);
        } else {
            sendErrorResponse('Invalid action');
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action']) && $input['action'] === 'update') {
            updateFeedback($input);
        } else {
            sendErrorResponse('Invalid action');
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteFeedback($_GET['id']);
        } else {
            sendErrorResponse('Feedback ID required');
        }
        break;
        
    default:
        sendErrorResponse('Method not allowed', 405);
}

/**
 * Get feedback list (admin only)
 */
function getFeedbackList() {
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
        $rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
        $dateFilter = isset($_GET['dateFilter']) ? sanitizeInput($_GET['dateFilter']) : '';
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(s.name LIKE ? OR t.name LIKE ? OR sub.subject_name LIKE ? OR f.comment LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($department)) {
            $whereConditions[] = "d.dept_name = ?";
            $params[] = $department;
        }
        
        if (!empty($teacher)) {
            $whereConditions[] = "t.name = ?";
            $params[] = $teacher;
        }
        
        if ($rating > 0) {
            $whereConditions[] = "f.rating = ?";
            $params[] = $rating;
        }
        
        if (!empty($dateFilter)) {
            $whereConditions[] = getDateFilterCondition($dateFilter);
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM feedback f 
                     JOIN students s ON f.student_id = s.student_id 
                     JOIN teachers t ON f.teacher_id = t.teacher_id 
                     JOIN subjects sub ON f.subject_id = sub.subject_id 
                     JOIN departments d ON s.department_id = d.department_id 
                     $whereClause";
        
        $countStmt = $db->query($countSql, $params);
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        
        // Get pagination info
        $pagination = getPaginationInfo($page, $limit, $total);
        
        // Get feedback data
        $sql = "SELECT f.*, s.name as student_name, s.email as student_email, 
                       t.name as teacher_name, d.dept_name, sub.subject_name,
                       f.timestamp
                FROM feedback f 
                JOIN students s ON f.student_id = s.student_id 
                JOIN teachers t ON f.teacher_id = t.teacher_id 
                JOIN subjects sub ON f.subject_id = sub.subject_id 
                JOIN departments d ON s.department_id = d.department_id 
                $whereClause 
                ORDER BY f.timestamp DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $pagination['limit'];
        $params[] = $pagination['offset'];
        
        $stmt = $db->query($sql, $params);
        $feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format feedback data
        foreach ($feedbacks as &$feedback) {
            $feedback['formatted_date'] = formatDate($feedback['timestamp']);
            $feedback['formatted_datetime'] = formatDateTime($feedback['timestamp']);
        }
        
        $response = [
            'feedbacks' => $feedbacks,
            'pagination' => $pagination
        ];
        
        sendSuccessResponse($response, 'Feedback list retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve feedback list: ' . $e->getMessage());
    }
}

/**
 * Get student's own feedback
 */
function getStudentFeedback() {
    if (!isStudent()) {
        sendErrorResponse('Student access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        $studentId = getCurrentUserId();
        
        $sql = "SELECT f.*, t.name as teacher_name, d.dept_name, sub.subject_name,
                       f.timestamp
                FROM feedback f 
                JOIN teachers t ON f.teacher_id = t.teacher_id 
                JOIN subjects sub ON f.subject_id = sub.subject_id 
                JOIN departments d ON t.department_id = d.department_id 
                WHERE f.student_id = ? 
                ORDER BY f.timestamp DESC";
        
        $stmt = $db->query($sql, [$studentId]);
        $feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format feedback data
        foreach ($feedbacks as &$feedback) {
            $feedback['formatted_date'] = formatDate($feedback['timestamp']);
            $feedback['formatted_datetime'] = formatDateTime($feedback['timestamp']);
        }
        
        sendSuccessResponse($feedbacks, 'Student feedback retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve student feedback: ' . $e->getMessage());
    }
}

/**
 * Get teacher's received feedback
 */
function getTeacherFeedback() {
    if (!isTeacher()) {
        sendErrorResponse('Teacher access required', 403);
    }
    
    try {
        $db = Database::getInstance();
        $teacherId = getCurrentUserId();
        
        $sql = "SELECT f.*, s.name as student_name, s.email as student_email, 
                       d.dept_name, sub.subject_name, f.timestamp
                FROM feedback f 
                JOIN students s ON f.student_id = s.student_id 
                JOIN subjects sub ON f.subject_id = sub.subject_id 
                JOIN departments d ON s.department_id = d.department_id 
                WHERE f.teacher_id = ? 
                ORDER BY f.timestamp DESC";
        
        $stmt = $db->query($sql, [$teacherId]);
        $feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format feedback data
        foreach ($feedbacks as &$feedback) {
            $feedback['formatted_date'] = formatDate($feedback['timestamp']);
            $feedback['formatted_datetime'] = formatDateTime($feedback['timestamp']);
        }
        
        sendSuccessResponse($feedbacks, 'Teacher feedback retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve teacher feedback: ' . $e->getMessage());
    }
}

/**
 * Submit new feedback
 */
function submitFeedback($data) {
    if (!isStudent()) {
        sendErrorResponse('Only students can submit feedback', 403);
    }
    
    validateRequiredFields($data, ['teacherId', 'subjectId', 'rating', 'comment']);
    
    $teacherId = intval($data['teacherId']);
    $subjectId = intval($data['subjectId']);
    $rating = validateRating($data['rating']);
    $comment = sanitizeInput($data['comment']);
    $studentId = getCurrentUserId();
    
    if (empty(trim($comment))) {
        sendErrorResponse('Comment is required');
    }
    
    try {
        $db = Database::getInstance();
        
        // Check if feedback already exists for this student-subject combination
        $checkSql = "SELECT feedback_id FROM feedback WHERE student_id = ? AND subject_id = ?";
        $checkStmt = $db->query($checkSql, [$studentId, $subjectId]);
        
        if ($checkStmt->fetch()) {
            sendErrorResponse('Feedback already submitted for this subject');
        }
        
        // Validate teacher and subject
        $validateSql = "SELECT s.subject_id, t.teacher_id 
                       FROM subjects s 
                       JOIN teachers t ON s.teacher_id = t.teacher_id 
                       WHERE s.subject_id = ? AND t.teacher_id = ? AND s.status = 'active'";
        $validateStmt = $db->query($validateSql, [$subjectId, $teacherId]);
        
        if (!$validateStmt->fetch()) {
            sendErrorResponse('Invalid teacher or subject');
        }
        
        // Insert feedback
        $sql = "INSERT INTO feedback (student_id, teacher_id, subject_id, rating, comment) 
                VALUES (?, ?, ?, ?, ?)";
        
        $db->query($sql, [$studentId, $teacherId, $subjectId, $rating, $comment]);
        $feedbackId = $db->lastInsertId();
        
        logActivity($studentId, 'submit_feedback', "Submitted feedback ID: $feedbackId");
        sendSuccessResponse(['feedback_id' => $feedbackId], 'Feedback submitted successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to submit feedback: ' . $e->getMessage());
    }
}

/**
 * Update feedback (admin only)
 */
function updateFeedback($data) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    validateRequiredFields($data, ['feedbackId', 'rating', 'comment']);
    
    $feedbackId = intval($data['feedbackId']);
    $rating = validateRating($data['rating']);
    $comment = sanitizeInput($data['comment']);
    
    try {
        $db = Database::getInstance();
        
        $sql = "UPDATE feedback SET rating = ?, comment = ? WHERE feedback_id = ?";
        $db->query($sql, [$rating, $comment, $feedbackId]);
        
        logActivity(getCurrentUserId(), 'update_feedback', "Updated feedback ID: $feedbackId");
        sendSuccessResponse(null, 'Feedback updated successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to update feedback: ' . $e->getMessage());
    }
}

/**
 * Delete feedback (admin only)
 */
function deleteFeedback($feedbackId) {
    if (!isAdmin()) {
        sendErrorResponse('Admin access required', 403);
    }
    
    $feedbackId = intval($feedbackId);
    
    try {
        $db = Database::getInstance();
        
        $sql = "DELETE FROM feedback WHERE feedback_id = ?";
        $db->query($sql, [$feedbackId]);
        
        logActivity(getCurrentUserId(), 'delete_feedback', "Deleted feedback ID: $feedbackId");
        sendSuccessResponse(null, 'Feedback deleted successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to delete feedback: ' . $e->getMessage());
    }
}

/**
 * Get feedback statistics
 */
function getFeedbackStats() {
    try {
        $db = Database::getInstance();
        
        // Get total feedback count
        $totalSql = "SELECT COUNT(*) as total FROM feedback";
        $totalStmt = $db->query($totalSql);
        $totalFeedback = $totalStmt->get_result()->fetch_assoc()['total'];
        
        // Get average rating
        $avgSql = "SELECT AVG(rating) as average FROM feedback";
        $avgStmt = $db->query($avgSql);
        $averageRating = round($avgStmt->get_result()->fetch_assoc()['average'], 1);
        
        // Get active teachers count
        $teachersSql = "SELECT COUNT(DISTINCT teacher_id) as total FROM feedback";
        $teachersStmt = $db->query($teachersSql);
        $activeTeachers = $teachersStmt->get_result()->fetch_assoc()['total'];
        
        // Get monthly feedback count
        $monthlySql = "SELECT COUNT(*) as total FROM feedback 
                       WHERE MONTH(timestamp) = MONTH(CURRENT_DATE()) 
                       AND YEAR(timestamp) = YEAR(CURRENT_DATE())";
        $monthlyStmt = $db->query($monthlySql);
        $monthlyFeedback = $monthlyStmt->get_result()->fetch_assoc()['total'];
        
        $stats = [
            'totalFeedback' => $totalFeedback,
            'averageRating' => $averageRating,
            'activeTeachers' => $activeTeachers,
            'monthlyFeedback' => $monthlyFeedback
        ];
        
        sendSuccessResponse($stats, 'Statistics retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve statistics: ' . $e->getMessage());
    }
}

/**
 * Get rating distribution
 */
function getRatingDistribution() {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT rating, COUNT(*) as count 
                FROM feedback 
                GROUP BY rating 
                ORDER BY rating DESC";
        
        $stmt = $db->query($sql);
        $distribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Initialize all ratings with 0 count
        $ratingCounts = array_fill(1, 5, 0);
        
        foreach ($distribution as $item) {
            $ratingCounts[$item['rating']] = intval($item['count']);
        }
        
        $result = [
            'rating5' => $ratingCounts[5],
            'rating4' => $ratingCounts[4],
            'rating3' => $ratingCounts[3],
            'rating2' => $ratingCounts[2],
            'rating1' => $ratingCounts[1]
        ];
        
        sendSuccessResponse($result, 'Rating distribution retrieved successfully');
        
    } catch (Exception $e) {
        sendErrorResponse('Failed to retrieve rating distribution: ' . $e->getMessage());
    }
}

/**
 * Get date filter condition
 */
function getDateFilterCondition($filterType) {
    switch ($filterType) {
        case 'today':
            return "DATE(f.timestamp) = CURDATE()";
        case 'week':
            return "f.timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        case 'month':
            return "MONTH(f.timestamp) = MONTH(CURRENT_DATE()) AND YEAR(f.timestamp) = YEAR(CURRENT_DATE())";
        case 'quarter':
            return "f.timestamp >= DATE_FORMAT(CURDATE(), '%Y-%m-01') - INTERVAL (QUARTER(CURDATE()) - 1) QUARTER";
        case 'year':
            return "YEAR(f.timestamp) = YEAR(CURRENT_DATE())";
        default:
            return "1=1";
    }
}
?>

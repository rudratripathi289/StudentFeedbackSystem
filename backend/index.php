<?php
/**
 * Main Backend Entry Point
 * Student Feedback System Backend
 */

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Start session
session_start();

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/backend/';

// Remove base path from request URI
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove query string
$requestUri = strtok($requestUri, '?');

// Split the URI into segments
$segments = array_filter(explode('/', $requestUri));

// Route the request
if (empty($segments)) {
    // Root endpoint - show API info
    showApiInfo();
} elseif ($segments[0] === 'api') {
    // API endpoints
    if (isset($segments[1])) {
        $apiFile = $segments[1] . '.php';
        $apiPath = __DIR__ . '/api/' . $apiFile;
        
        if (file_exists($apiPath)) {
            require_once $apiPath;
        } else {
            sendNotFoundResponse('API endpoint not found');
        }
    } else {
        sendNotFoundResponse('API endpoint not specified');
    }
} elseif ($segments[0] === 'database') {
    // Database setup endpoint
    if (isset($segments[1]) && $segments[1] === 'setup') {
        setupDatabase();
    } else {
        sendNotFoundResponse('Database endpoint not found');
    }
} else {
    sendNotFoundResponse('Endpoint not found');
}

/**
 * Show API information
 */
function showApiInfo() {
    header('Content-Type: application/json');
    echo json_encode([
        'name' => 'Student Feedback System API',
        'version' => '1.0.0',
        'description' => 'Backend API for Student Feedback System',
        'endpoints' => [
            'Authentication' => [
                'POST /api/auth.php' => 'Login, Register, Logout',
                'GET /api/auth.php?action=check' => 'Check authentication status'
            ],
            'Feedback' => [
                'GET /api/feedback.php?action=list' => 'Get feedback list (admin)',
                'GET /api/feedback.php?action=student' => 'Get student feedback',
                'GET /api/feedback.php?action=teacher' => 'Get teacher feedback',
                'POST /api/feedback.php' => 'Submit feedback',
                'PUT /api/feedback.php' => 'Update feedback (admin)',
                'DELETE /api/feedback.php?id={id}' => 'Delete feedback (admin)'
            ],
            'Users' => [
                'GET /api/users.php?action=list' => 'Get user list (admin)',
                'GET /api/users.php?action=departments' => 'Get departments',
                'GET /api/users.php?action=profile' => 'Get user profile',
                'POST /api/users.php' => 'Create user (admin)',
                'PUT /api/users.php' => 'Update user (admin)',
                'DELETE /api/users.php?id={id}&type={type}' => 'Delete user (admin)'
            ],
            'Courses' => [
                'GET /api/courses.php?action=list' => 'Get course list (admin)',
                'GET /api/courses.php?action=student' => 'Get student courses',
                'GET /api/courses.php?action=teacher' => 'Get teacher courses',
                'GET /api/courses.php?action=departments' => 'Get departments',
                'GET /api/courses.php?action=teachers' => 'Get teachers',
                'POST /api/courses.php' => 'Create course (admin)',
                'PUT /api/courses.php' => 'Update course (admin)',
                'DELETE /api/courses.php?id={id}' => 'Delete course (admin)'
            ],
            'Database' => [
                'GET /database/setup' => 'Setup database schema'
            ]
        ],
        'documentation' => 'See README.md for detailed API documentation'
    ]);
}

/**
 * Setup database
 */
function setupDatabase() {
    try {
        // Include database configuration
        require_once __DIR__ . '/config/database.php';
        
        // Read and execute schema file
        $schemaFile = __DIR__ . '/database/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception('Schema file not found');
        }
        
        $schema = file_get_contents($schemaFile);
        
        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        $results = [];
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $connection->query($statement);
                    $results[] = [
                        'statement' => substr($statement, 0, 50) . '...',
                        'status' => 'success'
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'statement' => substr($statement, 0, 50) . '...',
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Database setup completed',
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Database setup failed',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Send not found response
 */
function sendNotFoundResponse($message) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'error' => 'Not Found',
        'message' => $message
    ]);
}
?>

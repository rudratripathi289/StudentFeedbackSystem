<?php
/**
 * Database Configuration
 * Student Feedback System Backend
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'feedback_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        // Establish MySQLi connection
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        // Set charset
        $this->connection->set_charset(DB_CHARSET);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = [], $param_types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $this->connection->error);
            }

            if (!empty($params)) {
                // Dynamically determine param types if not provided
                if (empty($param_types)) {
                    foreach ($params as $param) {
                        if (is_int($param)) {
                            $param_types .= 'i';
                        } elseif (is_float($param)) {
                            $param_types .= 'd';
                        } else {
                            $param_types .= 's';
                        }
                    }
                }
                $stmt->bind_param($param_types, ...$params);
            }
            
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->connection->insert_id;
    }
}

// Create database instance
$db = Database::getInstance();
?>

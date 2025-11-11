<?php
/**
 * Database Connection Class
 * ==========================
 * PDO database wrapper met error handling
 */

class Database {
    private static $instance = null;
    private $conn;
    
    /**
     * Constructor - Private voor Singleton pattern
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                Security::logError('Database connection failed: ' . $e->getMessage());
                die('Database connection failed. Please try again later.');
            }
        }
    }
    
    /**
     * Get Database Instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO Connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Prepare Statement
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    /**
     * Execute Query
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * Get Last Insert ID
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Begin Transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->conn->rollback();
    }
    
    /**
     * Test Connection
     */
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $stmt = $db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Setup Admin Account (run once)
     */
    public static function setupAdminAccount() {
        try {
            $db = self::getInstance()->getConnection();
            
            // Check if admin already exists
            $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([ADMIN_USERNAME]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Admin account already exists'];
            }
            
            // Create admin account
            $password_hash = password_hash(ADMIN_PASSWORD, PASSWORD_ARGON2ID);
            
            $stmt = $db->prepare("
                INSERT INTO admin_users (username, password_hash, email) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                ADMIN_USERNAME,
                $password_hash,
                ADMIN_EMAIL
            ]);
            
            return [
                'success' => true,
                'message' => 'Admin account created successfully'
            ];
            
        } catch (PDOException $e) {
            Security::logError('Admin setup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create admin account'
            ];
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

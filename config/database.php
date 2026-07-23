<?php
/**
 * Database configuration using PDO Singleton pattern
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $lastStatement;

    private function __construct() {
        // Use the global $pdo that is set by the root config.php
        global $pdo;
        
        // Check if PDO was successfully initialized
        if (!isset($pdo) || $pdo === null) {
            throw new Exception('Database connection not initialized. Check config.php database credentials.');
        }
        
        $this->pdo = $pdo;
        // Make the PDO instance globally accessible
        $GLOBALS['pdo'] = $this->pdo;
        
        // Verify connection is working
        try {
            $this->pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
        } catch (\PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prepare a SQL statement
     * @param string $sql SQL query
     * @return PDOStatement
     */
    public function prepare($sql) {
        if ($this->pdo === null) {
            throw new Exception('PDO instance is not set in Database class. Check config.php and database connection.');
        }
        try {
            return $this->pdo->prepare($sql);
        } catch (\PDOException $e) {
            throw new Exception('Database prepare failed: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO instance
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    /**
     * Get last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a database transaction
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the current transaction
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Roll back the current transaction
     * @return bool
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserializing
    public function __wakeup() {}
}
?>
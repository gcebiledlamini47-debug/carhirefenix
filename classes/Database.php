<?php
/**
 * Database.php - Database abstraction layer with prepared statements
 * Provides secure database operations preventing SQL injection
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        // Get database credentials from environment
        $db_host = getenv('DB_HOST') ?: 'localhost';
        $db_user = getenv('DB_USER') ?: 'root';
        $db_password = getenv('DB_PASSWORD') ?: '';
        $db_name = getenv('DB_NAME') ?: 'fenix';
        
        // Create connection
        $this->connection = new mysqli($db_host, $db_user, $db_password, $db_name);
        
        // Check connection
        if ($this->connection->connect_error) {
            error_log('Database connection failed: ' . $this->connection->connect_error);
            die('Database connection failed. Please contact support.');
        }
        
        // Set charset
        $this->connection->set_charset('utf8mb4');
    }
    
    /**
     * Get singleton database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Execute prepared statement and return results
     */
    public function query($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }
            
            // Bind parameters if provided
            if (!empty($params)) {
                // Auto-detect types if not provided
                if (empty($types)) {
                    $types = $this->getParamTypes($params);
                }
                $stmt->bind_param($types, ...$params);
            }
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            // Get results
            $result = $stmt->get_result();
            $rows = [];
            
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            
            $stmt->close();
            return $rows;
            
        } catch (Exception $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute query and return single row
     */
    public function queryOne($sql, $params = [], $types = '') {
        $results = $this->query($sql, $params, $types);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Insert data and return inserted ID
     */
    public function insert($table, $data) {
        try {
            $columns = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            
            $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES ($placeholders)";
            
            $types = $this->getParamTypes($values);
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $id = $this->connection->insert_id;
            $stmt->close();
            
            return $id;
            
        } catch (Exception $e) {
            error_log('Database insert error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = [], $whereTypes = '') {
        try {
            $setClause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
            $sql = "UPDATE $table SET $setClause WHERE $where";
            
            $values = array_merge(array_values($data), $whereParams);
            $types = $this->getParamTypes($values);
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected;
            
        } catch (Exception $e) {
            error_log('Database update error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete data
     */
    public function delete($table, $where, $params = [], $types = '') {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            
            $types = $this->getParamTypes($params);
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected;
            
        } catch (Exception $e) {
            error_log('Database delete error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Escape string for safe use in queries
     */
    public function escape($str) {
        return $this->connection->real_escape_string($str);
    }
    
    /**
     * Auto-detect parameter types for binding
     */
    private function getParamTypes($params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
    
    /**
     * Get raw connection (use sparingly)
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>

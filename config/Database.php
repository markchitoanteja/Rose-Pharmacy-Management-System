<?php
// Load application configuration and helper functions
require_once __DIR__ . '/App.php';

class Database
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $conn;

    public function __construct()
    {
        $this->servername = env("DB_HOST");
        $this->username   = env("DB_USERNAME");
        $this->password   = env("DB_PASSWORD");
        $this->dbname     = env("DB_DATABASE");

        $this->connectServer();
        $this->createDatabase();
        $this->connectDatabase();
        $this->createTables();
        $this->insertDefaults();
    }

    private function connectServer()
    {
        try {
            $this->conn = @new mysqli($this->servername, $this->username, $this->password);
            if ($this->conn->connect_error) {
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            log_error($e->getMessage());
            die("Connection failed. Please check logs for details.");
        }
    }

    private function createDatabase()
    {
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname;
        if (!$this->conn->query($sql)) {
            die("Error creating database: " . $this->conn->error);
        }
    }

    private function connectDatabase()
    {
        $this->conn->select_db($this->dbname);
    }

    private function createTables()
    {
        $queries = [];

        $queries[] = "CREATE TABLE IF NOT EXISTS roles (
            role_id INT PRIMARY KEY AUTO_INCREMENT,
            role_name VARCHAR(50) NOT NULL
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role_id INT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1, -- 1 = active, 0 = inactive (soft delete)
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(role_id)
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            contact_number VARCHAR(20),
            address VARCHAR(255)
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS medicines (
            medicine_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            description TEXT,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            expiry_date DATE,
            supplier_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS sales (
            sale_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS sale_items (
            sale_item_id INT PRIMARY KEY AUTO_INCREMENT,
            sale_id INT NOT NULL,
            medicine_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
            FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id)
        )";

        $queries[] = "CREATE TABLE IF NOT EXISTS activity_logs (
            log_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )";

        foreach ($queries as $sql) {
            if (!$this->conn->query($sql)) {
                die("Error creating table: " . $this->conn->error);
            }
        }
    }

    private function insertDefaults()
    {
        $this->conn->query("INSERT IGNORE INTO roles (role_id, role_name) VALUES (1, 'Admin'), (2, 'Cashier')");
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $this->conn->query("INSERT IGNORE INTO users (user_id, full_name, username, password_hash, role_id)
                            VALUES (1, 'Default Admin', 'admin', '$adminPassword', 1)");
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function close()
    {
        $this->conn->close();
    }

    // ========================
    // SQL Helper Functions
    // ========================

    public function insert($table, $data)
    {
        try {
            $fields = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), "?"));
            $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Insert prepare failed: " . $this->conn->error);
            }

            $types = str_repeat("s", count($data));
            $stmt->bind_param($types, ...array_values($data));

            return $stmt->execute();
        } catch (Exception $e) {
            log_error($e->getMessage());
            return false;
        }
    }

    public function update($table, $data, $where, $whereParams)
    {
        try {
            $set = implode(", ", array_map(fn($k) => "$k = ?", array_keys($data)));
            $sql = "UPDATE $table SET $set WHERE $where";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Update prepare failed: " . $this->conn->error);
            }

            $types = str_repeat("s", count($data) + count($whereParams));
            $stmt->bind_param($types, ...array_merge(array_values($data), $whereParams));

            return $stmt->execute();
        } catch (Exception $e) {
            log_error($e->getMessage());
            return false;
        }
    }

    public function delete($table, $where, $whereParams)
    {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Delete prepare failed: " . $this->conn->error);
            }

            $types = str_repeat("s", count($whereParams));
            $stmt->bind_param($types, ...$whereParams);

            return $stmt->execute();
        } catch (Exception $e) {
            log_error($e->getMessage());
            return false;
        }
    }

    public function select_one($table, $where, $whereParams)
    {
        try {
            $sql = "SELECT * FROM $table WHERE $where LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Select_one prepare failed: " . $this->conn->error);
            }

            $types = str_repeat("s", count($whereParams));
            $stmt->bind_param($types, ...$whereParams);
            $stmt->execute();

            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            log_error($e->getMessage());
            return null;
        }
    }

    public function select_many($table, $where, $whereParams, $orderBy = null, $direction = "ASC", $limit = null)
    {
        try {
            $sql = "SELECT * FROM $table WHERE $where";
            if ($orderBy) $sql .= " ORDER BY $orderBy $direction";
            if ($limit) $sql .= " LIMIT $limit";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Select_many prepare failed: " . $this->conn->error);
            }

            $types = str_repeat("s", count($whereParams));
            $stmt->bind_param($types, ...$whereParams);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            log_error($e->getMessage());
            return [];
        }
    }

    public function select_all($table, $orderBy = null, $direction = "ASC", $limit = null)
    {
        try {
            $sql = "SELECT * FROM $table";
            if ($orderBy) $sql .= " ORDER BY $orderBy $direction";
            if ($limit) $sql .= " LIMIT $limit";

            $result = $this->conn->query($sql);
            if (!$result) {
                throw new Exception("Select_all query failed: " . $this->conn->error);
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            log_error($e->getMessage());
            return [];
        }
    }

    public function custom_query($sql, $params = [], $single = false)
    {
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Custom_query prepare failed: " . $this->conn->error);
            }

            if ($params) {
                $types = str_repeat("s", count($params));
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) {
                throw new Exception("Custom_query execution failed: " . $stmt->error);
            }

            $rows = $result->fetch_all(MYSQLI_ASSOC);

            if ($single) {
                return $rows[0] ?? null; // return single row
            }

            return $rows; // always array of rows
        } catch (Exception $e) {
            log_error($e->getMessage());
            return $single ? null : [];
        }
    }
}

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

    // Constructor -> initialize connection & setup
    public function __construct()
    {
        $this->servername = env("DB_HOST");
        $this->username = env("DB_USERNAME");
        $this->password = env("DB_PASSWORD");
        $this->dbname = env("DB_DATABASE");

        $this->connectServer();
        $this->createDatabase();
        $this->connectDatabase();
        $this->createTables();
        $this->insertDefaults();
    }

    // Connect to MySQL server (no DB selected yet)
    private function connectServer()
    {
        try {
            // Attempt to connect
            $this->conn = @new mysqli($this->servername, $this->username, $this->password);

            // Check for connection errors
            if ($this->conn->connect_error) {
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            // Log error
            log_error($e->getMessage());

            // Stop execution with a safe message
            die("Connection failed. Please check logs for details.");
        }
    }

    // Create database if not exists
    private function createDatabase()
    {
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname;
        if (!$this->conn->query($sql)) {
            die("Error creating database: " . $this->conn->error);
        }
    }

    // Select the database
    private function connectDatabase()
    {
        $this->conn->select_db($this->dbname);
    }

    // Create all required tables
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

    // Insert default roles & admin user if not exists
    private function insertDefaults()
    {
        // Insert Roles
        $this->conn->query("INSERT IGNORE INTO roles (role_id, role_name) VALUES (1, 'Admin'), (2, 'Cashier')");

        // Insert Default Admin
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $this->conn->query("INSERT IGNORE INTO users (user_id, full_name, username, password_hash, role_id)
                            VALUES (1, 'Default Admin', 'admin', '$adminPassword', 1)");
    }

    // Public function to get DB connection
    public function getConnection()
    {
        return $this->conn;
    }

    // Public query helper
    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    // Prevent SQL injection with prepared statements
    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    // Close connection
    public function close()
    {
        $this->conn->close();
    }
}

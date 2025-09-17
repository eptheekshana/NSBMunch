<?php
// Database configuration file for NSBMunch
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty password for XAMPP default
define('DB_NAME', 'nsbmunch');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get database connection
function getConnection() {
    global $pdo;
    return $pdo;
}

// Function to execute query and return results
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

// Function to fetch single row
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Function to fetch multiple rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

// Function to get last inserted ID
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Function to check if table exists
function tableExists($tableName) {
    global $pdo;
    try {
        $sql = "SELECT 1 FROM $tableName LIMIT 1";
        $pdo->query($sql);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
?>
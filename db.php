<?php
// db.php - WASMER / SQLITE VERSION
// This points to the SQLite file sitting in the same folder

$db_file = __DIR__ . '/foothaven.sqlite';

try {
    // Connect to the SQLite file
    $conn = new PDO("sqlite:" . $db_file);
    
    // Set error mode to Exceptions (Critical for debugging)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable Foreign Keys
    $conn->exec("PRAGMA foreign_keys = ON;");

} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
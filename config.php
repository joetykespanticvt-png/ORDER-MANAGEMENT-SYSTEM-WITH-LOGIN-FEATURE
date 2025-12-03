<?php
// config.php - FINAL WORKING VERSION (Using existing 'test' database)

$host = '127.0.0.1'; 
$port = '3307';      
$db_name = 'test'; // *** FIX: CHANGED TO A KNOWN DATABASE NAME ***
$username = 'root';   
$password = ''; // Use the password that worked before (likely blank, or the one you set)

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // If you see this, the password/username is wrong again.
    die("Connection failed: " . $e->getMessage()); 
}
?>
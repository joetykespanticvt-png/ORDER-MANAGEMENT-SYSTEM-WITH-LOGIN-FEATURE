<?php
// config.php 

$host = '127.0.0.1'; 
$port = '3307';      
$db_name = 'test'; 
$username = 'root';   
$password = ''; 
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
   
    die("Connection failed: " . $e->getMessage()); 
}

?>

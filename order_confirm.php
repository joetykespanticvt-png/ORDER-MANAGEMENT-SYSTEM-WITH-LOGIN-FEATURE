<?php
// order_confirm.php - Displays the final result

session_start();

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 2. Check for order details passed via session
if (!isset($_SESSION['order_total']) || !isset($_SESSION['order_change'])) {
    // If no order details are found, redirect to prevent direct access
    header('Location: welcome.php');
    exit();
}

// Grab the details and format them (matching your desired output)
$total_cost = number_format($_SESSION['order_total'], 0);
$change = number_format($_SESSION['order_change'], 0);
$current_user = $_SESSION['username'];

// Clear the session variables immediately after reading them (one-time display)
unset($_SESSION['order_total']);
unset($_SESSION['order_change']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body>
    
    <p>The total cost is <?= $total_cost; ?></p>
    <p>Your change is <?= $change; ?></p>
    
    <h2>Thanks for the order! <?= htmlspecialchars($current_user); ?></h2>
    
    <p><a href="welcome.php">Go back to ordering</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
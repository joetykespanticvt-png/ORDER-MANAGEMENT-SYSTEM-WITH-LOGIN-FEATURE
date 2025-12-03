<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['order_total']) || !isset($_SESSION['order_change'])) {
    header('Location: welcome.php');
    exit();
}

$total_cost = number_format($_SESSION['order_total'], 0);
$change = number_format($_SESSION['order_change'], 0);
$current_user = $_SESSION['username'];

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

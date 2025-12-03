<?php
session_start();
require_once 'config.php';

if (isset($_POST['register_btn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: register.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Username already taken.';
        header('Location: register.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$username, $hashed_password])) {
        $_SESSION['success'] = 'Registration successful! You can now log in.';
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: register.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: sans-serif; background-color: #fff; margin: 50px; }
        .container { width: 300px; padding: 20px; border: 1px solid #ccc; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; box-sizing: border-box; color: gray;
        }
        button {
            background-color: #e0e0e0; color: black; padding: 10px 15px; margin: 8px 0; border: none; cursor: pointer; width: 100%; font-size: 18px; text-align: center;
        }
        .error, .success { padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register here</h2>

        <?php
        if (isset($_SESSION['error'])): ?>
            <p class="error"><?= $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']);
        endif;
        if (isset($_SESSION['success'])): ?>
            <p class="success"><?= $_SESSION['success']; ?></p>
            <?php unset($_SESSION['success']);
        endif;
        ?>

        <form action="register.php" method="post">
            <input type="text" name="username" placeholder="username here" required>
            <input type="password" name="password" placeholder="password here" required>
            <button type="submit" name="register_btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>

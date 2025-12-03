<?php

session_start();

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit();
}

if (isset($_POST['login_btn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header('Location: welcome.php');
                exit();

            } else {
                $_SESSION['error'] = 'Invalid username or password.';
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: login.php');
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Database error during login: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    </head>
<body>
    <div class="container">
        <h2>Login here</h2>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="username here" required>
            <input type="password" name="password" placeholder="password here" required>
            <button type="submit" name="login_btn">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>

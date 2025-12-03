<?php
// login.php - FINAL CORRECTED VERSION

// START DEBUGGING CODE (Optional: Uncomment these lines if you need to see errors again)
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

session_start(); // <<< This is the ONLY session_start() call

require_once 'config.php'; 

// Check if user is already logged in, redirect to welcome.php
if (isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit();
}

if (isset($_POST['login_btn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Check if all fields are filled
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit();
    }

    try {
        // 2. Query the database for the user
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // 3. Verify the password hash
            if (password_verify($password, $user['password'])) {
                
                // 4. Login successful: Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // 5. Redirect to welcome page
                header('Location: welcome.php'); 
                exit();

            } else {
                // Password incorrect
                $_SESSION['error'] = 'Invalid username or password.';
                header('Location: login.php');
                exit();
            }
        } else {
            // Username not found
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
        // Display messages
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
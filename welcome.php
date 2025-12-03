<?php
// welcome.php - Canteen Page with Full Database Operations and Redirect

session_start();
require_once 'config.php'; // Includes $pdo connection

// 1. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user = $_SESSION['username'];
$menu_items = [];
$item_fetch_error = '';

try {
    // 2. Fetch the current menu items from the grocery_db
    // NOTE: We use the 'grocery_db.' prefix because the items tables are separate from the 'users' table.
    $stmt = $pdo->query("SELECT item_id, item_name, price, stock_quantity FROM grocery_db.grocery_items WHERE stock_quantity > 0 ORDER BY item_name ASC");
    $raw_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reformat array for easy access
    foreach ($raw_items as $item) {
        $menu_items[$item['item_name']] = [
            'price' => (float)$item['price'],
            'stock' => (int)$item['stock_quantity'],
            'id' => (int)$item['item_id']
        ];
    }

} catch (PDOException $e) {
    // If this fails, the 'grocery_db' is not accessible or the table is wrong.
    $item_fetch_error = 'Error loading menu items (DB Error). Check that grocery_db exists.';
}


// --- Order Processing Logic ---
$order_error_message = '';

if (isset($_POST['submit_order']) && empty($item_fetch_error)) {
    $item_name = $_POST['order_item'];
    $quantity = (int)$_POST['quantity'];
    $cash = (float)$_POST['cash'];

    // Validation
    if (!isset($menu_items[$item_name])) {
        $order_error_message = 'Invalid item selected.';
    } elseif ($quantity <= 0) {
        $order_error_message = 'Quantity must be greater than zero.';
    } elseif ($quantity > $menu_items[$item_name]['stock']) {
        $order_error_message = 'Insufficient stock. Only ' . $menu_items[$item_name]['stock'] . ' available.';
    } else {
        
        $price = $menu_items[$item_name]['price'];
        $item_id = $menu_items[$item_name]['id'];
        $total_cost = $price * $quantity;
        $change = $cash - $total_cost;

        if ($change < 0) {
            $order_error_message = 'Cash is insufficient. Total cost is ' . number_format($total_cost, 2) . ' PHP.';
        } else {
            // --- DATABASE TRANSACTIONS (SAVE ORDER AND UPDATE STOCK) ---
            try {
                $pdo->beginTransaction(); 

                // 1. INSERT: Save the order to the 'orders' table in grocery_db
                $sql_insert = "INSERT INTO grocery_db.orders (user_id, item_name, quantity, total_cost)
                               VALUES (:user_id, :item_name, :quantity, :total_cost)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':user_id' => $current_user_id,
                    ':item_name' => $item_name,
                    ':quantity' => $quantity,
                    ':total_cost' => $total_cost
                ]);

                // 2. UPDATE: Reduce stock quantity in grocery_db.grocery_items
                $sql_update = "UPDATE grocery_db.grocery_items SET stock_quantity = stock_quantity - :quantity WHERE item_id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    ':quantity' => $quantity,
                    ':id' => $item_id
                ]);

                $pdo->commit(); 

                // ----------------------------------------------------
                // SUCCESS: Store details and REDIRECT to confirmation page
                // ----------------------------------------------------
                $_SESSION['order_total'] = $total_cost;
                $_SESSION['order_change'] = $change;
                
                header('Location: order_confirm.php');
                exit();
                // ----------------------------------------------------

            } catch (PDOException $e) {
                $pdo->rollBack(); 
                // This is the error you saw: "Failed to process order (DB Error)."
                $order_error_message = 'Failed to process order (DB Error). Check if the orders table exists.';
            }
        }
    }
}


// --- Order History Fetch (for display on this page, if user returns) ---
// This part is kept for completeness, but the user will be redirected away on a successful order.
$order_history = [];
try {
    $sql_history = "SELECT order_id, item_name, quantity, total_cost, order_date 
                    FROM grocery_db.orders 
                    WHERE user_id = :user_id 
                    ORDER BY order_date DESC";
    $stmt_history = $pdo->prepare($sql_history);
    $stmt_history->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_history->execute();
    $order_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // History fetch failure is less critical than order processing failure
    $history_error = "Could not fetch order history.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteen Ordering</title>
</head>
<body>
    <h1>Welcome to the canteen, <span style="color: red;"><?= htmlspecialchars($current_user); ?></span></h1>
    <p><a href="logout.php">Logout</a></p>
    
    <h2>Current Menu and Stock:</h2>
    <ul>
        <?php foreach ($menu_items as $item => $data) : ?>
            <li><?= htmlspecialchars($item); ?> - <?= number_format($data['price'], 2); ?> PHP (Stock: **<?= $data['stock']; ?>**)</li>
        <?php endforeach; ?>
    </ul>

    <hr>
    
    <?php if ($order_error_message): ?>
        <p style="color: red; font-weight: bold;">❌ <?= $order_error_message; ?></p>
    <?php endif; ?>

    <h3>Place New Order</h3>
    <form action="welcome.php" method="post">
        
        <label for="order_item">Choose your order:</label>
        <select name="order_item" id="order_item">
            <?php if (empty($menu_items)): ?>
                 <option value="">No items available</option>
            <?php else: ?>
                <?php foreach ($menu_items as $item => $data) : ?>
                    <option value="<?= htmlspecialchars($item); ?>"><?= htmlspecialchars($item); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        
        <p>
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required min="1" value="1">
        </p>
        
        <p>
            <label for="cash">Cash:</label>
            <input type="number" id="cash" name="cash" required min="0" step="any">
        </p>

        <button type="submit" name="submit_order">Submit</button>
    </form>
    
    <hr>

    <h2>Your Order History</h2>
    <?php if (!empty($order_history)): ?>
        <?php else: ?>
        <p>You have not placed any orders yet.</p>
    <?php endif; ?>

</body>
</html>
<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_table = isset($_POST['product_table']) ? $_POST['product_table'] : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($user_id && $product_id > 0 && $product_table && $quantity > 0) {
    try {
        $updateQuery = $db->prepare("UPDATE orders SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id AND product_table = :product_table");
        $updateQuery->execute(['quantity' => $quantity, 'user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $product_table]);

        if ($updateQuery->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Query failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
?>

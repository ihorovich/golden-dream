<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$product_id = $data['product_id'];
$product_table = $data['product_table'];
$quantity = $data['quantity'];

try {
    $updateQuery = $db->prepare("UPDATE orders SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id AND product_table = :product_table");
    $updateQuery->execute([
        'quantity' => $quantity,
        'user_id' => $user_id,
        'product_id' => $product_id,
        'product_table' => $product_table
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Update failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}
?>

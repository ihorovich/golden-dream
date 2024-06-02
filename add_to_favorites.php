<?php
session_start();

if (!isset($_SESSION['isAuthenticated']) || $_SESSION['isAuthenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include 'db_connection.php'; // Підключення до бази даних

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$product_table = $data['table'];
$user_id = $_SESSION['user_id'];

try {
    $query = $db->prepare("INSERT INTO favorites (user_id, product_id, product_table) VALUES (:user_id, :product_id, :product_table)");
    $query->execute(['user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $product_table]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Insert failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $e->getMessage()]);
}
?>

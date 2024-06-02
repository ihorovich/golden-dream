<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$product_id = $data['product_id'];
$table = $data['product_table'];
$quantity = $data['quantity'];

$db = new PDO("mysql:host=localhost;dbname=jewelry_company;charset=utf8", 'root', 'CBuVjYDNiWkNcbcV');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("UPDATE orders SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id AND product_table = :product_table");
$stmt->execute(['quantity' => $quantity, 'user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $table]);

echo json_encode(['success' => true]);
?>

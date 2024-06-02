<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$product_id = $data['product_id'];
$table = $data['product_table'];
$quantity = $data['quantity'];
$unit_price = $data['unit_price'];

$db = new PDO("mysql:host=localhost;dbname=jewelry_company;charset=utf8", 'root', 'CBuVjYDNiWkNcbcV');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("INSERT INTO orders (user_id, product_id, product_table, quantity, unit_price) VALUES (:user_id, :product_id, :product_table, :quantity, :unit_price)");
$stmt->execute(['user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $table, 'quantity' => $quantity, 'unit_price' => $unit_price]);

echo json_encode(['success' => true]);
?>

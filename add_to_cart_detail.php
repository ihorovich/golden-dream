<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$product_table = isset($_POST['product_table']) ? htmlspecialchars($_POST['product_table']) : '';

if ($product_id > 0 && $product_table) {
    try {
        // Check if the product is already in the cart
        $checkQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id AND product_id = :product_id AND product_table = :product_table");
        $checkQuery->execute(['user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $product_table]);
        $existingItem = $checkQuery->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            echo json_encode(['success' => false, 'message' => 'Product is already in the cart.']);
            exit;
        }

        // Add to cart logic
        $stmt = $db->prepare("INSERT INTO orders (user_id, product_id, product_table, quantity, unit_price) VALUES (:user_id, :product_id, :product_table, 1, (SELECT price FROM $product_table WHERE product_id = :product_id))");
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id, 'product_table' => $product_table]);

        // Fetch updated cart items
        $cartQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id");
        $cartQuery->execute(['user_id' => $user_id]);
        $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

        $cartItemsDetails = [];
        foreach ($cartItems as $item) {
            $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
            $productQuery->execute(['product_id' => $item['product_id']]);
            $product = $productQuery->fetch(PDO::FETCH_ASSOC);
            $productName = $product['name'];
            $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';

            $cartItemsDetails[] = [
                'product_id' => $item['product_id'],
                'product_table' => $item['product_table'],
                'name' => $productName,
                'image' => $productImage,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ];
        }

        echo json_encode(['success' => true, 'cartItems' => $cartItemsDetails, 'cartCount' => count($cartItems)]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or table.']);
}
?>

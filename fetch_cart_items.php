<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    $cartQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id");
    $cartQuery->execute(['user_id' => $user_id]);
    $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cartItems as $item) {
        $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
        $productQuery->execute(['product_id' => $item['product_id']]);
        $product = $productQuery->fetch(PDO::FETCH_ASSOC);
        $productName = $product['name'];
        $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';
        echo '
        <div class="cart-item" data-id="' . $item['product_id'] . '" data-table="' . $item['product_table'] . '" data-price="' . $item['unit_price'] . '">
            <img src="' . $productImage . '" alt="' . htmlspecialchars($productName) . '" style="width: 50px; height: 50px;">
            <a href="product-detail.php?product_id=' . $item['product_id'] . '&table=' . $item['product_table'] . '">' . htmlspecialchars($productName) . '</a>
            <div class="cart-item-controls">
                <button onclick="updateQuantity(' . $item['product_id'] . ', \'' . $item['product_table'] . '\', -1)">-</button>
                <span class="quantity">' . $item['quantity'] . '</span>
                <button onclick="updateQuantity(' . $item['product_id'] . ', \'' . $item['product_table'] . '\', 1)">+</button>
            </div>
            <p>' . htmlspecialchars($item['unit_price']) . '$</p>
            <button onclick="removeFromCart(' . $item['product_id'] . ', \'' . $item['product_table'] . '\')">Видалити</button>
        </div>';
    }
}
?>

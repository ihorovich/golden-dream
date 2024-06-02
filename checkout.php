<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

$cartQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id");
$cartQuery->execute(['user_id' => $user_id]);
$cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (count($cartItems) === 0) {
        echo "<script>alert('Ваш кошик порожній. Додайте товари перед оформленням замовлення.'); window.location.href = 'checkout.php';</script>";
        exit;
    }

    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $comment = $_POST['comment'];
    $shipping_method = $_POST['shipping_method'];
    $payment_method = $_POST['payment_method'];

    $shipping_methods = [
        'nova_poshta' => 'НОВА ПОШТА',
        'ukrposhta' => 'УКР ПОШТА'
    ];

    $payment_methods = [
        'credit_card' => 'Кредитна карта',
        'paypal' => 'PayPal',
        'cash_on_delivery' => 'Оплата при доставці'
    ];

    try {
        $db->beginTransaction();

        $orderQuery = $db->prepare("INSERT INTO orders_summary (user_id, name, address, phone, comment, shipping_method, payment_method) VALUES (:user_id, :name, :address, :phone, :comment, :shipping_method, :payment_method)");
        $orderQuery->execute([
            'user_id' => $user_id,
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'comment' => $comment,
            'shipping_method' => $shipping_methods[$shipping_method],
            'payment_method' => $payment_methods[$payment_method]
        ]);

        $orderId = $db->lastInsertId();

        foreach ($cartItems as $item) {
            $orderItemQuery = $db->prepare("INSERT INTO order_items (order_id, product_id, product_table, quantity, unit_price) VALUES (:order_id, :product_id, :product_table, :quantity, :unit_price)");
            $orderItemQuery->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_table' => $item['product_table'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ]);
        }

        $clearCartQuery = $db->prepare("DELETE FROM orders WHERE user_id = :user_id");
        $clearCartQuery->execute(['user_id' => $user_id]);

        $db->commit();
        header('Location: confirmation.php?order_id=' . $orderId);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Order processing failed: " . $e->getMessage());
        echo "Order processing failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .checkout-container {
            display: flex;
            justify-content: space-between;
            max-width: 1400px;
            margin: auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .checkout-form-container,
        .checkout-items-container {
            width: 48%;
        }
        .checkout-form-container h2{
            font-size: 20px;
            text-transform: uppercase;
            color: #0000ff;
            font-weight: 500;
        }
        .checkout-form-container h3{
            text-transform: uppercase;
            color: #0000ff;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .checkout-item {
            display: flex;
            gap: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #000;
        }
        .checkout-total {
            text-align: right;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .checkout-form {
            margin-top: 20px;
        }
        .checkout-items-container h2{
            text-transform: uppercase;
            color: #0000ff;
            font-weight: 500;
            margin-bottom: 15px;
        }
        .checkout-form textarea {
            width: 600px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .checkout-form input{
            width: 600px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .checkout-form select{
            width: 622px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .checkout-form button {
            background: #000;
            color: #fff;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 622px;
        }
        .checkout-form h3 {
            margin-top: 20px;
        }
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -600px;
            width: 500px;
            height: 100%;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.3);
            transition: right 0.3s;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        .cart-sidebar.open {
            right: 0;
        }
        .cart-close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 20px;
        }
        .cart-items-container {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .text-products{
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 250px;
        }
        .total-price-container {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }
        .total-price {
            font-weight: bold;
            text-align: right;
        }
        .btn-checkout {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0000ff;
            color: #fff;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .btn-checkout:hover {
            background-color: #0000cc;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
        }
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .desc-product{
            font-size: 14px;
            color: #0000ff;
            font-weight: 500;
            text-decoration: none;
            line-height: 20px;
        }
        .detail-description {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            height: 100%;
            margin-left: 20px;
            gap: 25px;
        }
        .name-product {
            font-size: 14px;
            color: #0000ff;
            line-height: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-delete {
            border: 1px solid #0000ff;
            background-color: transparent;
            color: #0000ff;
            height: 23px;
        }
        .btn-plus-minus {
            border: 1px solid #0000ff;
            background-color: transparent;
            color: black;
            padding: 5px;
            cursor: pointer;
            height: 27px;
            width: 27px;
        }
        .h2-cart {
            font-size: 20px;
            font-weight: 400;
            color: #0000ff;
            margin-left: 20px;
        }
        .price-product {
            color: #0000ff;
        }
        .cart-div {
            margin-top: 20px;
        }
        .price-div {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
        .price-div span {
            font-weight: 600;
        }
        .quantity {
            color: #0000ff;
            font-weight: 500;
        }
        .error {
            border-color: red;
        }
        .error-message {
            color: red;
            font-size: 12px;
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="item-header">
                <div class="shopNameE-COMMERCE"><a class="shopName" href="index.php"><b class="nameE-COMMERCE">ЗОЛОТЕ</b> ДИВО</a></div>
                    <div class="item-icon-header">
                        <img class="item-icon user-icon" src="image/user-icon.svg" alt="Користувач" onclick="window.open('profile.php', '_blank')">
                        <img class="item-icon" src="image/heart-icon-head.png" alt="Favorites" onclick="window.open('favorites.php', '_blank')">
                    <div class="cart-icon-container">
                        <img class="item-icon cart-icon" src="image/cart_icon.svg" alt="Корзина" onclick="toggleCart()">
                        <span id="cart-count" class="cart-count"><?= count($cartItems) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container checkout-container">
            <div class="checkout-form-container">
                <h2>Оформлення замовлення</h2>
                <form class="checkout-form" method="POST" action="checkout.php">
                    <input type="text" name="name" placeholder="Ім'я" required>
                    <input type="text" name="address" placeholder="Адреса" required>
                    <input type="text" name="phone" placeholder="Телефон" required>
                    <textarea name="comment" placeholder="Коментар"></textarea>
                    <h3>Спосіб доставки</h3>
                    <select name="shipping_method" required>
                        <option value="nova_poshta">НОВА ПОШТА</option>
                        <option value="ukrposhta">УКР ПОШТА</option>
                    </select>
                    <h3>Спосіб оплати</h3>
                    <select name="payment_method" required>
                        <option value="credit_card">Кредитна карта</option>
                        <option value="paypal">PayPal</option>
                        <option value="cash_on_delivery">Оплата при доставці</option>
                    </select>
                    <button type="submit">Замовити</button>
                </form>
            </div>
            <div class="checkout-items-container">
                <h2>Ваші товари</h2>
                <?php if (count($cartItems) > 0): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php
                        $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
                        $productQuery->execute(['product_id' => $item['product_id']]);
                        $product = $productQuery->fetch(PDO::FETCH_ASSOC);
                        $productName = $product['name'];
                        $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';
                        ?>
                        <div class="checkout-item">
                            <div>
                                <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($productName) ?>" style="width: 150px; height: 200px;">
                            </div>
                            <div class="text-products">
                                <div><span><a class="desc-product" href="product-detail.php?product_id=<?= $item['product_id'] ?>&table=<?= $item['product_table'] ?>" target="_blank"><?= htmlspecialchars($productName) ?></a></span></div>
                                <div><span><?= $item['quantity'] ?> x $<?= $item['unit_price'] ?></span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="checkout-total">
                        <strong>Всього:</strong>
                        <?php
                        $total = array_reduce($cartItems, function($sum, $item) {
                            return $sum + ($item['quantity'] * $item['unit_price']);
                        }, 0);
                        ?>
                        $<?= number_format($total, 2) ?>
                    </div>
                <?php else: ?>
                    <p>Ваш кошик порожній</p>
                <?php endif; ?>
            </div>
        </div>
        <div id="cart-sidebar" class="cart-sidebar">
        <button class="cart-close-btn" onclick="toggleCart()">×</button>
        <div class="cart-div">
            <h2 class="h2-cart">КОРЗИНА</h2>
            <hr>
        </div>
        <div id="cart-items" class="cart-items-container">
            <?php foreach ($cartItems as $item):
                $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
                $productQuery->execute(['product_id' => $item['product_id']]);
                $product = $productQuery->fetch(PDO::FETCH_ASSOC);
                $productName = $product['name'];
                $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';
            ?>
            <div class="cart-item" data-id="<?= $item['product_id'] ?>" data-table="<?= $item['product_table'] ?>" data-price="<?= $item['unit_price'] ?>">
                <div>
                    <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($productName) ?>" style="width: 150px; height: 200px;">
                </div>
                <div class="detail-description">
                    <a class="name-product" href="product-detail.php?product_id=<?= $item['product_id'] ?>&table=<?= $item['product_table'] ?>"><?= htmlspecialchars($productName) ?></a>
                    <div class="cart-item-controls">
                        <button class="btn-plus-minus" onclick="updateQuantity(<?= $item['product_id'] ?>, '<?= $item['product_table'] ?>', -1)"><img src="image/add-remove.svg"/></button>
                        <span class="quantity"><?= $item['quantity'] ?></span>
                        <button class="btn-plus-minus" onclick="updateQuantity(<?= $item['product_id'] ?>, '<?= $item['product_table'] ?>', 1)"><img src="image/add-product.svg"/></button>
                    </div>
                    <p class="price-product"><?= htmlspecialchars($item['unit_price']) ?>$</p>
                    <button class="btn-delete" onclick="removeFromCart(<?= $item['product_id'] ?>, '<?= $item['product_table'] ?>')">Видалити товар</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="total-price-container">
            <div class="price-div">
                <p class="total-price">Загальна сума:</p>
                <span id="total-price">0.00$</span>
            </div>
            <button class="btn-checkout" onclick="checkout()">ОФОРМИТИ ЗАМОВЛЕННЯ</button>
        </div>
    </div>
    </main>
    <footer>
    <div class="footer-container">
        <div class="head-footer">
            <div class="desc-footer">
                <p>Давайте створимо щось чудове разом !</p>
            </div>
            <div class="div-menu-footer">
                <div class="item-menu-footer">
                    <ul class="ul-menu-footer">
                        <li><a class="menu-footer" href="ring.php">ОБРУЧКИ</a></li>
                        <li><a class="menu-footer" href="pendants.php">ПІДВІСКИ</a></li>
                        <li><a class="menu-footer" href="bracelet.php">БРАСЛЕТИ</a></li>
                        <li><a class="menu-footer" href="earrings.php">СЕРЕЖКИ</a></li>
                    </ul>
                </div>
                <div class="item-menu-footer">
                    <ul class="ul-menu-footer">
                        <li><a class="menu-footer" href="anklets.php">АНКЛЕТИ</a></li>
                        <li><a class="menu-footer" href="necklace.php">КОЛЬЄ</a></li>
                        <li><a class="menu-footer" href="brooches.php">БРОШКИ</a></li>
                        <li><a class="menu-footer" href="cuffs.php">КАФИ</a></li>
                    </ul>
                </div>
                <div class="item-menu-footer">
                    <ul class="ul-menu-footer">
                        <li><a class="menu-footer" href="chokers.php">ЧОКЕРИ</a></li>
                    </ul>
                </div>
            </div>
            <div class="contact-social">
                <p>Telegram</p>
                <p>Instagram</p>
                <p>Twitter</p>
            </div>
        </div>
        <div class="foo-footer">
            <div>
                <p class="email-company">goldendream@gmail.com</p>
            </div>
            <div class="company-2024">
                <p>2024 @ all rights reserved</p>
                <div class="privacy-policy">
                    <div>
                    <p>Privacy Policy</p>
                    </div>
                    <div>
                    <p>Terms of Use</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
    <script>
        function toggleCart() {
            const cartSidebar = document.getElementById('cart-sidebar');
            cartSidebar.classList.toggle('open');
        }

        function updateCartCount() {
            const cartItems = document.querySelectorAll('.cart-item');
            document.getElementById('cart-count').textContent = cartItems.length;
        }

        function updateTotalPrice() {
            const cartItems = document.querySelectorAll('.cart-item');
            let totalPrice = 0;

            cartItems.forEach(item => {
                const quantity = parseInt(item.querySelector('.quantity').textContent);
                const price = parseFloat(item.querySelector('.price-product').textContent);
                totalPrice += quantity * price;
            });

            document.getElementById('total-price').textContent = totalPrice.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateTotalPrice();
            updateCartCount();
        });
    </script>
</body>
</html>

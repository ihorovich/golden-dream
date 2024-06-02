<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

$orderQuery = $db->prepare("SELECT * FROM orders_summary WHERE user_id = :user_id ORDER BY order_date DESC");
$orderQuery->execute(['user_id' => $user_id]);
$orders = $orderQuery->fetchAll(PDO::FETCH_ASSOC);

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    $cartQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id");
    $cartQuery->execute(['user_id' => $user_id]);
    $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cartItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/profile.css">
    <style>
        .orders-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .order {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
            flex-wrap: wrap;
            box-sizing: border-box;
            margin-bottom: 20px;
        }
        .order-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .order-detail-item {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }
        .profile-header h1 {
            font-size: 28px;
            color: #0000ff;
            margin-bottom: 30px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-item img {
            width: 50px;
            height: 50px;
        }
        .order-item-details {
            flex-grow: 1;
            margin-left: 10px;
        }
        .order-item-price {
            margin-left: 10px;
            text-align: left;
        }
        .order-item-name-price {
            display: flex;
            flex-direction: column;
        }
        .empty-orders {
            text-align: left;
            font-size: 18px;
            color: red;
            margin-top: 50px;
        }
        .order-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-transform: uppercase;
            width: 100%;
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
        .text-str {
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
                <div class="shopNameE-COMMERCE">
                    <a class="shopName" href="index.php"><b class="nameE-COMMERCE">GOLDEN </b>DREAM</a>
                </div>
                <div class="shopNameE-COMMERCE">
                    <a class="shopName" href="news.php">НОВИНИ</a>
                </div>
                <div class="item-icon-header">
                    <img class="item-icon user-icon" src="image/user-icon.svg" alt="User">
                    <img class="item-icon" src="image/heart-icon-head.png" alt="Favorites" onclick="window.location.href='favorites.php'">
                    <div class="cart-icon-container">
                        <img class="item-icon cart-icon" src="image/cart_icon.svg" alt="Корзина" onclick="toggleCart()">
                        <span id="cart-count" class="cart-count">0</span>
                    </div>
                </div>

            </div>
            <div class="auth-menu">
                <div class="menu-content">
                    <form class="auth-form login-form active" action="authorizatio.php" method="POST">
                        <h2>Login</h2>
                        <input type="text" name="email" placeholder="Email">
                        <input type="password" name="password" placeholder="Password">
                        <div class="form-footer">
                            <div class="checkbox-div">
                                <label>Remember me</label>
                                <input type="checkbox" name="remember_me">
                            </div>
                            <div>
                                <a href="#" class="forgot-password">Forgot password?</a>
                            </div>
                        </div>
                        <button type="submit">Login</button>
                        <p class="switch-form">Don't have an account? <a href="#" class="register-link">Register</a></p>
                    </form>
                    <form class="auth-form register-form" action="register.php" method="POST">
                        <h2>Register</h2>
                        <input type="text" name="name" placeholder="Name">
                        <input type="text" name="last_name" placeholder="Last Name">
                        <input type="text" name="email" placeholder="Email">
                        <input type="text" name="phone_number" placeholder="Phone Number">
                        <input type="password" name="password" placeholder="Password">
                        <button type="submit">Register</button>
                        <p class="switch-form">Already have an account? <a href="#" class="login-link">Login</a></p>
                    </form>
                </div>
                <span class="close-menu">×</span>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="page-goods-header">
                <div><a href="ring.php">ОБРУЧКИ</a></div>
                <div><a href="pendants.php">ПІДВІСКИ</a></div>
                <div><a href="bracelet.php">БРАСЛЕТИ</a></div>
                <div><a href="earrings.php">СЕРЕЖКИ</a></div>
                <div><a href="anklets.php">АНКЛЕТИ</a></div>
                <div><a href="necklace.php">КОЛЬЄ</a></div>
                <div><a href="brooches.php">БРОШКИ</a></div>
                <div><a href="cuffs.php">КАФИ</a></div>
                <div><a href="chokers.php">ЧОКЕРИ</a></div>
            </div>
        </div>
        <div class="container profile-container">
            <div class="profile-content">
                <div class="profile-header">
                    <h1>МОЯ ІСТОРІЯ ЗАМОВЛЕНЬ</h1>
                </div>
                <div class="profile-info orders-container">
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order">
                                <div class="order-header">
                                    ЗАМОВЛЕНННЯ #<?= htmlspecialchars($order['order_id']) ?> - <?= htmlspecialchars($order['order_date']) ?>
                                </div>
                                <div class="order-detail-item">
                                    <div class="order-details">
                                        <p><strong class="text-str">Ім'я:</strong> <?= htmlspecialchars($order['name']) ?></p>
                                        <p><strong class="text-str">АДРЕСА:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                        <p><strong class="text-str">НОМЕР ТЕЛЕФОНУ:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                                        <p><strong class="text-str">НАЗВА ПОШТИ:</strong> <?= htmlspecialchars($order['shipping_method']) ?></p>
                                        <p><strong class="text-str">СПОСІБ ОПЛАТИ:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                                        <p><strong class="text-str">КОМЕНТАР:</strong> <?= htmlspecialchars($order['comment']) ?></p>
                                    </div>
                                    <div class="order-items">
                                        <?php
                                        $orderItemsQuery = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                                        $orderItemsQuery->execute(['order_id' => $order['order_id']]);
                                        $orderItems = $orderItemsQuery->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <?php foreach ($orderItems as $item): ?>
                                            <?php
                                            $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
                                            $productQuery->execute(['product_id' => $item['product_id']]);
                                            $product = $productQuery->fetch(PDO::FETCH_ASSOC);
                                            $productName = $product['name'];
                                            $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';
                                            ?>
                                            <div class="order-item">
                                                <div>
                                                    <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($productName) ?>" style="width: 150px; height: 200px;">
                                                </div>
                                                <div class="order-item-name-price">
                                                    <div class="order-item-details">
                                                        <span><?= htmlspecialchars($productName) ?></span>
                                                        <span><?= htmlspecialchars($item['quantity']) ?> x $<?= htmlspecialchars($item['unit_price']) ?></span>
                                                    </div>
                                                    <div class="order-item-price">
                                                        $<?= number_format($item['quantity'] * $item['unit_price'], 2) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-orders">Ваша історія замовлень пуста.</p>
                    <?php endif; ?>
                </div>
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
<script src="js/auth-menu.js"></script>
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

    document.getElementById('total-price').textContent = totalPrice.toFixed(2) + '$';
}

function addToCart(productId, event) {
    event.stopPropagation();

    const item = document.querySelector(`.item-goods[data-id="${productId}"]`);
    const name = item.getAttribute('data-name');
    const price = item.getAttribute('data-price');
    const table = item.getAttribute('data-table');
    const image = item.style.backgroundImage.slice(5, -2);

    const existingItem = document.querySelector(`.cart-item[data-id="${productId}"][data-table="${table}"]`);
    if (!existingItem) {
        const cartItems = document.getElementById('cart-items');
        const newItem = document.createElement('div');
        newItem.className = 'cart-item';
        newItem.setAttribute('data-id', productId);
        newItem.setAttribute('data-table', table);
        newItem.innerHTML = `
            <img src="${image}" alt="${name}" style="width: 150px; height: 200px;">
            <div class="detail-description">
                <a class="name-product" href="product-detail.php?product_id=${productId}&table=${table}">${name}</a>
                <div class="cart-item-controls">
                    <button class="btn-plus-minus" onclick="updateQuantity(${productId}, '${table}', -1)">-</button>
                    <span class="quantity">1</span>
                    <button class="btn-plus-minus" onclick="updateQuantity(${productId}, '${table}', 1)">+</button>
                </div>
                <p class="price-product">${price}</п>
                <button class="btn-delete" onclick="removeFromCart(${productId}, '${table}')">Видалити товар</button>
            </div>`;
        cartItems.appendChild(newItem);

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: <?= $user_id ?>,
                product_id: productId,
                product_table: table,
                quantity: 1,
                unit_price: parseFloat(price)
            })
        }).then(response => response.json()).then(data => {
            if (data.success) {
                alert('Товар додано до кошика!');
                updateTotalPrice();
                updateCartCount();
            } else {
                alert('Не вдалося додати товар до кошика.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    } else {
        alert('Товар вже у кошику.');
    }
}

function removeFromCart(productId, table) {
    const cartItem = document.querySelector(`.cart-item[data-id="${productId}"][data-table="${table}"]`);
    cartItem.remove();

    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: <?= $user_id ?>,
            product_id: productId,
            product_table: table
        })
    }).then(response => response.json()).then(data => {
        if (data.success) {
            alert('Товар видалено з кошика!');
            updateTotalPrice();
            updateCartCount();
        } else {
            alert('Не вдалося видалити товар з кошика.');
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}

function updateQuantity(productId, table, change) {
    const cartItem = document.querySelector(`.cart-item[data-id="${productId}"][data-table="${table}"]`);
    const quantityElement = cartItem.querySelector('.quantity');
    let quantity = parseInt(quantityElement.textContent);
    quantity += change;

    if (quantity <= 0) {
        removeFromCart(productId, table);
    } else {
        quantityElement.textContent = quantity;
        updateTotalPrice();
        updateCartCount();

        fetch('update_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: <?= $user_id ?>,
                product_id: productId,
                product_table: table,
                quantity: quantity
            })
        }).then(response => response.json()).then(data => {
            if (!data.success) {
                alert('Не вдалося оновити кількість товару.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }
}

function checkout() {
    window.location.href = 'checkout.php';
}

    document.addEventListener('DOMContentLoaded', function() {
        updateTotalPrice();
        updateCartCount();
    });
</script>
</body>
</html>

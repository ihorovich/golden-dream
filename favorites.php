<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isAuthenticated = isset($_SESSION['isAuthenticated']) ? $_SESSION['isAuthenticated'] : false;

$tables = ['rings', 'pendants', 'bracelet', 'earrings', 'anklets', 'necklace','brooches', 'cuffs', 'chokers']; // Додайте всі таблиці тут

$favorites = [];
if ($user_id) {
    foreach ($tables as $table) {
        try {
            $query = $db->prepare("SELECT f.favorite_id, p.*, :table AS product_table FROM favorites f JOIN $table p ON f.product_id = p.product_id WHERE f.user_id = :user_id AND f.product_table = :table");
            $query->execute(['user_id' => $user_id, 'table' => $table]);
            $favorites = array_merge($favorites, $query->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            echo "Query failed: " . $e->getMessage();
            exit;
        }
    }
}

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
    <title>Favorites</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/favorites_goods.css">
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
                <form class="auth-form login-form active" id="loginForm">
                    <h2 class="login-h2">ВХІД В ПРОФІЛЬ</h2>
                    <div class="div-input">
                        <div class="input-email-pass">
                            <input type="text" name="email" id="email" placeholder="Email">
                            <p class="error-message" id="emailError">Будь ласка, введіть правильну електронну адресу</p>
                        </div>
                        <div class="input-email-pass">
                            <input type="password" name="password" id="password" placeholder="Пароль">
                            <p class="error-message" id="passwordError">Будь ласка, введіть пароль</p>
                        </div>
                    </div>
                    <div class="div-btn">
                        <button type="submit">УВІЙТИ</button>
                        <p class="switch-form">Немає облікового запису? <a href="#" class="register-link">ЗАРЕЄСТРУВАТИСЯ</a></p>
                    </div>
                </form>
                <form class="auth-form register-form" id="registerForm">
                    <h2 class="login-h2">РЕЄСТРАЦІЯ ПРОФІЛЮ</h2>
                    <div class="div-input">
                        <div class="input-reg">
                            <input type="text" name="name" id="name" placeholder="Ім'я">
                            <p class="error-message" id="nameError">Будь ласка, введіть ім'я</p>
                        </div>
                        <div class="input-reg">
                            <input type="text" name="last_name" id="last_name" placeholder="Прізвище">
                            <p class="error-message" id="last_nameError">Будь ласка, введіть прізвище</p>
                        </div>
                        <div class="input-reg">
                            <input type="text" name="email" id="email" placeholder="Email">
                            <p class="error-message" id="emailError">Неправильний формат електронної пошти</p>
                        </div>
                        <div class="input-reg">
                            <input type="text" name="phone_number" id="phone_number" placeholder="Номер телефону">
                            <p class="error-message" id="phone_numberError">Неправильний формат номера телефону</p>
                        </div>
                        <div class="input-reg">
                            <input type="password" name="password" id="password" placeholder="Пароль">
                            <p class="error-message" id="passwordError">Пароль повинен містити принаймні 6 символів</p>
                        </div>
                    </div>
                    <div class="div-btn">
                        <button type="submit">ЗАРЕЄСТРУВАТИСЯ</button>
                        <p class="switch-form">Вже маєте обліковий запис? <a href="#" class="login-link">УВІЙТИ</a></p>
                    </div>
                </form>
            </div>
            <span class="close-menu"><img src="image/close-btn.svg" alt=""></span>
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
        <nav class="profile-menu">
            <div class="favorites-header">
                <h1 class="favotites-text">УЛЮБЛЕНІ ТОВАРИ</h1>
            </div>
            <div>
                <ul class="ul-menu">
                    <?php if ($isAuthenticated): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="orders.php">My Orders</a></li>
                    <?php endif; ?>
                    <li><a href="favorites.php">Favorites</a></li>
                </ul>
            </div>
        </nav>

        <div class="profile-content">
            <div class="favorites-container">
                <div class="set-of-goods-container">
                    <?php if (empty($favorites)): ?>
                    <p class="missing-goods">Улюблених товарів немає</p>
                    <?php else: ?>
                    <?php foreach ($favorites as $product) : ?>
                    <?php
                        $name = htmlspecialchars($product['name']);
                        $description = htmlspecialchars($product['description']);
                        $price = htmlspecialchars($product['price']);
                        $image1Data = $product['image1'];
                        $product_table = htmlspecialchars($product['product_table']);
                        if ($image1Data) {
                            $image1 = 'data:image/png;base64,' . base64_encode($image1Data);
                        } else {
                            $image1 = 'default-image1.png';
                        }
                    ?>
                    <div class="item-goods" data-id="<?= $product['product_id'] ?>" data-name="<?= $name ?>"
                        data-price="<?= $price ?>$" data-description="<?= $description ?>" data-table="<?= $product_table ?>"
                        style="background-image: url('<?= $image1 ?>');" onmouseenter="showAddGoods(this)" onmouseleave="hideAddGoods(this)">
                        <div class="good-details">
                            <h4 class="goods-name"><?= $name ?></h4>
                            <p class="goods-description hover-underline"
                                onclick="redirectToProductDetailPage(<?= $product['product_id'] ?>, '<?= $product_table ?>')"><?= $description ?>
                            </p>
                            <p class="goods-price"><?= $price ?>$</p>
                            <div class="add-goods hidden">
                                <div><img class="heart-icon" src="image/heart-filled-icon.png" alt="Іконка серця"
                                        onclick="removeFromFavorites(<?= $product['product_id'] ?>, '<?= $product_table ?>', <?= $user_id ?>)"></div>
                                <div class="btn-div-add"><button>Додати до кошика</button></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
<script src="js/header.js"></script>
<script src="js/auth-menu.js"></script>
<script>
        function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    cartSidebar.classList.toggle('open');
}
    function redirectToProductDetailPage(productId, table) {
        window.location.href = `product-detail.php?product_id=${productId}&table=${table}`;
    }

    function removeFromFavorites(productId, table, userId) {
        fetch('remove_from_favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                table: table,
                user_id: userId
            })
        }).then(response => response.json()).then(data => {
            if (data.success) {
                alert('Товар видалено з улюблених!');
                // Видалити товар з DOM
                const itemGoods = document.querySelector(`.item-goods[data-id="${productId}"]`);
                itemGoods.remove();
            } else {
                alert('Не вдалося видалити товар з улюблених.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.item-goods').forEach(item => {
            item.addEventListener('mouseenter', function() {
                showAddGoods(this);
            });
            item.addEventListener('mouseleave', function() {
                hideAddGoods(this);
            });
        });
    });

    function showAddGoods(element) {
        const addGoods = element.querySelector('.add-goods');
        addGoods.classList.remove('hidden');
    }

    function hideAddGoods(element) {
        const addGoods = element.querySelector('.add-goods');
        addGoods.classList.add('hidden');
    }
</script>
<script>

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

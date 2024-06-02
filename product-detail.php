<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$table = isset($_GET['table']) ? htmlspecialchars($_GET['table']) : '';

if ($product_id > 0 && $table) {
    try {
        $query = $db->prepare("SELECT * FROM $table WHERE product_id = :product_id");
        $query->execute(['product_id' => $product_id]);
        $product = $query->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $name = htmlspecialchars($product['name']);
            $description = htmlspecialchars($product['description']);
            $price = htmlspecialchars($product['price']);
            $details_and_care = htmlspecialchars($product['details_and_care']);
            $size = htmlspecialchars($product['size']);
            $article = htmlspecialchars($product['article']);
            $brand = htmlspecialchars($product['brand']);
            $image1Data = $product['image1'];
            $image2Data = $product['image2'];

            if ($image1Data) {
                $image1 = 'data:image/png;base64,' . base64_encode($image1Data);
            } else {
                $image1 = 'default-image1.png';
            }

            if ($image2Data) {
                $image2 = 'data:image/png;base64,' . base64_encode($image2Data);
            } else {
                $image2 = 'default-image2.png';
            }

            // Format the details and care section
            $formatted_details = preg_replace('/(КОЛІР|СКЛАД|ВИРОБНИК):/', '<strong>$1:</strong>', $details_and_care);
            $formatted_details = nl2br($formatted_details); // Ensure line breaks are preserved
        } else {
            echo "Product not found.";
            exit;
        }
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        echo "Query failed: " . $e->getMessage();
        exit;
    }
} else {
    echo "Invalid product ID or table.";
    exit;
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
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Деталі продукту</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/ring.css">
    <link rel="stylesheet" href="css/product-detail.css">
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
    </header>
        <div class="product-detail-container">
            <div class="product-image">
                <div class="details-image">
                    <div><img height="130px" src="<?= $image1 ?>" alt="Зображення продукту 1"></div>
                    <div><img height="130px" src="<?= $image2 ?>" alt="Зображення продукту 2"></div>
                </div>
                <div><img id="product-image" src="<?= $image1 ?>" alt="Зображення продукту"></div>
            </div>
            <div class="product-info">
                <h1 class="product-name"><?= $name ?></h1>
                <div class="price-size">
                    <p class="product-price"><?= $price ?>$</p>
                    <p class="product-size">РОЗМІР <?= $size ?></p>
                </div>
                <button class="add-to-cart" onclick="addToCart(<?= $product_id ?>)">ДОДАТИ В КОШИК</button>
                <div class="brend">
                    <p class="articul">АРТИКУЛ <?= $article ?></p>
                    <p>БРЕНД <?= $brand ?></p>
                </div>
                <div>
                    <hr class="line-faq">
                    <div class="faq-item">
                        <div class="question">
                            <span class="question-text">ОПИС</span>
                            <span class="toggle-btn">▼</span>
                        </div>
                        <div class="answer">
                            <p class="answer-p-01">ВІДПОВІДЬ:</p>
                            <p id="product-description" class="answer-p-02"><?= $description ?></p>
                        </div>
                    </div>
                    <hr class="line-faq">
                    <div class="faq-item">
                        <div class="question">
                            <span class="question-text">ДЕТАЛІ ТА ДОГЛЯД</span>
                            <span class="toggle-btn">▼</span>
                        </div>
                        <div class="answer">
                            <p class="answer-p-01">ВІДПОВІДЬ:</p>
                            <p id="product-description" class="answer-p-02"><?= nl2br($formatted_details) ?></p>
                        </div>
                    </div>
                    <hr class="line-faq">
                    <div class="faq-item">
                        <div class="question">
                            <span class="question-text">ДОСТАВКА ТА ПОВЕРНЕННЯ</span>
                            <span class="toggle-btn">▼</span>
                        </div>
                        <div class="answer">
                            <p class="answer-p-01">ВІДПОВІДЬ:</p>
                            <p id="product-description" class="answer-p-02">Доставка замовлень по Україні здійснюється компанією "Нова Пошта". Міжнародна доставка здійснюється компанією "Укрпошта". Вартість доставки розраховується згідно тарифів перевізника. Відправка замовлення відбувається протягом 2-6 днів з дня підтвердження замовлення.

Повернення товарів здійснюється протягом 14 днів від дати отримання замовлення. Детальну інформацію шукайте в розділі "Доставка" та "Повернення</p>
                        </div>
                    </div>
                </div>
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
                        <button class="btn-plus-minus" onclick="updateQuantity(<?= $item['product_id'] ?>, '<?= $item['product_table'] ?>', -1)">-</button>
                        <span class="quantity"><?= $item['quantity'] ?></span>
                        <button class="btn-plus-minus" onclick="updateQuantity(<?= $item['product_id'] ?>, '<?= $item['product_table'] ?>', 1)">+</button>
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
<script src="js/auth-menu.js"></script>
<script>
    function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    cartSidebar.classList.toggle('open');
}
    document.addEventListener('DOMContentLoaded', function () {
    const thumbnails = document.querySelectorAll('.details-image img');
    const mainImage = document.getElementById('product-image');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function () {
            mainImage.src = thumbnail.src;
        });
    });

    const questions = document.querySelectorAll('.question');
    questions.forEach(question => {
        question.addEventListener('click', function () {
            const parent = this.parentNode;
            if (parent.classList.contains('active')) {
                parent.classList.remove('active');
            } else {
                const allActiveItems = document.querySelectorAll('.faq-item.active');
                allActiveItems.forEach(item => {
                    item.classList.remove('active');
                });
                parent.classList.add('active');
            }
        });
    });

    updateTotalPrice();
    updateCartCount();
});
</script>
    <script>

function addToCart(productId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart_detail.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Product added to cart successfully');
                updateCartUI(response.cartItems);
                updateCartCountUI(response.cartCount); // Оновлення лічильника кошика
            } else {
                alert('Failed to add product to cart: ' + response.message);
            }
        } else {
            alert('Failed to add product to cart');
        }
    };
    xhr.send('product_id=' + productId + '&product_table=<?= $table ?>');
}


function updateCartUI(cartItems) {
    const cartItemsContainer = document.getElementById('cart-items');
    cartItemsContainer.innerHTML = '';
    cartItems.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.setAttribute('data-id', item.product_id);
        cartItem.setAttribute('data-table', item.product_table);
        cartItem.innerHTML = `
            <div>
                <img src="${item.image}" alt="${item.name}" style="width: 150px; height: 200px;">
            </div>
            <div class="detail-description">
                <a class="name-product" href="product-detail.php?product_id=${item.product_id}&table=${item.product_table}">${item.name}</a>
                <div class="cart-item-controls">
                    <button class="btn-plus-minus" onclick="updateQuantity(${item.product_id}, '${item.product_table}', -1)">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="btn-plus-minus" onclick="updateQuantity(${item.product_id}, '${item.product_table}', 1)">+</button>
                </div>
                <p class="price-product">${item.unit_price}$</p>
                <button class="btn-delete" onclick="removeFromCart(${item.product_id}, '${item.product_table}')">Видалити товар</button>
            </div>
        `;
        cartItemsContainer.appendChild(cartItem);
    });
    updateTotalPrice();
    updateCartCount();
}

function updateCartCountUI(cartCount) {
    document.getElementById('cart-count').textContent = cartCount;
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

        fetch('update_quantity_detail.php', {
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
    </script>
</body>
</html>

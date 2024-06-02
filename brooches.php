<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

// Filters
$price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 10;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 1000;
$brand = isset($_GET['brand']) ? $_GET['brand'] : [];
$color = isset($_GET['color']) ? $_GET['color'] : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

$filters = [];
$sql = "SELECT * FROM brooches WHERE price BETWEEN :price_min AND :price_max";

$filters[':price_min'] = $price_min;
$filters[':price_max'] = $price_max;

if (!empty($brand)) {
    $sql .= " AND brand IN (" . implode(', ', array_map(function($key) {
        return ":brand_$key";
    }, array_keys($brand))) . ")";
    foreach ($brand as $key => $value) {
        $filters[":brand_$key"] = $value;
    }
}

if (!empty($color)) {
    $color_conditions = [];
    foreach ($color as $key => $value) {
        $color_conditions[] = "details_and_care LIKE :color_$key";
        $filters[":color_$key"] = "%Колір: $value%";
    }
    $sql .= " AND (" . implode(' OR ', $color_conditions) . ")";
}

if ($sort == 'asc') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort == 'desc') {
    $sql .= " ORDER BY price DESC";
}

try {
    $query = $db->prepare($sql);
    $query->execute($filters);
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo "Query failed: " . $e->getMessage();
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$favorites = [];
if ($user_id) {
    $favoritesQuery = $db->prepare("SELECT product_id FROM favorites WHERE user_id = :user_id AND product_table = 'brooches'");
    $favoritesQuery->execute(['user_id' => $user_id]);
    $favorites = $favoritesQuery->fetchAll(PDO::FETCH_COLUMN);

    $cartQuery = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id");
    $cartQuery->execute(['user_id' => $user_id]);
    $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);
}else {
    $cartItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-COMMERCE WEBSITE - Brooches</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/ring.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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

      <div class="content">
        <!-- Filter Form -->
        <section class="filter-section">
          <form class="filter-section-form" method="GET" action="brooches.php">
          <h3 class="filter-h3">Фільтри</h3>
            <div class="filter-group">
              <label for="price_min">Ціна від:</label>
              <input class="input-price" type="number" id="price_min" name="price_min" value="<?= htmlspecialchars($price_min) ?>">
            </div>
            <div class="filter-group">
              <label for="price_max">Ціна до:</label>
              <input class="input-price" type="number" id="price_max" name="price_max" value="<?= htmlspecialchars($price_max) ?>">
            </div>
            <div class="filter-group">
              <label>Бренд:</label>
              <div><input type="checkbox" name="brand[]" value="KOLOSOVA" <?= in_array('KOLOSOVA', $brand) ? 'checked' : '' ?>> KOLOSOVA</div>
            </div>
            <div class="filter-group">
              <label>Колір:</label>
              <div><input type="checkbox" name="color[]" value="Срібний" <?= in_array('Срібний', $color) ? 'checked' : '' ?>> Срібний</div>
              <div><input type="checkbox" name="color[]" value="Золотий" <?= in_array('Золотий', $color) ? 'checked' : '' ?>> Золотий</div>
              <div><input type="checkbox" name="color[]" value="Бежевий" <?= in_array('Бежевий', $color) ? 'checked' : '' ?>> Бежевий</div>
              <div><input type="checkbox" name="color[]" value="Молочний" <?= in_array('Молочний', $color) ? 'checked' : '' ?>> Молочний</div>
              <div><input type="checkbox" name="color[]" value="Чорний" <?= in_array('Чорний', $color) ? 'checked' : '' ?>> Чорний</div>
            </div>
            <div class="filter-group">
              <label for="sort">Сортувати:</label>
              <select class="sort" id="sort" name="sort">
                <option value="default" <?= $sort == 'default' ? 'selected' : '' ?>>За замовчуванням</option>
                <option value="asc" <?= $sort == 'asc' ? 'selected' : '' ?>>За зростаючою ціною</option>
                <option value="desc" <?= $sort == 'desc' ? 'selected' : '' ?>>За спадаючою ціною</option>
              </select>
            </div>
            <div class="btn-filter">
            <button class="resetmit-btn" type="button" onclick="resetFilters('brooches.php')">ОЧИСТИТИ ФІЛЬТРИ</button>
                <button class="submit-btn" type="submit">ЗАСТОСУВАТИ ФІЛЬТРИ</button>
            </div>
          </form>
        </section>

        <section class="set-of-goods-wrapper">
          <div class="set-of-goods-container">
            <?php if (empty($products)): ?>
              <p class="missing-goods">Немає продуктів з таким фільтром</p>
            <?php else: ?>
              <?php foreach ($products as $product) : ?>
              <?php
                  $name = htmlspecialchars($product['name']);
                  $description = htmlspecialchars($product['description']);
                  $price = htmlspecialchars($product['price']);
                  $image1Data = $product['image1'];
                  $isFavorite = in_array($product['product_id'], $favorites);
                  if ($image1Data) {
                      $image1 = 'data:image/png;base64,' . base64_encode($image1Data);
                  } else {
                      $image1 = 'default-image1.png';
                  }
              ?>
              <div class="item-goods" data-id="<?= $product['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= $price ?>$" data-description="<?= $description ?>" data-table="brooches" style="background-image: url('<?= $image1 ?>');" onmouseenter="showAddGoods(this)" onmouseleave="hideAddGoods(this)">
                  <div class="good-details">
                      <h4 class="goods-name"><?= $name ?></h4>
                      <p class="goods-description hover-underline" onclick="redirectToProductDetailPage(<?= $product['product_id'] ?>, 'brooches')"><?= $description ?></p>
                      <p class="goods-price"><?= $price ?>$</p>
                      <div class="add-goods hidden">
                          <div>
                              <img class="heart-icon <?= $isFavorite ? 'favorited' : '' ?>" src="image/<?= $isFavorite ? 'heart-filled-icon.png' : 'heart-icon.png' ?>" alt="Heart Icon" onclick="<?= $isFavorite ? 'removeFromFavorites' : 'addToFavorites' ?>(<?= $product['product_id'] ?>, 'brooches', <?= $user_id ?>)">
                          </div>
                          <div class="btn-div-add">
                                <button onclick="addToCart(<?= $product['product_id'] ?>, event)">Додати до кошика</button>
                            </div>
                      </div>
                  </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
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
  <script src="js/auth-menu.js"></script>
  <script src="js/show-hide-goods.js"></script>
  <script src="js/reset-filters.js?v=1.0"></script>

  <script src="js/redirect-to-product.js"></script>
  <script src="js/opening-basket.js"></script>
  <script>


document.addEventListener('DOMContentLoaded', function() {
      const userId = <?= json_encode($user_id) ?>;

      function addToFavorites(productId, table) {
        console.log(`Adding to favorites: Product ID ${productId}, Table ${table}, User ID ${userId}`);
        fetch('add_to_favorites.php', {
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
            alert('Product added to favorites!');
            const heartIcon = document.querySelector(`.item-goods[data-id="${productId}"] .heart-icon`);
            heartIcon.src = 'image/heart-filled-icon.png';
            heartIcon.classList.add('favorited');
            heartIcon.onclick = () => removeFromFavorites(productId, table);
          } else {
            console.error(`Failed to add to favorites: ${data.message}`);
            alert(`Failed to add product to favorites: ${data.message}`);
          }
        }).catch(error => {
          console.error('Error:', error);
        });
      }

      function removeFromFavorites(productId, table) {
        console.log(`Removing from favorites: Product ID ${productId}, Table ${table}, User ID ${userId}`);
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
            alert('Product removed from favorites!');
            const heartIcon = document.querySelector(`.item-goods[data-id="${productId}"] .heart-icon`);
            heartIcon.src = 'image/heart-icon.png';
            heartIcon.classList.remove('favorited');
            heartIcon.onclick = () => addToFavorites(productId, table);
          } else {
            console.error(`Failed to remove from favorites: ${data.message}`);
            alert(`Failed to remove product from favorites: ${data.message}`);
          }
        }).catch(error => {
          console.error('Error:', error);
        });
      }

      document.querySelectorAll('.item-goods').forEach(item => {
        const heartIcon = item.querySelector('.heart-icon');
        heartIcon.addEventListener('click', function() {
          const productId = item.dataset.id;
          const table = item.dataset.table;
          if (heartIcon.classList.contains('favorited')) {
            removeFromFavorites(productId, table);
          } else {
            addToFavorites(productId, table);
          }
        });

        item.addEventListener('mouseenter', function() {
          showAddGoods(this);
        });

        item.addEventListener('mouseleave', function() {
          hideAddGoods(this);
        });
      });
    });
    function updateCartCount() {
        const cartItems = document.querySelectorAll('.cart-item');
        document.getElementById('cart-count').textContent = cartItems.length;
    }

    function updateTotalPrice() {
        const cartItems = document.querySelectorAll('.cart-item');
        let totalPrice = 0;

        cartItems.forEach(item => {
            const quantity = parseInt(item.querySelector('.quantity').textContent);
            const price = parseFloat(item.querySelector('p').textContent);
            totalPrice += quantity * price;
        });

        document.getElementById('total-price').textContent = totalPrice.toFixed(2);
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
                    <p class="price-product">${price}</p>
                    <button class="btn-delete" onclick="removeFromCart(${productId}, '${table}')">ВИДАЛИТИ ТОВАР</button>
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
    <script>
    function showError(field, message) {
        document.getElementById(field).classList.add('error');
        var errorMessage = document.getElementById(field + 'Error');
        errorMessage.innerText = message;
        errorMessage.style.display = 'block';
    }

    function clearErrors() {
        var errorFields = document.querySelectorAll('.error');
        errorFields.forEach(function (field) {
            field.classList.remove('error');
        });
        var errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(function (message) {
            message.style.display = 'none';
        });
    }

    document.getElementById('registerForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this);

            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearErrors();
                if (data.success) {
                    window.location.href = 'profile.php';
                } else {
                    for (const [field, message] of Object.entries(data.errors)) {
                        showError(field, message);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });

    document.getElementById('loginForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = new FormData(this);

        fetch('authorizatio.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearErrors();
            if (data.success) {
                if (data.isAdmin) {
                    window.location.href = 'admin.php';
                } else {
                    window.location.href = 'profile.php';
                }
            } else {
                for (const [field, message] of Object.entries(data.errors)) {
                    showError(field, message);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
        document.querySelector('.user-icon').addEventListener('click', function() {
            <?php if (isset($_SESSION['isAuthenticated'])): ?>
                <?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true): ?>
                    window.location.href = 'admin.php';
                <?php else: ?>
                    window.location.href = 'profile.php';
                <?php endif; ?>
            <?php else: ?>
                document.querySelector('.auth-menu').classList.add('open');
            <?php endif; ?>
        });
    </script>
</body>
</html>

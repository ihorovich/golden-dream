<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

try {
    $query = $db->prepare("SELECT * FROM rings LIMIT 4");
    $query->execute();
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo "Query failed: " . $e->getMessage();
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$favorites = [];
if ($user_id) {
    $favoritesQuery = $db->prepare("SELECT product_id FROM favorites WHERE user_id = :user_id");
    $favoritesQuery->execute(['user_id' => $user_id]);
    $favorites = $favoritesQuery->fetchAll(PDO::FETCH_COLUMN);

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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-COMMERCE WEBSITE</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/index.css">
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
                <img class="item-icon user-icon" src="image/user-icon.svg" alt="Користувач">
                <img class="item-icon" src="image/heart-icon-head.png" alt="Favorites" onclick="redirectToFavorites()">
                <div class="cart-icon-container">
                    <img class="item-icon cart-icon" src="image/cart_icon.svg" alt="Корзина" onclick="toggleCart(event)">
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
    <section class="main-section">
        <div class="container">
            <div class="container-item">
                <div class="about-company">
                    <p class="about-company-p">Золота Мрія - це більше, ніж ювелірна компанія.</p>
                </div>
                <div class="what-of-company">
                    <h1 class="h1-name">ДИЗАЙНЕРСЬКІ ЮВЕЛІРНІ <br /> ВИРОБИ ЗІ СРІБЛА ТА ЗОЛОТА</h1>
                </div>
                <div class="btn-description">
                    <a href="#products-section" id="scrollToProducts" class="new-collection">КУПИТИ ТОВАР</a>
                    <div class="p-description">
                        <p class="about-text-p"> Відкрийте для себе наші
                            обручки, завушниці, намистини, браслети та інші
                            унікальні прикраси, які додають блиск та вишуканість
                            до вашого образу.
                        </p>
                        <p class="about-text-p-2"> Ми вибираємо найчистіші коштовності, вдосконалюємо техніку обробки і втілюємо
                            у
                            кожному виробі філософію розкіші та гармонії.<br />
                            <br /> Наша мета - не тільки дотримуватися вищих стандартів якості, а й створювати шедеври, які
                            запам'ятовуються навіть у золотому віці вишуканості.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-section">
        <div class="container">
            <div class="about-text">
                <img width="300" src="image/history-01.png" alt="Про нас">
                <div class="text-section-1" id="history-section">
                    <h2 class="about-h2">ІСТОРІЯ СТВОРЕННЯ</h2>
                    <div>
                        <h3 class="dosvid">Великий досвід</h3>
                        <p class="history-descr-p-1">Заснована в <span>2005 році</span>, компанія виникла з бажання поринути у
                            світ
                            найвищого
                            рівня мистецтва та створення ексклюзивних прикрас, які будуть виражати найглибші почуття та емоції
                            своїх
                            власників.</p>
                    </div>
                </div>
                <div class="text-section-3">
                    <p class="num-section">01</p>
                </div>
            </div>
            <div class="facts">
                <div class="facts-text">
                    <div class="facts-item">
                        <div class="facts-item-div">
                            <h4 class="facts-item-h4">20</h4>
                            <p class="facts-item-p">РОКІВ ДОСВІДУ</p>
                        </div>
                        <div>
                            <h4 class="facts-item-h4">500</h4>
                            <p class="facts-item-p">УНІКАЛЬНИХ ДИЗАЙНІВ ЩОРІЧНО</p>
                        </div>
                    </div>
                    <div>
                        <div class="facts-item-div">
                            <h4 class="facts-item-h4">1000</h4>
                            <p class="facts-item-п">НАГОРОД ЗА МАЙСТЕРНІСТЬ</p>
                        </div>
                        <div>
                            <h4 class="facts-item-h4">20</h4>
                            <p class="facts-item-p">РОКІВ ДОСВІДУ</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="international-exhibitions">
                <p class="descr-international-exhibitions"> Ми брали участь у більше ніж 20 міжнародних виставках, де наші
                    творіння
                    вразили шанувальників та
                    фахівців із різних куточків світу.</p>

            </div>
        </div>
    </section>
    <section class="goods-section">
        <div class="container">
            <div class="h2-goods-container" id="products-section">
                <h2 class="goods-h2">ТОВАРИ</h2>
                <p class="goods-num-section">02</p>
            </div>
            <div class="what-goods-container">
                <h3 class="what-goods">Обручки</h3>
                <button class="btn-more" onclick="window.location.href='ring.php'">Подивитися більше</button>
            </div>
            <div class="set-of-goods-container">
                <?php if (empty($products)): ?>
                <p class="missing-goods">Товари відсутні.</p>
                <?php else: ?>
                <?php foreach ($products as $product) : ?>
                <?php
                $name = htmlspecialchars($product['name']);
                $description = htmlspecialchars($product['description']);
                $price = htmlspecialchars($product['price']);
                $image1Data = $product['image1'];
                $isFavorite = in_array($product['product_id'], $favorites); // Перевірка, чи є товар у списку улюблених
                if ($image1Data) {
                    $image1 = 'data:image/png;base64,' . base64_encode($image1Data);
                } else {
                    $image1 = 'default-image1.png';
                }
                ?>
                <div class="item-goods" data-id="<?= $product['product_id'] ?>" data-name="<?= $name ?>" data-price="<?= $price ?>$" data-description="<?= $description ?>" data-table="rings" style="background-image: url('<?= $image1 ?>');" onmouseenter="showAddGoods(this)" onmouseleave="hideAddGoods(this)">
                    <div class="good-details">
                        <h4 class="goods-name"><?= $name ?></h4>
                        <p class="goods-description hover-underline" onclick="redirectToProductDetailPage(<?= $product['product_id'] ?>, 'rings')"><?= $description ?></p>
                        <p class="goods-price"><?= $price ?>$</p>
                        <div class="add-goods hidden">
                            <div>
                                <img class="heart-icon" src="image/<?= $isFavorite ? 'heart-filled-icon.png' : 'heart-icon.png' ?>" alt="Іконка серця" onclick="<?= $isFavorite ? 'removeFromFavorites' : 'addToFavorites' ?>(<?= $product['product_id'] ?>, 'rings', <?= $user_id ?>)">
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
        </div>
    </section>

    <section class="quality-guarantee-section">
        <div class="container">
          <div class="h2-guarantee-container">
            <h2 class="guarantee-h2">ГАРАНТІЯ ЯКОСТІ</h2>
            <div class="text-section-3">
              <p class="num-section">03</p>
            </div>
          </div>
          <div class="img-guarantee">
            <img src="image/guarantee-01.svg" alt="ГАРАНТІЯ ЯКОСТІ">
            <img src="image/guarantee-02.svg" alt="ГАРАНТІЯ ЯКОСТІ">
          </div>
          <div class="guarantee-div">
            <div class="desc-guarantee">
              <h4 class="guarantee-h4">Наша гарантія якості</h4>
              <p class="guarantee-text">Наша гарантія якості охоплює кожен етап виробництва - від концепції до
                виготовлення, відбираючи тільки
                найкращі матеріали та застосовуючи технології, що відповідають найсучаснішим тенденціям у світі
                ювелірного мистецтва.</p>
            </div>
            <div class="hr-guarantee">
              <span class="hr-01"></span>
              <span class="hr-02"></span>
            </div>
          </div>
          <div class="guarantee-facts">
            <div>
              <h4 class="what-guarantee">ЯКА ГАРАНТІЯ?</h4>
              <a href="#products-section" id="scrollToProducts" class="make-purchase">Здійснити покупку</a>
            </div>
            <div class="desc-facts-guarantee">
              <div>
                <h4 class="h4-fact-guarantee">Технології Найвищого Рівня</h4>
                <p class="p-fact-guarantee">Кожна дрібничка це мистецтво. Відбір матеріалів проводиться з особливою
                  увагою до деталей,
                  забезпечуючи, що кожен елемент виглядає не лише як прикраса, але і як вираз вишуканості та
                  винятковості.</p>
              </div>
              <div class="div-fact-guarantee">
                <h4 class="h4-fact-guarantee">Найвища Якість Кожної Деталі</h4>
                <p class="p-fact-guarantee">Наша майстерня використовує передові технології у виробництві, поєднуючи
                  традиційні ремесла з
                  інноваційними методами. Це дозволяє нам досягати найвищої якості та точності в кожному етапі творчого
                  процесу.</p>
              </div>
            </div>
          </div>
        </div>
        </div>
    </section>
    <section class="frequently-asked-questions-section">
        <div class="container">
          <div class="h2-frequently-asked-questions-container">
            <h2 class="frequently-asked-questions-h2">ЧАСТІ ЗАПИТАННЯ</h2>
            <p class="frequently-num-section">04</p>
          </div>
          <div class="faq-list-item">
            <div class="faq-list">
              <hr class="line-faq">
              <div class="faq-item">
                <div class="question">
                  <span class="question-text">Як я можу замовити індивідуальний дизайн?</span>
                  <span class="toggle-btn">▼</span>
                </div>
                <div class="answer">
                  <p class="answer-p-01">ВІДПОВІДЬ:</p>
                  <p class="answer-p-02">Замовлення індивідуального дизайну на нашому сайті - це простий і захоплюючий
                    процес, який дозволяє вам отримати прикрасу вашої мрії. Спочатку зв'яжіться з нами, розкажіть про
                    ваші бажання та побажання. Після консультації з дизайнером, ми розпочнемо розробку вашого
                    унікального дизайну та надамо вам можливість підтвердити його перед виробництвом. Потім мивиготовимо вашу прикрасу та доставимо вам її, гарантуючи вам задоволення від унікального та
                    неповторного образу.</p>
                </div>
              </div>
              <hr class="line-faq">
              <div class="faq-item">
                <div class="question">
                  <span class="question-text">Чому Золота Мрія - це особливий вибір?</span>
                  <span class="toggle-btn">▼</span>
                </div>
                <div class="answer">
                  <p class="answer-p-01">ВІДПОВІДЬ:</p>
                  <p class="answer-p-02">Золота Мрія - це не просто бренд, це спеціальне поєднання елегантності та
                    тепла, яке робить наші прикраси особливими та унікальними. Кожна прикраса, створена під брендом
                    Золоте Диво, втілення в собі не лише вишуканого дизайну, але й величезного обсягу душі та таланту
                    наших майстрів. Вони прагнуть робити кожну деталь унікальною та неповторною, вкладаючи в неї
                    частинку свого серця та професійної майстерності. Кожна прикраса Золотого Дива має свою власну
                    історію, яка починається з вибору матеріалів та завершується чудовим виробом, що підкреслює ваш
                    стиль та елегантність.</p>
                </div>
              </div>
              <hr class="line-faq">
              <div class="faq-item">
                <div class="question">
                  <span class="question-text">Чи можливо повернути або обміняти прикрасу?</span>
                  <span class="toggle-btn">▼</span>
                </div>
                <div class="answer">
                  <p class="answer-p-01">ВІДПОВІДЬ:</p>
                  <p class="answer-p-02">Звичайно! Ми розуміємо, що іноді можуть виникати ситуації, коли вам необхідно
                    повернути або обміняти прикрасу. У нас діє гнучка політика повернення та обміну, щоб забезпечити
                    вашу задоволеність та впевненість у покупці. Якщо ви не задоволені придбаною прикрасою, ви можете
                    повернути її протягом визначеного періоду після отримання замовлення. Зверніться до нашої служби
                    підтримки клієнтів для отримання всієї необхідної інформації та інструкцій з повернення або обміну.
                  </p>
                </div>
              </div>
              <hr class="line-faq">
              <div class="faq-item">
                <div class="question">
                  <span class="question-text">чи гарантована якість матеріалів?</span>
                  <span class="toggle-btn">▼</span>
                </div>
                <div class="answer">
                  <p class="answer-p-01">ВІДПОВІДЬ:</p>
                  <p class="answer-p-02">
                    Абсолютно! Ми пишаємося нашою високою якістю матеріалів, використовуваних у виробництві наших
                    прикрас. Кожен елемент нашої продукції ретельно відібраний з метою забезпечення якості та
                    довговічності. Ми працюємо лише з надійними постачальниками, які гарантують якість матеріалів, таких
                    як дорогоцінні метали, дорогоцінні та напівдорогоцінні камені, а також інші компоненти, що
                    використовуються у виробництві прикрас.</p>
                </div>
              </div>
              <hr class="line-faq">
              <div class="faq-item">
                <div class="question">
                  <span class="question-text">Чи здійснюється міжнародна доставка?</span>
                  <span class="toggle-btn">▼</span>
                </div>
                <div class="answer">
                  <p class="answer-p-01">ВІДПОВІДЬ:</p>
                  <p class="answer-p-02">Так, ми надаємо міжнародну доставку для наших клієнтів з усього світу! Наша
                    компанія працює з надійними логістичними партнерами, щоб забезпечити швидку та надійну доставку
                    наших прикрас у будь-яку точку світу. При оформленні замовлення ви можете вибрати зручний для вас метод доставки та вказати бажану адресу доставки. Ми пропонуємо різноманітні варіанти доставки з
                    різною швидкістю та вартістю, щоб ви могли обрати оптимальний варіант, який відповідає вашим
                    потребам та бюджету.</p>
                </div>
              </div>
              <hr class="line-faq-end">
              <!-- Додайте інші запитання і відповіді за аналогією -->
            </div>
          </div>
        </div>
    </section>
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
<script src="js/redirect-to-product.js"></script>
<script src="js/opening-basket.js"></script>
<script src="js/quaestion.js"></script>
<script>
    function addToFavorites(productId, table, userId) {
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
                alert('Товар додано до улюблених!');
                const heartIcon = document.querySelector(`.item-goods[data-id="${productId}"] .heart-icon`);
                heartIcon.src = 'image/heart-filled-icon.png';
                heartIcon.onclick = () => removeFromFavorites(productId, table, userId);
            } else {
                alert('Не вдалося додати товар до улюблених.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
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
                const heartIcon = document.querySelector(`.item-goods[data-id="${productId}"] .heart-icon`);
                heartIcon.src = 'image/heart-icon.png';
                heartIcon.onclick = () => addToFavorites(productId, table, userId);
            } else {
                alert('Не вдалося видалити товар з улюблених.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
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
                    <a class="name-product" href="product-detail.php?product_id=${productId}&table=${table}">${name}</а>
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
</script>
<script>
        document.getElementById('scrollToProducts').addEventListener('click', function(event) {
            event.preventDefault();
            document.querySelector('#products-section').scrollIntoView({
                behavior: 'smooth'
            });
        });
</script>
<script>
        document.getElementById('scrollToHistory').addEventListener('click', function(event) {
            event.preventDefault();
            document.querySelector('#history-section').scrollIntoView({
                behavior: 'smooth'
            });
        });
</script>

<script>
    function redirectToFavorites() {
        window.location.href = 'favorites.php';
    }
</script>
</body>
</html>

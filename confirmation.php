<?php
session_start();

include 'db_connection.php'; // Підключення до бази даних

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if ($order_id) {
    $orderQuery = $db->prepare("SELECT * FROM orders_summary WHERE order_id = :order_id");
    $orderQuery->execute(['order_id' => $order_id]);
    $orderDetails = $orderQuery->fetch(PDO::FETCH_ASSOC);

    $itemsQuery = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
    $itemsQuery->execute(['order_id' => $order_id]);
    $orderItems = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
} else {
    $orderDetails = null;
    $orderItems = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .confirmation-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 1400px;
            margin-top: 40px;
            margin-bottom: 40px;
            background: #fff;
            padding: 100px 20px;
            align-items: center; /* Центрує дочірні елементи по горизонталі */
            justify-content: center; /* Центрує дочірні елементи по вертикалі */
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .confirmation-item {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
        }
        .text-products{
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 250px;
            text-align: left;
        }
        .confirmation-total {
            text-align: right;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .btn-return {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0000ff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .btn-return:hover {
            background-color: #0000cc;
        }
        .confirmation-container h2{
            text-transform: uppercase;
            font-weight: 500;
            color: #0000ff;
            font-size: 20px;
            margin-bottom: 30px;
        }
        .desc-product{
            font-size: 14px;
            color: #0000ff;
            font-weight: 500;
            text-decoration: none;
            line-height: 20px;
        }
        .text-prod{
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-weight: 500;
            margin-bottom: 30px;
        }
        .desc-detail-confirm{
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-weight: 500;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <main>
        <div class="container confirmation-container">
            <h2>Підтвердження замовлення</h2>
            <?php if ($orderDetails): ?>
                <div class="text-prod"><h3>Дякуємо за замовлення, <?= htmlspecialchars($orderDetails['name']) ?>!</h3>
                <p>Ваше замовлення №<?= $order_id ?> успішно оформлене.</p></div>
                <div class="confirmation-items">
                    <?php foreach ($orderItems as $item):
                        $productQuery = $db->prepare("SELECT name, image1 FROM " . $item['product_table'] . " WHERE product_id = :product_id");
                        $productQuery->execute(['product_id' => $item['product_id']]);
                        $product = $productQuery->fetch(PDO::FETCH_ASSOC);
                        $productName = $product['name'];
                        $productImage = $product['image1'] ? 'data:image/png;base64,' . base64_encode($product['image1']) : 'default-image1.png';
                    ?>
                    <div class="confirmation-item">
                        <div>
                            <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($productName) ?>" style="width: 150px; height: 200px;">
                        </div>
                            <div class="text-products">
                                <div><span><a class="desc-product" href="product-detail.php?product_id=<?= $item['product_id'] ?>&table=<?= $item['product_table'] ?>" target="_blank"><?= htmlspecialchars($productName) ?></a></span></div>
                                <div><span><?= $item['quantity'] ?> x $<?= $item['unit_price'] ?></span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="confirmation-total">
                        <p>Загальна сума: <span id="total-price"></span>$</p>
                    </div>
                <div class="desc-detail-confirm">
                    <p>Ваша доставка буде здійснена через: <?= htmlspecialchars($orderDetails['shipping_method']) ?></p>
                    <p>Спосіб оплати: <?= htmlspecialchars($orderDetails['payment_method']) ?></p>
                    <button class="btn-return" onclick="window.location.href='index.php'">Повернутися на головну</button>
                </div>
            <?php else: ?>
                <p>Замовлення не знайдено.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function calculateTotalPrice() {
            let total = 0;
            <?php foreach ($orderItems as $item): ?>
                total += <?= $item['quantity'] ?> * <?= $item['unit_price'] ?>;
            <?php endforeach; ?>
            document.getElementById('total-price').textContent = total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', calculateTotalPrice);
    </script>
</body>
</html>

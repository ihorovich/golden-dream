<?php

include 'db_connection.php'; // Підключення до бази даних

$errors = [];
$success = false;
$tables = ['rings', 'necklace', 'bracelets', 'earrings', 'pendants'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = isset($_POST['table']) ? $_POST['table'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $article = isset($_POST['article']) ? $_POST['article'] : '';
    $brand = isset($_POST['brand']) ? $_POST['brand'] : '';
    $details_and_care = isset($_POST['details_and_care']) ? $_POST['details_and_care'] : '';

    $image1 = isset($_FILES['image1']['tmp_name']) ? $_FILES['image1']['tmp_name'] : '';
    $image2 = isset($_FILES['image2']['tmp_name']) ? $_FILES['image2']['tmp_name'] : '';

    if (!$table || !$name || !$description || !$price || !$size || !$article || !$brand || !$details_and_care || !$image1 || !$image2) {
        $errors[] = "Будь ласка, заповніть всі поля та завантажте обидва зображення.";
    } else {
        $image1Data = file_get_contents($image1);
        $image2Data = file_get_contents($image2);

        try {
            $query = $db->prepare("INSERT INTO $table (user_id, name, description, price, size, article, brand, details_and_care, image1, image2) VALUES (1, :name, :description, :price, :size, :article, :brand, :details_and_care, :image1, :image2)");
            $query->bindParam(':name', $name);
            $query->bindParam(':description', $description);
            $query->bindParam(':price', $price);
            $query->bindParam(':size', $size);
            $query->bindParam(':article', $article);
            $query->bindParam(':brand', $brand);
            $query->bindParam(':details_and_care', $details_and_care);
            $query->bindParam(':image1', $image1Data, PDO::PARAM_LOB);
            $query->bindParam(':image2', $image2Data, PDO::PARAM_LOB);
            $query->execute();
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Не вдалося додати товар: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати новий товар</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            margin: 20px auto;
        }
        .form-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .form-container nav ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .form-container nav ul li {
            display: inline;
        }
        .form-container nav ul li a {
            color: #007bff;
            text-decoration: none;
        }
        .form-container nav ul li a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .form-group input[type="file"] {
            border: none;
        }
        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            width: 100%;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Додати новий товар</h1>
        <nav>
            <ul>
                <li><a href="admin.php">Повернутися до панелі адміністратора</a></li>
                <li><a href="manage_products.php">Управління товарами</a></li>
            </ul>
        </nav>
        <?php if ($success): ?>
            <p class="success">Товар успішно додано!</p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="add_product.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="table">Таблиця:</label>
                <select id="table" name="table" required>
                    <option value="">Оберіть таблицю</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?= $table ?>"><?= ucfirst($table) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Назва:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Опис:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Ціна:</label>
                <input type="text" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="size">Розмір:</label>
                <input type="text" id="size" name="size" required>
            </div>
            <div class="form-group">
                <label for="article">Артикул:</label>
                <input type="text" id="article" name="article" required>
            </div>
            <div class="form-group">
                <label for="brand">Бренд:</label>
                <input type="text" id="brand" name="brand" required>
            </div>
            <div class="form-group">
                <label for="details_and_care">Деталі та догляд:</label>
                <input type="text" id="details_and_care" name="details_and_care" required>
            </div>
            <div class="form-group">
                <label for="image1">Зображення 1:</label>
                <input type="file" id="image1" name="image1" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="image2">Зображення 2:</label>
                <input type="file" id="image2" name="image2" accept="image/*" required>
            </div>
            <button type="submit">Додати товар</button>
        </form>
    </div>
</body>
</html>

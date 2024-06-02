<?php

include 'db_connection.php'; // Підключення до бази даних

$errors = [];
$success = false;
$tables = ['rings', 'necklace', 'bracelet', 'earrings', 'pendants', 'anklets', 'earrings', 'brooches', 'cuffs', 'chokers'];
$currentTable = isset($_GET['table']) ? $_GET['table'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $product_id = $_POST['product_id'];
        try {
            $query = $db->prepare("DELETE FROM $currentTable WHERE product_id = :product_id");
            $query->execute(['product_id' => $product_id]);
            $success = "Товар успішно видалено.";
        } catch (PDOException $e) {
            $errors[] = "Не вдалося видалити товар: " . $e->getMessage();
        }
    } elseif (isset($_POST['update'])) {
        $product_id = $_POST['product_id'];
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $price = isset($_POST['price']) ? $_POST['price'] : '';
        $size = isset($_POST['size']) ? $_POST['size'] : '';
        $article = isset($_POST['article']) ? $_POST['article'] : '';
        $brand = isset($_POST['brand']) ? $_POST['brand'] : '';
        $details_and_care = isset($_POST['details_and_care']) ? $_POST['details_and_care'] : '';
        $image1 = !empty($_FILES['image1']['tmp_name']) ? file_get_contents($_FILES['image1']['tmp_name']) : null;
        $image2 = !empty($_FILES['image2']['tmp_name']) ? file_get_contents($_FILES['image2']['tmp_name']) : null;

        if (!$name || !$description || !$price || !$size || !$article || !$brand || !$details_and_care) {
            $errors[] = "Будь ласка, заповніть всі поля.";
        } else {
            try {
                $query = $db->prepare("UPDATE $currentTable SET name = :name, description = :description, price = :price, size = :size, article = :article, brand = :brand, details_and_care = :details_and_care, image1 = COALESCE(:image1, image1), image2 = COALESCE(:image2, image2) WHERE product_id = :product_id");
                $query->bindParam(':name', $name);
                $query->bindParam(':description', $description);
                $query->bindParam(':price', $price);
                $query->bindParam(':size', $size);
                $query->bindParam(':article', $article);
                $query->bindParam(':brand', $brand);
                $query->bindParam(':details_and_care', $details_and_care);
                $query->bindParam(':product_id', $product_id);
                if ($image1 !== null) {
                    $query->bindParam(':image1', $image1, PDO::PARAM_LOB);
                } else {
                    $query->bindValue(':image1', null, PDO::PARAM_NULL);
                }
                if ($image2 !== null) {
                    $query->bindParam(':image2', $image2, PDO::PARAM_LOB);
                } else {
                    $query->bindValue(':image2', null, PDO::PARAM_NULL);
                }
                $query->execute();
                $success = "Товар успішно оновлено.";
            } catch (PDOException $e) {
                $errors[] = "Не вдалося оновити товар: " . $e->getMessage();
            }
        }
    }
}

$products = [];
if ($currentTable) {
    try {
        $query = $db->prepare("SELECT * FROM $currentTable");
        $query->execute();
        $products = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errors[] = "Не вдалося отримати товари: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Управління товарами</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-container h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-container input[type="text"],
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
        }
        .form-container textarea {
            resize: vertical;
            height: 100px;
        }
        .form-container button {
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
        .form-container button:hover {
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
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #0000ff;
            font-weight: 500;
            color: #fff;
        }
        table td img {
            max-width: 50px;
            height: auto;
            display: block;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .actions form {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .actions input[type="text"],
        .actions input[type="file"] {
            margin-bottom: 0;
            width: auto;
        }
        .actions button {
            background-color: #0000ff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            width: 100%;
        }
        .actions button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Управління товарами</h1>
        <div class="form-container">
            <form method="get" action="manage_products.php">
                <label for="table">Оберіть таблицю:</label>
                <select id="table" name="table" onchange="this.form.submit()">
                    <option value="">Оберіть таблицю</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?= $table ?>" <?= $currentTable === $table ? 'selected' : '' ?>><?= ucfirst($table) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
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
        </div>
        <?php if ($currentTable && !empty($products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Опис</th>
                        <th>Ціна</th>
                        <th>Розмір</th>
                        <th>Артикул</th>
                        <th>Бренд</th>
                        <th>Деталі та догляд</th>
                        <th>Фото 1</th>
                        <th>Фото 2</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['description']) ?></td>
                            <td><?= htmlspecialchars($product['price']) ?></td>
                            <td><?= htmlspecialchars($product['size']) ?></td>
                            <td><?= htmlspecialchars($product['article']) ?></td>
                            <td><?= htmlspecialchars($product['brand']) ?></td>
                            <td><?= htmlspecialchars($product['details_and_care']) ?></td>
                            <td><img src="data:image/jpeg;base64,<?= base64_encode($product['image1']) ?>" /></td>
                            <td><img src="data:image/jpeg;base64,<?= base64_encode($product['image2']) ?>" /></td>
                            <td>
                                <div class="actions">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                        <input type="text" name="description" value="<?= htmlspecialchars($product['description']) ?>" required>
                                        <input type="text" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                                        <input type="text" name="size" value="<?= htmlspecialchars($product['size']) ?>" required>
                                        <input type="text" name="article" value="<?= htmlspecialchars($product['article']) ?>" required>
                                        <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>
                                        <input type="text" name="details_and_care" value="<?= htmlspecialchars($product['details_and_care']) ?>" required>
                                        <input type="file" name="image1">
                                        <input type="file" name="image2">
                                        <input type="hidden" name="update" value="1">
                                        <button type="submit">Оновити</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                                        <input type="hidden" name="delete" value="1">
                                        <button type="submit">Видалити</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($currentTable): ?>
            <p>Товари відсутні.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: ../Jewerly_Company/index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (!$title || !$content) {
        $errors[] = "Будь ласка, заповніть всі поля.";
    } else {
        $sql = "INSERT INTO news (title, content, image) VALUES (:title, :content, :image)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        if ($image) {
            $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':image', null, PDO::PARAM_NULL);
        }

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Не вдалося додати новину.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додати Новину</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            display: flex;
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
    <div class="sidebar">
        <h2>АДМІН ПАНЕЛЬ</h2>
        <ul>
            <li><a href="admin.php">Головна панель</a></li>
            <li><a href="add_product.php">Додати новий товар</a></li>
            <li><a href="manage_products.php">Управління товарами</a></li>
            <li><a href="add_news.php">Додати новину</a></li>
            <li><a href="manage_news.php">Управління новинами</a></li>
        </ul>
        <div class="logout-button">
            <form method="POST">
                <button type="submit" name="logout">Вийти</button>
            </form>
        </div>
    </div>
    <div class="form-container">
        <h1>Додати новину</h1>
        <nav>
            <ul>
                <li><a href="admin.php">Повернутися до панелі адміністратора</a></li>
            </ul>
        </nav>
        <?php if ($success): ?>
            <p class="success">Новину успішно додано!</p>
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
        <form action="process_add_news.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="content">Зміст:</label>
                <textarea id="content" name="content" rows="10" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Зображення:</label>
                <input type="file" id="image" name="image">
            </div>
            <button type="submit">Додати новину</button>
        </form>
    </div>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: ../Jewerly_Company/index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_news.php');
    exit;
}

$newsId = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    try {
        if ($image) {
            $sql = "UPDATE news SET title = :title, content = :content, image = :image WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
        } else {
            $sql = "UPDATE news SET title = :title, content = :content WHERE id = :id";
            $stmt = $db->prepare($sql);
        }
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $newsId);
        $stmt->execute();
        header('Location: manage_news.php');
        exit;
    } catch (PDOException $e) {
        error_log("Update failed: " . $e->getMessage());
        echo "Update failed: " . $e->getMessage();
        exit;
    }
}

try {
    $stmt = $db->prepare("SELECT * FROM news WHERE id = :id");
    $stmt->bindParam(':id', $newsId);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$news) {
        header('Location: manage_news.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo "Query failed: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати новину</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            background-color: #fff;
        }
        .sidebar {
            width: 250px;
            background-color: #0000ff;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
        }
        .sidebar h2 {
            color: #fff;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar ul li a:hover {
            text-decoration: underline;
        }
        .main-content {
            margin-left: 300px;
            padding: 15px;
            width: calc(100% - 210px);
        }
        .main-content h1 {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label, input, textarea {
            font-size: 16px;
        }
        textarea {
            resize: vertical;
        }
        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .current-image {
            max-width: 200px;
            margin-top: 10px;
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
    <div class="main-content">
        <h1>Редагувати новину</h1>
        <form action="edit_news.php?id=<?= $newsId ?>" method="POST" enctype="multipart/form-data">
            <label for="title">Заголовок:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($news['title']) ?>" required>

            <label for="content">Зміст:</label>
            <textarea id="content" name="content" rows="10" required><?= htmlspecialchars($news['content']) ?></textarea>

            <label for="image">Зображення:</label>
            <input type="file" id="image" name="image">
            <?php if ($news['image']): ?>
                <p>Поточне зображення:</p>
                <img src="data:image/jpeg;base64,<?= base64_encode($news['image']) ?>" alt="Current Image" class="current-image">
            <?php endif; ?>

            <button type="submit">Оновити новину</button>
        </form>
    </div>
</body>
</html>

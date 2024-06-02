<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: ../Jewerly_Company/index.php');
    exit;
}

try {
    $stmt = $db->query("SELECT * FROM news ORDER BY created_at DESC");
    $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo "Query failed: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $newsId = $_POST['news_id'];
    try {
        $stmt = $db->prepare("DELETE FROM news WHERE id = :id");
        $stmt->bindParam(':id', $newsId);
        $stmt->execute();
        header('Location: manage_news.php');
        exit;
    } catch (PDOException $e) {
        error_log("Delete failed: " . $e->getMessage());
        echo "Delete failed: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Управління новинами</title>
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
            margin-left: 200px;
            padding: 15px;
            width: calc(100% - 250px);
        }
        .main-content h1 {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .news-table {
            width: 100%;
            border-collapse: collapse;
        }
        .news-table th, .news-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .news-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        .news-table td a {
            color: #007bff;
            text-decoration: none;
        }
        .news-table td a:hover {
            text-decoration: underline;
        }
        .news-table form {
            display: inline;
        }
        .news-table form button {
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
        .news-table form button:hover {
            background-color: #c9302c;
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
        <h1>Управління новинами</h1>
        <table class="news-table">
            <thead>
                <tr>
                    <th>Заголовок</th>
                    <th>Дата створення</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($newsItems as $news) : ?>
                    <tr>
                        <td><?= htmlspecialchars($news['title']) ?></td>
                        <td><?= htmlspecialchars($news['created_at']) ?></td>
                        <td>
                            <a href="edit_news.php?id=<?= $news['id'] ?>">Редагувати</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                <button type="submit" name="delete">Видалити</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

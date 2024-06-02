<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: ../Jewerly_Company/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../Jewerly_Company/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адміністративна панель</title>
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
        }
        .main-content nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 15px;
        }
        .main-content nav ul li a {
            color: #333;
            text-decoration: none;
            font-size: 16px;
        }
        .main-content nav ul li a:hover {
            text-decoration: underline;
        }
        .logout-button {
            margin-top: auto;
        }
        .logout-button button {
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
        <?php include $content; ?>
    </div>
</body>
</html>

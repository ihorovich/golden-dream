<?php
session_start();

// Перевірка аутентифікації користувача
if (!isset($_SESSION['isAuthenticated']) || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header('Location: index.php');
    exit;
}

// Встановлення шляху до файлу index.php
$indexFilePath = 'index.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedContent = $_POST['content'];

    if (file_put_contents($indexFilePath, $updatedContent)) {
        $success = "Зміни успішно збережено.";
    } else {
        $errors[] = "Не вдалося зберегти зміни.";
    }
}

$currentContent = '';
if (file_exists($indexFilePath)) {
    $currentContent = file_get_contents($indexFilePath);
} else {
    $errors[] = "Файл index.php не знайдено.";
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Оновити інформацію на сайті</title>
    <script src="https://cdn.tiny.cloud/1/4ibgap9wdf3rczyiut5j1zk9u8k2mdvqszn4ovjxqs9g116g/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        /* Загальні стилі */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'WixMadeforText';
            font-size: 14px;
            font-weight: normal;
            background-color: #FFFEFE;
            padding: 0;
            display: flex;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            height: 100%;
            padding: 20px;
            flex: 1;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-container textarea {
            width: 100%;
            height: 400px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
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
    </style>
    <script>
        tinymce.init({
            selector: 'textarea',
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Оновити інформацію на сайті</h1>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <div class="form-container">
            <form method="post" action="update_site_info.php">
                <label for="content">Редагувати вміст головної сторінки:</label>
                <textarea id="content" name="content"><?= htmlspecialchars($currentContent) ?></textarea>
                <button type="submit">Зберегти зміни</button>
            </form>
        </div>
    </div>
</body>
</html>

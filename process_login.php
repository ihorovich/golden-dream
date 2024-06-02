<?php
// Підключення до бази даних
$connection = mysqli_connect("localhost", "root", "CBuVjYDNiWkNcbcV", "jewelry_company");
mysqli_set_charset($connection, "utf8"); // Встановлення кодування UTF-8

if (!$connection) {
    die("Помилка з'єднання: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Отримання даних з форми
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Підготовка SQL-запиту для перевірки електронної пошти в базі даних
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Перевірка паролю
        if (password_verify($password, $user['password'])) {
            // Авторизація успішна
            echo "Авторизація успішна!";
            header("Location: header.html");
            // Тут можна виконати необхідні дії, наприклад, зберегти дані сесії або перенаправити користувача на іншу сторінку
        } else {
            // Неправильний пароль
            echo "Неправильний пароль";
        }
    } else {
        // Користувача з такою електронною поштою не знайдено
        echo "Користувача з такою електронною поштою не знайдено";
    }
}

// Закриття з'єднання
mysqli_close($connection);
?>

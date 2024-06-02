<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Перевірка на пусті поля
    if (empty($_POST['name'])) {
        echo "Будь ласка, введіть ім'я";
        exit;
    }

    if (empty($_POST['last_name'])) {
        echo "Будь ласка, введіть прізвище";
        exit;
    }

    if (empty($_POST['email'])) {
        echo "Будь ласка, введіть адресу електронної пошти";
        exit;
    }

    if (empty($_POST['phone_number'])) {
        echo "Будь ласка, введіть номер телефону";
        exit;
    }

    // Перевірка на введення лише цифр для номера телефону
    if (!is_numeric($_POST['phone_number'])) {
        echo "Номер телефону повинен містити тільки цифри";
        exit;
    }

    if (empty($_POST['password'])) {
        echo "Будь ласка, введіть пароль";
        exit;
    }

    // Підключення до бази даних з вказанням кодування UTF-8
    $connection = mysqli_connect("localhost", "root", "CBuVjYDNiWkNcbcV", "jewelry_company");
    mysqli_set_charset($connection, "utf8"); // Встановлення кодування UTF-8

    if (!$connection) {
        die("Помилка з'єднання: " . mysqli_connect_error());
    }

    // Отримання даних з форми
    $name = $_POST['name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хешування паролю

    // Перевірка на існування користувача з таким ім'ям
    $check_name_query = "SELECT * FROM users WHERE name='$name'";
    $result_name = mysqli_query($connection, $check_name_query);

    if (mysqli_num_rows($result_name) > 0) {
        echo "Користувач з таким ім'ям вже існує";
        exit;
    }

    // Перевірка на існування користувача з таким прізвищем
    $check_last_name_query = "SELECT * FROM users WHERE last_name='$last_name'";
    $result_last_name = mysqli_query($connection, $check_last_name_query);

    if (mysqli_num_rows($result_last_name) > 0) {
        echo "Користувач з таким прізвищем вже існує";
        exit;
    }

    // Перевірка на існування користувача з такою адресою електронної пошти
    $check_email_query = "SELECT * FROM users WHERE email='$email'";
    $result_email = mysqli_query($connection, $check_email_query);

    if (mysqli_num_rows($result_email) > 0) {
        echo "Користувач з такою адресою електронної пошти вже існує";
        exit;
    }

    // Запит на вставку даних в таблицю users
    $query = "INSERT INTO users (name, last_name, email, phone_number, password) VALUES ('$name', '$last_name', '$email', '$phone_number', '$password')";

    if (mysqli_query($connection, $query)) {
        // Редірект на сторінку успішної реєстрації або виведення повідомлення
        header("Location: header.html");
        exit;
    } else {
        echo "Помилка: " . mysqli_error($connection);
    }

    // Закриття з'єднання
    mysqli_close($connection);
}
?>

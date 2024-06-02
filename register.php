<?php
session_start();
$errors = [];
$response = ['success' => false, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Перевірка на пусті поля
    if (empty($_POST['name'])) {
        $errors['name'] = "Будь ласка, введіть ім'я";
    }
    if (empty($_POST['last_name'])) {
        $errors['last_name'] = "Будь ласка, введіть прізвище";
    }
    if (empty($_POST['email'])) {
        $errors['email'] = "Будь ласка, введіть електронну адресу";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Неправильний формат електронної пошти";
    }
    if (empty($_POST['phone_number'])) {
        $errors['phone_number'] = "Будь ласка, введіть номер телефону";
    } elseif (!preg_match('/^\d{10}$/', $_POST['phone_number'])) {
        $errors['phone_number'] = "Неправильний формат номера телефону";
    }
    if (empty($_POST['password'])) {
        $errors['password'] = "Будь ласка, введіть пароль";
    } elseif (strlen($_POST['password']) < 6) {
        $errors['password'] = "Пароль повинен містити принаймні 6 символів";
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }

    // Підключення до бази даних
    $connection = mysqli_connect("localhost", "root", "CBuVjYDNiWkNcbcV", "jewelry_company");
    if (!$connection) {
        die("Помилка з'єднання: " . mysqli_connect_error());
    }

    // Отримання даних з форми
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $last_name = mysqli_real_escape_string($connection, $_POST['last_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $phone_number = mysqli_real_escape_string($connection, $_POST['phone_number']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хешування паролю

    // Перевірка на унікальність електронної пошти
    $check_email_query = "SELECT * FROM users WHERE email='$email'";
    $result_email = mysqli_query($connection, $check_email_query);

    if (mysqli_num_rows($result_email) > 0) {
        $response['errors']['email'] = "Користувач з такою адресою електронної пошти вже існує";
        echo json_encode($response);
        exit;
    }

    // Вставка даних в таблицю users
    $query = "INSERT INTO users (name, last_name, email, phone_number, password) VALUES ('$name', '$last_name', '$email', '$phone_number', '$password')";
    if (mysqli_query($connection, $query)) {
        $user_id = mysqli_insert_id($connection);

        // Збереження даних користувача у сесії
        $_SESSION['isAuthenticated'] = true;
        $_SESSION['user_id'] = $user_id; // Зберігаємо user_id
        $_SESSION['name'] = $name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        $_SESSION['phone_number'] = $phone_number;

        // Використання user_id для унікального ключа в `sessionStorage`
        echo json_encode(['success' => true]);
        exit;
    } else {
        $response['errors']['database'] = "Помилка: " . mysqli_error($connection);
        echo json_encode($response);
        exit;
    }

    // Закриття з'єднання
    mysqli_close($connection);
}
?>

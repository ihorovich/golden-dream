<?php
session_start();
$errors = [];
$response = ['success' => false, 'errors' => []];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email'])) {
        $errors['email'] = "Будь ласка, введіть електронну адресу";
    }
    if (empty($_POST['password'])) {
        $errors['password'] = "Будь ласка, введіть пароль";
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }

    $connection = mysqli_connect("localhost", "root", "CBuVjYDNiWkNcbcV", "jewelry_company");
    if (!$connection) {
        die("Помилка з'єднання: " . mysqli_connect_error());
    }

    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = $_POST['password'];

    if ($email === 'admin@gmail.com' && $password === 'Administrator') {
        $_SESSION['isAuthenticated'] = true;
        $_SESSION['isAdmin'] = true;
        echo json_encode(['success' => true, 'isAdmin' => true]);
        exit;
    }

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['isAuthenticated'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone_number'] = $row['phone_number'];
            $_SESSION['isAdmin'] = false;

            echo json_encode(['success' => true, 'isAdmin' => false]);
            exit;
        } else {
            $response['errors']['email'] = 'Неправильна адреса електронної пошти або пароль';
            $response['errors']['password'] = 'Неправильна адреса електронної пошти або пароль';
        }
    } else {
        $response['errors']['email'] = 'Неправильна адреса електронної пошти або пароль';
        $response['errors']['password'] = 'Неправильна адреса електронної пошти або пароль';
    }

    mysqli_close($connection);
    echo json_encode($response);
}
?>

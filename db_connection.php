<?php
    $host = 'localhost';
    $db = 'jewelry_company';
    $user = 'root';
    $pass = 'CBuVjYDNiWkNcbcV';

    try {
        $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        echo "Connection failed: " . $e->getMessage();
        exit;
    }
?>

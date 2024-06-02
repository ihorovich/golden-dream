<?php
session_start();

if (isset($_SESSION['isAuthenticated']) && $_SESSION['isAuthenticated'] === true) {
    echo 'authenticated';
} else {
    echo 'unauthenticated';
}
?>

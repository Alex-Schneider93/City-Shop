<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['artikel_id'])) {
    $artikel_id = $_POST['artikel_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$artikel_id])) {
        $_SESSION['cart'][$artikel_id]++;
    } else {
        $_SESSION['cart'][$artikel_id] = 1;
    }

    header("Location: startseite.php?added=1");
    exit();
} else {
    header("Location: startseite.php");
    exit();
}
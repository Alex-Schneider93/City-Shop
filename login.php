<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $config = require 'config.php';

    try {
        $pdo = new PDO(
            "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8mb4",
            $config['user'],
            $config['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = $_POST['email'] ?? '';
        $passwort = $_POST['passwort'] ?? '';

        $stmt = $pdo->prepare("SELECT Kunden_ID, Password FROM Kunden WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && ($passwort === $user['Password'] || password_verify($passwort, $user['Password']))) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['Kunden_ID'];
            header("Location: startseite.php");
            exit();
        } else {
            header("Location: falsche_eingabe.html?error=1");
            exit();
        }

    } catch (PDOException $e) {
        die("Fehler: " . $e->getMessage());
    }
} else {
    header("Location: login.html");
    exit();
}
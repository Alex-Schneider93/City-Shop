<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $config = require 'config.php';

    try {
        $pdo = new PDO(
            "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8mb4",
            $config['user'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $vorname = $_POST['vorname'] ?? '';
        $nachname = $_POST['nachname'] ?? '';
        $email = $_POST['email'] ?? '';
        $passwort = $_POST['passwort'] ?? '';
        $agb = isset($_POST['agb']) ? 1 : 0;

        $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM Kunden WHERE Email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->fetchColumn() > 0) {
            header("Location: registrieren.html?error=exists");
            exit();
        }

        $hashedPassword = password_hash($passwort, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Kunden (Vorname, Nachname, Email, Password, AGB) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vorname, $nachname, $email, $hashedPassword, $agb]);

        header("Location: registrieren_erfolg.html?success=1");
        exit();

    } catch (PDOException $e) {
        die("Fehler beim Speichern: " . $e->getMessage());
    }
} else {
    header("Location: registrieren.html");
    exit();
}
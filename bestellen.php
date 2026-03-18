<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: startseite.php");
    exit();
}

$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8mb4",
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $userStmt = $pdo->prepare("SELECT Vorname, Nachname FROM Kunden WHERE Kunden_ID = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("Fehler: Kunde nicht gefunden.");
    }

    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT Artikel_ID, Artikel_Name, Preis, Bild_URL FROM Produkte WHERE Artikel_ID IN ($placeholders)");
    $stmt->execute($ids);
    $produkte = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "INSERT INTO Bestellungen (Kunden_ID, Vorname, Nachname, Artikel_Name, Bild_URL, Preis, Menge) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $pdo->prepare($sql);

    foreach ($produkte as $produkt) {
        $artikel_id = $produkt['Artikel_ID'];
        $menge = $_SESSION['cart'][$artikel_id];
        
        $insertStmt->execute([
            $_SESSION['user_id'],
            $userData['Vorname'],
            $userData['Nachname'],
            $produkt['Artikel_Name'],
            $produkt['Bild_URL'],
            $produkt['Preis'],
            $menge
        ]);
    }

    unset($_SESSION['cart']);

    header("Location: bestellung_erfolg.php");
    exit();

} catch (PDOException $e) {
    die("Fehler bei der Bestellung: " . $e->getMessage());
}
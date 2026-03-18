<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
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

    $stmt = $pdo->prepare("SELECT * FROM Bestellungen WHERE Kunden_ID = ? ORDER BY Bestelldatum DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $bestellungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gesamtSumme = 0;

} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="body.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="cart.css">
    <title>Bestellung erfolgreich</title>
</head>

<body>
    <header class="main-header">
        <div class="logo">
            <h1>CityShop</h1>
        </div>
        <nav>
            <a href="startseite.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="cart-wrapper">
        <div class="cart-card">
            <h2 style="color: #28a745;">✔ Vielen Dank für deine Bestellung!</h2>
            <p>Hier ist eine Übersicht deiner letzten Artikel:</p>

            <div class="cart-list">
                <?php foreach ($bestellungen as $row): 
                    $artikelSumme = $row['Preis'] * $row['Menge'];
                    $gesamtSumme += $artikelSumme; 
                ?>
                <div class="cart-item">
                    <div class="cart-product">
                        <img src="<?php echo htmlspecialchars($row['Bild_URL']); ?>" alt="Produkt">
                        <div class="product-info">
                            <span class="product-name"><?php echo htmlspecialchars($row['Artikel_Name']); ?></span>
                            <span class="product-price">Menge: <?php echo $row['Menge']; ?></span>
                        </div>
                    </div>
                    <div class="item-total">
                        <?php echo number_format($artikelSumme, 2, ',', '.'); ?> €
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                    <span>Gesamtsumme:</span>
                    <span><?php echo number_format($gesamtSumme, 2, ',', '.'); ?> €</span>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="startseite.php" class="btn-checkout" style="text-decoration: none;">Zurück zum Shop</a>
            </div>
        </div>
    </main>
</body>

</html>
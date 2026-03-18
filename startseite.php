<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8mb4",
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT Artikel_ID, Artikel_Name, Preis, Bild_URL FROM Produkte");
    $produkte = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="body.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="grid.css">
    <link rel="stylesheet" href="button.css">
    <title>CityShop - Startseite</title>
</head>

<body>

    <header class="main-header">
        <div class="logo">
            <h1>CityShop</h1>
        </div>
        <nav>
            <a href="warenkorb.php">Warenkorb (<?php echo $cart_count; ?>)</a>
            <a href="logout.php">Ausloggen</a>
        </nav>
    </header>

    <main>
        <section class="welcome-section"><br>
            <h2>Unsere Produkte</h2>
            <?php if (isset($_GET['added'])): ?>
            <p style="color: green; font-weight: bold;">Artikel wurde zum Warenkorb hinzugefügt!</p>
            <?php endif; ?>
        </section>

        <div class="product-grid">
            <?php foreach ($produkte as $produkt): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($produkt['Bild_URL']); ?>"
                    alt="<?php echo htmlspecialchars($produkt['Artikel_Name']); ?>">
                <h3><?php echo htmlspecialchars($produkt['Artikel_Name']); ?></h3>
                <p class="price"><?php echo number_format($produkt['Preis'], 2, ',', '.'); ?> €</p>

                <form action="add_to_cart.php" method="post">
                    <input type="hidden" name="artikel_id" value="<?php echo $produkt['Artikel_ID']; ?>">
                    <button type="submit" class="btn-primary">In den Warenkorb</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>
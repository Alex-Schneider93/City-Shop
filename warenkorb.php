<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['remove_item'])) {
        $id = $_POST['artikel_id'];
        unset($_SESSION['cart'][$id]);
    }
    
    if (isset($_POST['update_qty'])) {
        $id = $_POST['artikel_id'];
        $new_qty = (int)$_POST['quantity'];
        if ($new_qty > 0) {
            $_SESSION['cart'][$id] = $new_qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    header("Location: warenkorb.php");
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

    $cart_items = [];
    $total = 0;

    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT Artikel_ID, Artikel_Name, Preis, Bild_URL FROM Produkte WHERE Artikel_ID IN ($placeholders)");
        $stmt->execute($ids);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="body.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="cart.css">
    <title>CityShop - Dein Warenkorb</title>
</head>

<body>
    <?php
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>

    <header class="main-header">
        <div class="logo">
            <h1>CityShop</h1>
        </div>
        <nav>
            <a href="startseite.php">Startseite</a>
            <a href="logout.php">Ausloggen</a>
        </nav>
    </header>

    <main class="cart-wrapper">
        <div class="cart-card">
            <h2 class="cart-title">Warenkorb</h2>

            <?php if (empty($cart_items)): ?>
            <div class="empty-msg">
                <p>Sie haben nichts in den Warenkorb!</p>
                <a href="startseite.php" class="btn-primary">Jetzt shoppen</a>
            </div>
            <?php else: ?>
            <div class="cart-list">
                <?php foreach ($cart_items as $item): 
                        $qty = $_SESSION['cart'][$item['Artikel_ID']];
                        $subtotal = $item['Preis'] * $qty;
                        $total += $subtotal;
                    ?>
                <div class="cart-item">
                    <div class="cart-product">
                        <img src="<?php echo htmlspecialchars($item['Bild_URL']); ?>" alt="Produkt">
                        <div class="product-info">
                            <span class="product-name"><?php echo htmlspecialchars($item['Artikel_Name']); ?></span>
                            <span class="product-price"><?php echo number_format($item['Preis'], 2, ',', '.'); ?>
                                €</span>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <form method="post" class="qty-form">
                            <input type="hidden" name="artikel_id" value="<?php echo $item['Artikel_ID']; ?>">
                            <input type="number" name="quantity" value="<?php echo $qty; ?>" min="1" class="qty-field">
                            <button type="submit" name="update_qty" class="btn-icon">↻</button>
                            <button type="submit" name="remove_item" class="btn-delete">Löschen</button>
                        </form>
                        <div class="item-total">
                            <?php echo number_format($subtotal, 2, ',', '.'); ?> €
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">

                    <span> Gesamtmenge:<?php echo $cart_count; ?></span>
                    <span>Zwischensumme:<?php echo number_format($total, 2, ',', '.'); ?> €</span>
                </div>
                <div class="summary-row total">
                    <span>Gesamtbetrag:</span>
                    <span><?php echo number_format($total, 2, ',', '.'); ?> €</span>
                </div>
                <form action="bestellen.php" method="post">
                    <button type="submit" class="btn-checkout">Bestellen</button>
                </form>
                <a href="startseite.php" class="btn-continue">Weiter einkaufen</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
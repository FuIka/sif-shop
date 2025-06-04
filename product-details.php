<?php
session_start();

require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<h2>Товар не найден</h2>";
        exit;
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($product['name']); ?> - Детали товара</title>
    <link rel="stylesheet" href="style/style_product.css" />
    <link rel="icon" href="gui/logo.ico" type="image/x-icon">
</head>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cartLink = document.getElementById('cart-link');

        document.querySelectorAll('.add-to-cart').forEach(function (button) {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-product-id');

                fetch('../core/add_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Товар добавлен в корзину!');
                        if (cartLink && data.cartCount !== undefined) {
                            cartLink.innerHTML = `Корзина (${data.cartCount})`;
                        }
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                });
            });
        });
    });
</script>

<body>

<div class="container">
    <h1><?= htmlspecialchars($product['name']); ?></h1>

    <div class="product-details">
        <div class="image-container">
            <?php
            $image_src = 'gui/null.png';
            if (isset($product['image']) && trim($product['image']) !== '') {
                $image_src = htmlspecialchars($product['image']);
            }
            ?>
            <img src="<?= $image_src; ?>" alt="<?= htmlspecialchars($product['name']); ?>" />
        </div>

        <div class="details">
            <div class="price"><?= number_format($product['price'], 2, '.', ','); ?> руб.</div>
            <h2>Описание</h2>
            <div class="description"><?= nl2br(htmlspecialchars($product['description'])); ?></div>

            <div class="buttons">
                <a href="#" class="button-link add-to-cart"
                   data-product-id="<?= htmlspecialchars($product['id']); ?>">Добавить в корзину</a>
                <a href="index.php" class="button-link">Вернуться к списку товаров</a>
            </div>
        </div>
    </div>
</div>

</body>

</html>
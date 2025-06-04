<?php
session_start();
include 'includes/db.php';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$categories = [];

if (isset($_GET['category']) && is_array($_GET['category'])) {
    $categories = array_map('trim', $_GET['category']);
    if (!empty($categories)) {
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $sql .= " AND category IN ($placeholders)";
        $params = array_merge($params, $categories);
    }
}

if (!empty($_GET['gpu_manufacturer'])) {
    $sql .= " AND gpu_manufacturer = ?";
    $params[] = $_GET['gpu_manufacturer'];
}

if (!empty($_GET['mother_manufacturer'])) {
    $sql .= " AND mother_manufacturer = ?";
    $params[] = $_GET['mother_manufacturer'];
}

if (!empty($_GET['price_from'])) {
    $sql .= " AND price >= ?";
    $params[] = $_GET['price_from'];
}

if (!empty($_GET['price_to'])) {
    $sql .= " AND price <= ?";
    $params[] = $_GET['price_to'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="style/style_catalog.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h2 {
            margin-top: 20px;
            text-align: center;
            font-size: 2em;
            color: #2c3e50;
        }

        .container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
        }

        .catalog-sidebar {
            width: 200px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
        }

        .catalog-sidebar h3 {
            margin-top: 0;
            color: #34495e;
        }

        .catalog-sidebar label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .catalog-sidebar select,
        .catalog-sidebar input[type=number] {
            width: 100%;
            padding: 6px 10px;
            margin-top: 4px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .product-list {
            flex-direction: column;
            width: calc(100% - 220px);
        }

        .product-list-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: rgba(0, 0, 0, 0.1) 0 4px 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .product-image-wrapper {
            display: flex;
            align-items: flex-start;
        }

        .product-list-item img {
            width: auto;
            height: auto;
            max-width: 150px;
            border-radius: .5em;
            object-fit: contain;
        }

        .product-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: .5em;
        }

        .product-details h4 {
            margin-top: none;
            margin-bottom: none;
            font-size: 16px;
            color: #2c3e50;
        }

        .product-details p {
            margin: none;
            font-size: 14px;
            color: #555;
        }

        .add-to-cart {
            background-color: #2980b9;
            color: #fff;
            border: none;
            padding: 8px;
            border-radius: .5em;
            cursor: pointer;
            font-weight: bold;
            transition: .3s background-color ease-in-out, transform .2s ease-in-out;
        }

        .add-to-cart:hover {
            background-color: #3498db;
            transform: scale(1.05);
        }

        button[disabled] {
            background-color: #bdc3c7 !important;
            cursor: not-allowed !important;
        }
    </style>
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

    <?php include 'includes/header.php'; ?>

    <h2>Каталог товаров</h2>

    <div class="container">
        <div class="catalog-sidebar">
            <form method="GET" action="">
                <h3>Категории</h3>
                <label><input type="checkbox" name="category[]" value="case" <?= in_array('case', $categories) ? 'checked' : '' ?>> Корпус</label>
                <label><input type="checkbox" name="category[]" value="motherboard" <?= in_array('motherboard', $categories) ? 'checked' : '' ?>> Материнская плата</label>
                <label><input type="checkbox" name="category[]" value="video" <?= in_array('video', $categories) ? 'checked' : '' ?>> Видеокарта</label>
                <label><input type="checkbox" name="category[]" value="cpu" <?= in_array('cpu', $categories) ? 'checked' : '' ?>> Процессор</label>
                <label><input type="checkbox" name="category[]" value="ram" <?= in_array('ram', $categories) ? 'checked' : '' ?>> ОЗУ</label>

                <h3>Производители видеокарт</h3>
                <select name="gpu_manufacturer">
                    <option value="" <?= empty($_GET['gpu_manufacturer']) ? 'selected' : '' ?>>Все</option>
                    <option value="Intel" <?= $_GET['gpu_manufacturer'] == 'Intel' ? 'selected' : '' ?>>Intel</option>
                    <option value="AMD" <?= $_GET['gpu_manufacturer'] == 'AMD' ? 'selected' : '' ?>>AMD</option>
                    <option value="NVIDIA" <?= $_GET['gpu_manufacturer'] == 'NVIDIA' ? 'selected' : '' ?>>NVIDIA</option>
                </select>

                <h3>Производители материнских плат</h3>
                <select name="mother_manufacturer">
                    <option value="" <?= empty($_GET['mother_manufacturer']) ? 'selected' : '' ?>>Все</option>
                    <option value="Asus" <?= $_GET['mother_manufacturer'] == 'Asus' ? 'selected' : '' ?>>Asus</option>
                    <option value="Gigabyte" <?= $_GET['mother_manufacturer'] == 'Gigabyte' ? 'selected' : '' ?>>Gigabyte
                    </option>
                    <option value="MSI" <?= $_GET['mother_manufacturer'] == 'MSI' ? 'selected' : '' ?>>MSI</option>
                </select>

                <h3>Цена от и до</h3>
                <div style='display:flex; gap:10px;">
<input type=' number' name='price_from' placeholder='От' style='width:100px;'
                    value='<?= htmlspecialchars($_GET["price_from"] ?? "") ?>'>
                    <input type='number' name='price_to' placeholder='До' style='width:100px;'
                        value='<?= htmlspecialchars($_GET["price_to"] ?? "") ?>'>
                </div>

                <br>
                <button type='submit'>Показать товары</button>
            </form>
        </div>

        <div class="product-list">
            <?php foreach ($products as $product):
                $image_path = !empty($product['image']) ? $product['image'] : 'gui/null.png';
                if (!file_exists($image_path)) {
                    $image_path = 'gui/null.png';
                }

                $out_of_stock = ($product['stock_quantity'] == 0);

                switch ($product['delivery_time']) {
                    case 'today':
                        $delivery_text = 'Сегодня';
                        break;
                    case '1-3':
                        $delivery_text = '1-3 дня';
                        break;
                    default:
                        $delivery_text = 'Неделя';
                }
                ?>
                <div class='product-list-item'>
                    <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class='product-details'>
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p>Цена：<?= htmlspecialchars($product['price']) ?> руб.</p>
                        <p>Доставка：<?= htmlspecialchars($delivery_text) ?></p>
                        <?php if (!$out_of_stock): ?>
                            <form method='POST' action='add_to_cart.php'>
                                <a href="#" class="button-link add-to-cart"
                                    data-product-id="<?= htmlspecialchars($product['id']); ?>">Добавить в корзину</a>
                            </form>
                        <?php else:
                            ?>
                            <button class='add-to-cart' disabled style='background-color:#ccc;'>Товар
                                отсутствует</button><?php endif; ?>
                        <p>На складе：<?= htmlspecialchars($product['stock_quantity']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
        </div>

        <?php include 'includes/footer.php'; ?>

    </div>

</body>

</html>
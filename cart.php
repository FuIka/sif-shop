<?php include 'includes/header.php'; ?>
<?php include 'includes/db.php'; ?>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.price, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    $cartItems = [];
    $totalPrice = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $subtotal = $row['price'] * $row['quantity'];
        $cartItems[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'subtotal' => $subtotal
        ];
        $totalPrice += $subtotal;
    }
} catch (PDOException $e) {
    die("Ошибка при получении корзины: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Корзина</title>
    <style>
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            color: #2c3e50;
        }

        table {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 40px auto;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        thead {
            background-color: #34495e;
        }

        th {
            padding: 15px;
            color: #fff;
        }

        td {
            padding: 15px;
        }

        tr {
            background-color: #fff;
            border-radius: 8px;
        }

        tr:nth-child(even) {
            background-color: #ecf0f1;
        }

        button {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }

        .total {
            font-size: 1.8em;
            font-weight: bold;
            text-align: center;
            margin-top: 30px;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .actions a {
            display: inline-block;
            padding: 14px 30px;
            background-color: #2980b9;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: .95em;
            transition: .3s all ease;
        }

        .actions a:hover {
            background-color: #3498db;
            transform: translateY(-2px);
        }

        @media(max-width:600px) {
            table {
                width: auto;
            }

            .actions {
                flex-direction: column;
                gap: 15px;
            }

            .actions a {
                width: auto;
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    <h1>Ваша корзина</h1>

    <?php if (empty($cartItems)): ?>
        <p style="text-align:center; font-size:1.2em;">Ваша корзина пуста.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена за шт.</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td style="border-top-left-radius :8px;"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                        <td style="min-width:150px;">
                            <form method="post" action="core/update_cart.php"
                                style="display:flex; align-items:center; gap:5px;">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>" />
                                <button type="submit" name="decrease" style="padding:4px 8px;">−</button>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>"
                                    min=1 style="width:50px;text-align:center;">
                                <button type="submit" name="increase" style="padding:4px 8px;">+</button>
                            </form>
                        </td>
                        <td><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> ₽</td>
                        <td style="border-top-right-radius :8px;">
                            <form method="post" action="core/update_cart.php" style="margin:auto;">
                                <input type="hidden" name="action" value="remove" />
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>" />
                                <button type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="total">Общая сумма заказа:<br><?php echo number_format($totalPrice, 2, ',', ' '); ?> ₽</p>

        <div class="actions">
            <a href="checkout.php">Оформить заказ</a>
            <a href="index.php">Продолжить покупки</a>
        </div>

    <?php endif; ?>

</body>

</html>

<?php include 'includes/footer.php'; ?>
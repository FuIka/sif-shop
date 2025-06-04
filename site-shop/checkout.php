<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, address FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        die("Пользователь не найден");
    }
} catch (PDOException $e) {
    die("Ошибка при получении данных пользователя: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="gui/logo.ico" type="image/x-icon" />
    <title>Оформление заказа</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 40px 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type=text],
        input[type=email],
        input[type=tel],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
        }

        button:hover {
            background-color: #219150;
        }
    </style>
</head>

<body>

    <h1>Оформление заказа</h1>

    <form method="post" action="">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" required
            value="<?php echo htmlspecialchars($user_data['username']); ?>" />

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required
            value="<?php echo htmlspecialchars($user_data['email']); ?>" />

        <label for="address">Адрес доставки</label>
        <textarea id="address" name="address" rows="3"
            required><?php echo htmlspecialchars($user_data['address']); ?></textarea>

        <button type="button" onclick="window.location.href='error404.php';">Подтвердить заказ</button>
    </form>

</body>

</html>
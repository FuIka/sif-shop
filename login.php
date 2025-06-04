<?php
session_start();
$error_message = '';

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Некорректный запрос.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, password, is_admin FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = (int)$user['is_admin'];
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                $error_message = "Неверный пароль.";
            }
        } else {
            $error_message = "Пользователь не найден.";
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Вход в систему</title>
    <link rel="stylesheet" href="style/style_reglog.css" />
    <link rel="icon" href="gui/logo.ico" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .home-button {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .home-button:hover {
            background: #555;
        }
    </style>
</head>

<body>
    <?php if ($error_message): ?>
        <div class="error-panel" id="errorPanel">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="index.php" class="home-button">На главную</a>
    </div>

    <div class="form-container">
        <h2>Войти в аккаунт</h2>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />

            <label>Имя пользователя:</label><br />
            <input type="text" name="username" required /><br />

            <label>Пароль:</label><br />
            <input type="password" name="password" required /><br />

            <button type="submit">Войти</button>
        </form>
        <p style="text-align:center;">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>

    <script>
        window.onload = function () {
            <?php if ($error_message): ?>
                const errorPanel = document.getElementById('errorPanel');
                setTimeout(function () {
                    errorPanel.style.opacity = 0;
                    setTimeout(function () { errorPanel.style.display = 'none'; }, 500);
                }, 3000);
            <?php endif; ?>
        };
    </script>

</body>

</html>
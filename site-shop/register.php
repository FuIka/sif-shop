<?php
session_start();
require_once 'includes/db.php';

$blocked_words = ['admin', 'moderator', 'test', 'guest', 'root'];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "Пожалуйста, заполните все поля.";
        $message_type = 'error';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = "Имя пользователя должно быть от 3 до 20 символов.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Некорректный формат email.";
        $message_type = 'error';
    } elseif (preg_match('/\s/', $password)) {
        $message = "Пароль не должен содержать пробелов.";
        $message_type = 'error';
    } else {
        foreach ($blocked_words as $word) {
            if (stripos($username, $word) !== false) {
                $message = "Имя пользователя содержит запрещённые слова.";
                $message_type = 'error';
                break;
            }
        }

        if (!$message) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username=:username OR email=:email");
                $stmt_check->execute([':username' => $username, ':email' => $email]);
                if ($stmt_check->rowCount() > 0) {
                    $stmt_username_check = $pdo->prepare("SELECT id FROM users WHERE username=:username");
                    $stmt_username_check->execute([':username' => $username]);
                    if ($stmt_username_check->rowCount() > 0) {
                        $message = "Это имя пользователя уже занято.";
                        $message_type = 'error';
                    } else {
                        $stmt_email_check = $pdo->prepare("SELECT id FROM users WHERE email=:email");
                        $stmt_email_check->execute([':email' => $email]);
                        if ($stmt_email_check->rowCount() > 0) {
                            $message = "Этот email уже зарегистрирован.";
                            $message_type = 'error';
                        }
                    }
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                    $result = $stmt_insert->execute([
                        ':username' => htmlspecialchars($username),
                        ':email' => htmlspecialchars($email),
                        ':password' => $hashed_password,
                    ]);
                    if ($result) {
                        $_SESSION['success_message'] = "Регистрация прошла успешно!";
                        header('Location: login.php');
                        exit;
                    } else {
                        throw new Exception('Ошибка при добавлении пользователя.');
                    }
                }
            } catch (Exception $e) {
                $message = "Ошибка базы данных: " . htmlspecialchars($e->getMessage());
                $message_type = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Регистрация</title>
    <link rel="stylesheet" href="style/style_reglog.css" />
    <link rel="icon" href="gui/logo.ico" type="image/x-icon" />
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

    <?php if ($message): ?>
        <div class="error-panel <?php echo htmlspecialchars($message_type); ?>" id="msgPanel">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php
    if (isset($_SESSION['success_message'])):
        ?>
        <div class="success">
            <?php echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="index.php" class="home-button">На главную</a>
    </div>

    <div class="form-container">
        <h2>Регистрация</h2>
        <form method="post" action="">
            <label for="username">Имя пользователя:</label><br />
            <input type="text" id="username" name="username" required pattern="[A-Za-zА-Яа-яЁё0-9]{3,20}"
                title="От 3 до 20 символов без пробелов" /><br />

            <label for="email">Email:</label><br />
            <input type="email" id="email" name="email" required /><br />

            <label for="password">Пароль:</label><br />
            <input type="password" id="password" name="password" required pattern="\S{6,}"
                title="Минимум 6 символов без пробелов" /><br />

            <button type="submit">Зарегистрироваться</button>
        </form>
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>

    <script>
        window.onload = function () {
            <?php if ($message): ?>
                const msgPanel = document.getElementById('msgPanel');
                setTimeout(function () { msgPanel.style.display = 'none'; }, 3000);
            <?php endif; ?>
        };
    </script>

    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const patternUsername = /^[A-Za-zА-Яа-яЁё0-9]{3,20}$/;
            const patternPassword = /^\S{6,}$/;

            if (!patternUsername.test(username)) {
                alert('Имя должно быть от 3 до 20 символов без пробелов.');
                e.preventDefault();
                return false;
            }

            if (!patternPassword.test(password)) {
                alert('Пароль должен содержать минимум 6 символов без пробелов.');
                e.preventDefault();
                return false;
            }
        });
    </script>

</body>

</html>
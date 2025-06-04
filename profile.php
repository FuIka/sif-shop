<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$message = '';
$message_type = '';

$user_id = $_SESSION['user_id'];
$current_username = '';
$current_email = '';

try {
    $stmt_user = $pdo->prepare("SELECT username, email FROM users WHERE id = :id");
    $stmt_user->execute([':id' => $user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $current_username = $user_data['username'];
        $current_email = $user_data['email'];
    } else {
        die("Пользователь не найден.");
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    if (empty($new_username) || empty($new_email)) {
        $message = "Пожалуйста, заполните все поля.";
        $message_type = 'error';
    } elseif (in_array(strtolower($new_username), ['admin', 'moderator', 'test', 'guest', 'root'])) {
        $message = "Использование данного имени пользователя запрещено.";
        $message_type = 'error';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Некорректный формат email.";
        $message_type = 'error';
    } else {
        try {
            $stmt_check_username = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt_check_username->execute([':username' => $new_username, ':id' => $user_id]);
            if ($stmt_check_username->rowCount() > 0) {
                $message = "Это имя пользователя уже занято.";
                $message_type = 'error';
            } else {
                $stmt_check_email = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $stmt_check_email->execute([':email' => $new_email, ':id' => $user_id]);
                if ($stmt_check_email->rowCount() > 0) {
                    $message = "Этот email уже зарегистрирован.";
                    $message_type = 'error';
                } else {
                    $stmt_update = $pdo->prepare("UPDATE users SET username=:username, email=:email WHERE id=:id");
                    if ($stmt_update->execute([':username' => $new_username, ':email' => $new_email, ':id' => $user_id])) {
                        $_SESSION['username'] = htmlspecialchars($new_username);
                        $_SESSION['email'] = htmlspecialchars($new_email);

                        header('Location: profile.php?updated=1');
                        exit;
                    } else {
                        $message = "Ошибка при обновлении профиля.";
                        $message_type = 'error';
                    }
                }
            }
        } catch (PDOException $e) {
            die("Ошибка базы данных: " . $e->getMessage());
        }
    }
}

$conn_close_needed = false;

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Профиль</title>
    <link rel="stylesheet" href="style/style_reglog.css" />
    <link rel="icon" href="gui/logo.ico" type="image/x-icon" />
    <style>
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: auto;
        }

        input[type=text],
        input[type=email] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }

        button:hover {
            background: #2ecc71;
        }

        .home-button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .home-button:hover {
            background: #333;
        }

        .error-panel {
            max-width: 400px;
            margin: auto;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            transition: .3s ease all;
        }

        .error-panel.success {
            background-color: #2ecc71;
            color: #fff;
        }

        .error-panel.error {
            background-color: #e74c3c;
            color: #fff;
        }
    </style>
</head>

<body>

    <?php if ($message): ?>
        <div class="error-panel <?php echo htmlspecialchars($message_type); ?>" id="msgPanel">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;">
        <a href="index.php" class="home-button">На главную</a>
    </div>

    <div class="form-container">
        <h2>Редактировать профиль</h2>
        <form method="post" action="profile.php">
            <label>Имя пользователя:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required />

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required />

            <button type="submit">Обновить профиль</button>
        </form>
    </div>

    <script>
        window.onload = function () {
            <?php if ($message): ?>
                const msgPanel = document.getElementById('msgPanel');
                setTimeout(function () { msgPanel.style.display = 'none'; }, 3000);
            <?php endif; ?>
        };
    </script>

</body>

</html>

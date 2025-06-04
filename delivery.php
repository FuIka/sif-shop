<?php session_start(); ?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <title>Доставка</title>
    <style>
        h2 {
            text-align: center;
        }

        .form-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #addressForm {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
        }

        input[type="text"] {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 12px;
            background-color: #27ae60;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #219150;
        }

        #status {
            margin-top: 15px;
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
    <div style="text-align:center;">
        <a href="index.php" class="home-button">На главную</a>
    </div>

    <div class="form-container">
        <h2>Введите ваш адрес доставки</h2>
        <form id="addressForm" autocomplete="off"> <input type="hidden" name="user"
                value="<?php echo htmlspecialchars($_SESSION['username']); ?>"> <label for="address">Адрес:</label>
            <input type="text" id="address" name="address" value="<?php echo $current_address; ?>" required /> <button
                type="submit">Сохранить</button>
        </form>
        <div id="status"></div>
    </div>
    <script>
        document.getElementById('addressForm').addEventListener('submit', function (e) {
            e.preventDefault(); const addressInput = document.getElementById('address'); const address = addressInput.value.trim(); if (!address) { alert('Пожалуйста, введите адрес.'); return; } fetch('../core/save_address.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ address }) }).then(res => res.json()).then(data => { const statusDiv = document.getElementById('status'); if (data.success) { statusDiv.textContent = 'Адрес успешно сохранён!'; statusDiv.style.color = 'green'; } else { statusDiv.textContent = 'Ошибка при сохранении:' + data.message; statusDiv.style.color = 'red'; } }).catch(() => { const statusDiv = document.getElementById('status'); statusDiv.textContent = 'Ошибка сети.'; statusDiv.style.color = 'red'; });
        }); 
    </script>
</body>

</html> <?php include 'includes/footer.php'; ?>
<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ошибка 404 - Страница не найдена</title>
    <link rel="icon" href="gui/logo.ico" type="image/x-icon" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }

        h1 {
            font-size: 72px;
            margin: 0 0 20px 0;
            color: #e74c3c;
        }

        p {
            font-size: 24px;
            margin: 10px 0 20px 0;
        }

        a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #2980b9;
        }

        @media(max-width: 600px) {
            h1 {
                font-size: 48px;
            }

            p {
                font-size: 18px;
                text-align: center;
                padding: 0 10px;
            }

            a {
                padding: 10px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <h1>404</h1>
    <p>Страница не найдена</p>
    <a href="/">Вернуться на главную</a>
</body>

</html>
<?php
session_start();

$currentPage = basename($_SERVER['PHP_SELF']);

require_once 'includes/db.php';

$cartCount = 0;

if (isset($_SESSION['user_id'])) {
  $userId = intval($_SESSION['user_id']);
  try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = intval($row['count']);
  } catch (PDOException $e) {
    $cartCount = 0;
  }
}
$deliveryText = 'Выбрать точку доставки';
if (!empty($_SESSION['address'])) {
  $deliveryText = "Адрес: " . htmlspecialchars($_SESSION['address']);
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <title>Магазин SIF-SHOP</title>
  <link rel="stylesheet" href="style/style.css" />
  <link rel="icon" href="gui/logo.ico" type="image/x-icon" />

  <style>
    .right-section {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-link {
      padding: 10px 15px;
      text-decoration: none;
      color: #333;
      transition: all 0.3s ease;
      white-space: nowrap;
    }

    .nav-link:hover {
      color: #007bff;
    }

    .nav-link.active {
      font-weight: bold;
      border-bottom: 2px solid #007bff;
    }

    .user-avatar {
      display: inline-block;
      width: 50px;
      height: 50px;
      line-height: 50px;
      border-radius: 50%;
      background-color: #4CAF50;
      color: white;
      font-weight: bold;
      text-align: center;
      font-size: 14px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .user-avatar:hover {
      display: inline-block;
      width: 50px;
      height: 50px;
      line-height: 50px;
      border-radius: 50%;
      background-color: #429645;
      color: white;
      font-weight: bold;
      text-align: center;
      font-size: 14px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>

<body>
  <header>
    <div class="top-bar">
      <div class="user-info">
        <?php if (isset($_SESSION['username'])): ?>
          <a href="profile.php" class="user-avatar">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
          </a>
        <?php endif; ?>
      </div>

      <div class="right-section">
        <a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Главная
          страница</a>

        <?php if (isset($_SESSION['username'])): ?>
          <a href="cart.php" class="nav-link <?php echo ($currentPage == 'cart.php') ? 'active' : ''; ?>">
            Корзина (<?php echo $cartCount; ?>)
          </a>

          <a href="delivery.php" class="nav-link <?php echo ($currentPage == 'delivery.php') ? 'active' : ''; ?>">
            <?php echo $deliveryText; ?>
          </a>

          <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <form method="post" action="admin.php" style="margin:0; display:inline-block;">
              <button type="submit" title="Админ Панель" style="background:none;border:none;padding:0;cursor:pointer;">
                <img src="gui/admin.png" alt="Админ-панель" style="width:20px;height:auto;border:none;">
              </button>
            </form>
          <?php endif; ?>

          <form method="post" action="logout.php" style="margin:0; display:inline-block;">
            <button type="submit" title="Выйти" style="background:none;border:none;padding:0;cursor:pointer;">
              <img src="gui/exit.png" alt="Выйти" style="width:20px;height:auto;border:none;">
            </button>
          </form>

        <?php else: ?>
          <a href="login.php" class="nav-link <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>">Войти /
            Регистрация</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="main-content"></div>

</body>

</html>
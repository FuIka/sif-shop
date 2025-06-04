<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$product_id = (int) $_POST['product_id'] ?? 0;

if (!$product_id || !in_array($action, ['update', 'remove'])) {
    die('Некорректные данные');
}

try {
    if ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    } elseif ($action === 'update') {
        $quantity = (int) $_POST['quantity'];
        if ($quantity < 1) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute([':quantity' => $quantity, ':user_id' => $user_id, ':product_id' => $product_id]);
        }
    }
} catch (PDOException $e) {
    die("Ошибка при обновлении корзины: " . htmlspecialchars($e->getMessage()));
}

header('Location: ../cart.php');
exit;
?>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['product_id'])) {    
    header('Location: cart.php'); 
    exit;
}

$product_id = (int) $_GET['product_id'];
$user_id = (int) $_SESSION['user_id'];

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);
    header('Location: cart.php');
    exit;
} catch (PDOException $e) {
    echo "Ошибка при удалении товара: " . htmlspecialchars($e->getMessage());
}
?>
<?php
session_start();
include 'includes/db.php';

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header('Location: index.php?error=invalid_product');
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] === $product_id) {
        $item['quantity']++;
        $found = true;
        break;
    }
}
unset($item);

if (!$found) {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $_SESSION['cart'][] = [
            'id' => (int) $product['id'],
            'name' => htmlspecialchars($product['name']),
            'price' => (float) $product['price'],
            'quantity' => 1,
            'image' => htmlspecialchars($product['image'])
        ];
    } else {
        header('Location: index.php?error=product_not_found');
        exit;
    }
}

header('Location: cart.php');
exit;
?>
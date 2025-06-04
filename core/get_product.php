<?php
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный или отсутствующий идентификатор продукта']);
    exit;
}

$id = (int) $_GET['id'];

require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);

$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Продукт не найден']);
    exit;
}

echo json_encode([
    'id' => $product['id'],
    'name' => $product['name'],
    'category' => $product['category'] ?? '',
    'price' => $product['price'],
    'image_url' => $product['image'] ?? '',
    'description' => $product['description'] ?? '',
    'stock_quantity' => $product['stock_quantity'] ?? 0
]);
?>
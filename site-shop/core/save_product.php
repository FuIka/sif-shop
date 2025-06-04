<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Некорректный метод запроса']);
  exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = isset($_POST['price']) ? floatval($_POST['price']) : null;
$image_url = trim($_POST['image_url'] ?? '');
$description = trim($_POST['description'] ?? '');
$stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : null;

if ($id <= 0 || empty($name) || empty($category) || $price === null || $stock_quantity === null) {
  http_response_code(400);
  echo json_encode(['error' => 'Некорректные или недостающие данные']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE products SET name=:name, category=:category, price=:price, image=:image, description=:description, stock_quantity=:stock_quantity WHERE id=:id");
  $stmt->execute([
    ':name' => $name,
    ':category' => $category,
    ':price' => $price,
    ':image' => $image_url,
    ':description' => $description,
    ':stock_quantity' => $stock_quantity,
    ':id' => $id
  ]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>
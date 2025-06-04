<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Нет ID пользователя в сессии']);
    exit;
}
$userId = intval($_SESSION['user_id']);

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Нет ID продукта']);
    exit;
}
$productId = intval($data['product_id']);

require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    if (!$stmt)
        throw new Exception("Ошибка подготовки запроса: " . $pdo->errorInfo()[2]);
    $stmt->execute([$productId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("Товар не найден");
    }
    $stmt->closeCursor();

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    if (!$stmt)
        throw new Exception("Ошибка подготовки запроса: " . $pdo->errorInfo()[2]);
    $stmt->execute([$userId, $productId]);
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $newQuantity = intval($row['quantity']) + 1;

        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        if (!$updateStmt)
            throw new Exception("Ошибка подготовки обновления: " . $pdo->errorInfo()[2]);
        if (!$updateStmt->execute([$newQuantity, $row['id']]))
            throw new Exception("Ошибка выполнения обновления: " . implode(' ', $updateStmt->errorInfo()));
        $updateStmt = null;
        $stmt->closeCursor();
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        if (!$insertStmt)
            throw new Exception("Ошибка подготовки вставки: " . $pdo->errorInfo()[2]);
        if (!$insertStmt->execute([$userId, $productId]))
            throw new Exception("Ошибка выполнения вставки: " . implode(' ', $insertStmt->errorInfo()));
        $insertStmt = null;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true]);
?>
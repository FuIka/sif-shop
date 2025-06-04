<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$address = trim($data['address'] ?? '');

if (empty($address)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Пустой адрес']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET address = :address WHERE username = :username");
    $stmt->execute([
        ':address' => $address,
        ':username' => $_SESSION['username']
    ]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Пользователь не найден или адрес не изменился']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>
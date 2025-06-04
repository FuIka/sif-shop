<?php

session_start();

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

require_once '../includes/db.php';

try {    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if ($id <= 0 || empty($username)) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректные данные']);
        exit;
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username=:username, password=:password, is_admin=:is_admin WHERE id=:id");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':is_admin' => $is_admin,
            ':id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username=:username, is_admin=:is_admin WHERE id=:id");
        $stmt->execute([
            ':username' => $username,
            ':is_admin' => $is_admin,
            ':id' => $id
        ]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>
<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, войдите, чтобы удалить чат.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_POST['other_user_id'] ?? null;

if (!is_numeric($other_user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный ID пользователя.']);
    exit;
}

$sql = "DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Чат удален.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при удалении чата.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error]);
}

$conn->close();
?>
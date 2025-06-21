<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, войдите, чтобы просмотреть сообщения.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['other_user_id'] ?? null;

if (!is_numeric($other_user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный ID пользователя.']);
    exit;
}

$sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['status' => 'success', 'messages' => $messages]);
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error]);
}

$conn->close();
?>
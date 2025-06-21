<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, войдите, чтобы отправить сообщение.']);
    exit;
}

$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? null;

if (!is_numeric($receiver_id) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Неверные данные.']);
    exit;
}

$sender_id = $_SESSION['user_id'];

$sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Сообщение отправлено.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при отправке сообщения.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error]);
}

$conn->close();
?>
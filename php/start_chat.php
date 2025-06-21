<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;

if ($receiver_id <= 0) {
    die("Неверный ID получателя.");
}

$sql_check = "SELECT 1 FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);


$stmt_check->execute();
$stmt_check->store_result();
$dialog_exists = $stmt_check->num_rows > 0;
$stmt_check->close();


if (!$dialog_exists) {
    $message = "Здравствуйте, меня заинтерисовал ваш товар!";
    $sql_insert = "INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt_insert->execute();
    $stmt_insert->close();
     error_log("start_chat.php: New dialog created");
}  else {
     error_log("start_chat.php: Dialog exists, no new dialog created");
}


header("Location: ../message.php?user_id=" . $receiver_id);
exit;
?>
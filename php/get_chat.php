<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>Пожалуйста, войдите, чтобы просмотреть сообщения.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_user_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if (!$selected_user_id) {
    echo "<p>Выберите диалог слева, чтобы просмотреть сообщения.</p>";
    exit;
}

$messages = [];
$sql_messages = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
if ($stmt_messages = $conn->prepare($sql_messages)) {
    $stmt_messages->bind_param("iiii", $user_id, $selected_user_id, $selected_user_id, $user_id);
    $stmt_messages->execute();
    $result_messages = $stmt_messages->get_result();

    while ($row = $result_messages->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt_messages->close();
} else {
    echo "<p>Ошибка подготовки запроса для сообщений: " . $conn->error . "</p>";
    exit;
}

function getUsername($conn, $user_id) {
    $sql = "SELECT name FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return htmlspecialchars($row['name']);
        }
        $stmt->close();
    }
    return "Неизвестный пользователь";
}
?>

<h2>Чат с <?php echo getUsername($conn, $selected_user_id); ?></h2>
<div class="chat-messages" id="chat-messages">
    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-item <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                <p><?php echo htmlspecialchars($message['message']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Здесь пока нет сообщений. Начните общение!</p>
    <?php endif; ?>
</div>
<form class="message-form" id="message-form">
    <input type="text" placeholder="Введите сообщение" id="message-text"/>
    <button type="submit">Отправить</button>
</form>
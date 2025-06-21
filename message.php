<?php
ini_set('display_errors', 0);
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$selected_user_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : null;

$dialog_exists = false;
if ($selected_user_id) {
    $sql_check_dialog = "SELECT 1 FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    if ($stmt_check_dialog = $conn->prepare($sql_check_dialog)) {
        $stmt_check_dialog->bind_param("iiii", $user_id, $selected_user_id, $selected_user_id, $user_id);
        $stmt_check_dialog->execute();
        $stmt_check_dialog->store_result();
        $dialog_exists = $stmt_check_dialog->num_rows > 0;
        $stmt_check_dialog->close();
    } else {
        die("Ошибка подготовки запроса для проверки диалога: " . $conn->error);
    }
}

$sql_users = "SELECT DISTINCT
                CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END AS other_user_id,
                (SELECT u.name FROM users u WHERE u.id = CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END) AS other_username
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?";

if ($stmt_users = $conn->prepare($sql_users)) {
    $stmt_users->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt_users->execute();
    $result_users = $stmt_users->get_result();

    $users = [];
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt_users->close();
} else {
    die("Ошибка подготовки запроса: " . $conn->error);
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

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Сообщения - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="icon" href="favicon.png" type="image/x-icon" />
    <style>
        .message-item {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .message-item.received {
            background-color: #f0f0f0;
            text-align: left;
        }

        .message-item.sent {
            background-color: #e2f7cb;
            text-align: right;
        }

        .messages-sidebar ul {
            list-style: none;
            padding: 0;
        }

        .messages-sidebar ul li {
            padding: 10px;
            cursor: pointer;
        }

        .messages-sidebar ul li.active {
            background-color: #e2f7cb;
            font-weight: bold;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <a href="index.php" class="logo">Руки.ру</a>
        <form class="search-form auth" action="category.php" method="GET">
          <input type="hidden" name="category" value="Другие">
          <input type="text" name="search" placeholder="Поиск">
          <button type="submit">Найти</button>
        </form>
        <div class="auth-buttons">
            <a id="login-link" href="login.php">Войти</a>
            <a
                    id="profile-link"
                    href="profile.php"
                    class="button-primary"
                    id="profile-link"
            >Личный кабинет</a
            >
            <a href="post-ad.php" class="button-primary">Разместить объявление</a>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <h1>Сообщения</h1>
        <div class="messages-content">
            <aside class="messages-sidebar">
                <h2>Диалоги</h2>
                <ul>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <li data-user-id="<?php echo htmlspecialchars($user['other_user_id']); ?>"
                                class="<?php echo ($selected_user_id == $user['other_user_id']) ? 'active' : ''; ?>">
                                Чат с <?php echo htmlspecialchars($user['other_username']); ?>
                                <div class="delete-chat-button" data-user-id="<?php echo htmlspecialchars($user['other_user_id']); ?>">&times;</div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>У вас пока нет диалогов.</li>
                    <?php endif; ?>
                </ul>
            </aside>
            <section class="messages-main">
    <div id="chat-container">
        <?php if ($selected_user_id): ?>
            <?php
            $has_dialog = false;
            foreach ($users as $user) {
                if ($user['other_user_id'] == $selected_user_id) {
                    $has_dialog = true;
                    break;
                }
            }
            ?>
            <?php if ($has_dialog): ?>
                <h2>Чат с <?php echo getUsername($conn, $selected_user_id); ?></h2>
                <div class="chat-messages" id="chat-messages">
                    </div>
                <form class="message-form" id="message-form">
                    <input type="text" placeholder="Введите сообщение" id="message-text"/>
                    <button type="submit">Отправить</button>
                </form>
            <?php else: ?>
                <p>У вас еще нет диалога с этим пользователем. Начните общение!</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Выберите диалог слева, чтобы просмотреть сообщения.</p>
        <?php endif; ?>
    </div>
</section>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; 2025 Руки.ру</p>
    </div>
</footer>

<script>
    const loginLink = document.getElementById("login-link");
    const profileLink = document.getElementById("profile-link");

    async function checkSession() {
        try {
            const response = await fetch("php/check_session.php");
            const data = await response.json();

            if (data.loggedIn) {
                loginLink.style.display = "none";
                profileLink.style.display = "block";
            } else {
                loginLink.style.display = "block";
                profileLink.style.display = "none";
            }
        } catch (error) {
            console.error("Error checking session:", error);
        }
    }

    document.addEventListener("DOMContentLoaded", checkSession);
</script>

<script src="js/check_session.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-container');
    let selectedUserId = null;

    const dialogList = document.querySelector('.messages-sidebar ul');
    if (dialogList) {
        dialogList.addEventListener('click', function(event) {
            const target = event.target;
            const dialogContainer = document.querySelector(".messages-sidebar");
            if (target.tagName === 'LI' && !target.classList.contains('delete-chat-button')) {
                dialogContainer.classList.toggle("active");
                document.querySelector(".messages-main").classList.toggle("active");

                selectedUserId = target.dataset.userId;
                Array.from(dialogList.children).forEach(li => li.classList.remove('active'));
                target.classList.add('active');

                loadChat(selectedUserId);
            }
        });

        dialogList.addEventListener('click', function(event) {
            const target = event.target;
            if (target.classList.contains('delete-chat-button')) {
                const otherUserId = target.dataset.userId;
                if (confirm('Вы уверены, что хотите удалить этот чат?')) {
                    deleteChat(otherUserId);
                }
                event.stopPropagation();
            }
        });
    }
    function loadChat(userId) {
        const chatMessages = document.getElementById('chat-messages');
        let scrollPosition = 0;

        if (chatMessages) {
            scrollPosition = chatMessages.scrollTop;
        }

        fetch(`php/get_chat.php?user_id=${userId}`)
            .then(response => response.text())
            .then(data => {
                chatContainer.innerHTML = data;
                attachEventListeners();
                selectedUserId = userId;

                const newChatMessages = document.getElementById('chat-messages');
                if (newChatMessages) {
                    newChatMessages.scrollTop = scrollPosition;
                }
            })
            .catch(error => {
                console.error('Error loading chat:', error);
                chatContainer.innerHTML = '<p>Failed to load chat.</p>';
            });
    }

    function attachEventListeners() {
        const messageForm = document.getElementById('message-form');
        const chatMessages = document.getElementById('chat-messages');
        const messageText = document.getElementById('message-text');

        if (messageForm) {
            messageForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const message = messageText.value.trim();
                if (message === '') {
                    return;
                }

                sendMessage(message, selectedUserId);
            });
        }

         if (chatMessages) {
             setTimeout(function() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 0);
         }
    }

    function sendMessage(message, receiverId) {
        console.log('sendMessage() вызывается');
        console.log('receiverId:', receiverId);

        fetch('php/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'receiver_id=' + receiverId + '&message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const messageText = document.getElementById('message-text');
                if (messageText) {
                    messageText.value = '';
                }
                setTimeout(function() {
                    loadChat(receiverId);
                }, 100);
            } else {
                alert('Ошибка отправки сообщения: ' + data.message);
            }
        });
    }

    const initialSelectedUser = document.querySelector('.messages-sidebar ul li.active');
    if (initialSelectedUser) {
        selectedUserId = initialSelectedUser.dataset.userId;
        loadChat(selectedUserId);
    }
});

function deleteChat(otherUserId) {
    fetch('php/delete_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'other_user_id=' + otherUserId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Чат удален.');
            window.location.href = 'message.php';
        } else {
            alert('Ошибка при удалении чата: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting chat:', error);
        alert('Ошибка при удалении чата.');
    });
}
</script>
</body>
</html>
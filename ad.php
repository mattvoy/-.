<?php

session_start();
require_once 'php/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$ad_id = $_GET['id'];

$sql = "SELECT ads.*, users.phone, ads.created_at FROM ads LEFT JOIN users ON ads.user_id = users.id WHERE ads.id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $ad = $result->fetch_assoc();

        $image_urls_str = $ad['image_url'];
        $image_urls = !empty($image_urls_str) ? explode(",", $image_urls_str) : [];
    } else {
        header("Location: index.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
};

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo htmlspecialchars($ad['title'] ?? 'Объявление'); ?> - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="icon" href="favicon.png" type="image/x-icon" />
    <style>
        .description {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <a href="index.php" class="logo">Руки.ру</a>
        <button class="search-toggle">
                <img src="img/search.png" alt="">
            </button>
        <div class="search-container">
          <form class="search-form" action="category.php" method="GET">
            <input type="hidden" name="category" value="Другие">
            <input type="text" name="search" placeholder="Поиск">
            <button type="submit">Найти</button>
          </form>
        </div>
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
        <?php if (isset($ad)): ?>
            <h1><?php echo htmlspecialchars($ad['title'] ?? 'Без названия'); ?></h1>
            <div class="dec-details">
                <div class="dec-images-slider">
                    <?php if (!empty($image_urls)): ?>
                        <div class="slide-container">
                            <?php foreach ($image_urls as $index => $image_url): ?>
                                <img class="slide" data-index="<?php echo $index; ?>"
                                     src="php/<?php echo htmlspecialchars(trim($image_url)); ?>"
                                     alt="Изображение объявления">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Нет изображений.</p>
                    <?php endif; ?>
                    <div class="slider-controls">
                        <button class="prev-slide">Предыдущий</button>
                        <button class="next-slide">Следующий</button>
                    </div>
                </div>
                <div class="dec-info">
                    <p class="price"><?php echo htmlspecialchars($ad['price'] ?? 'Не указана'); ?> руб.</p>
                    <p>Город: <?php echo htmlspecialchars($ad['town'] ?? 'Не указан'); ?></p>
                    <p>Категория: <?php echo htmlspecialchars($ad['category'] ?? 'Не указана'); ?></p>
                </div>
                <div class="dec-actions">
                    <?php $seller_id = $ad['user_id'];?>
                    <form action="php/start_chat.php" method="post">
                        <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($seller_id); ?>">
                        <button class="button-primary" type="submit">Написать продавцу</button>
                    </form>
                    <button class="phone" id="show-phone-button">Показать телефон</button>
                    <form method="post" action="php/add_to_favorites.php">
                        <input type="hidden" name="ad_id" value="<?php echo htmlspecialchars($ad['id']); ?>">
                        <button class="favorite__btn" type="submit">Добавить в избранное</button>
                    </form>
                    <div class="dec-description">
                        <p>Дата публикации: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($ad['created_at']))); ?></p>
                        <h2>Описание</h2>
                        <p class="description"><?php echo htmlspecialchars($ad['description'] ?? 'Нет описания'); ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Объявление не найдено.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; 2025 Руки.ру</p>
    </div>
</footer>

<div class="modal" id="phone-modal">
    <div class="modal-content">
        <span class="close-button" id="close-modal-button">&times;</span>
        <h2>Внимание!</h2>
        <p>Будьте осторожны! Продавец может запросить предоплату или попросить перейти по подозрительным ссылкам.
            <ul>
                <li>Никогда не переходите по ссылкам, полученным от продавца.</li>
                <li>Не переводите деньги заранее, если не уверены в продавце.</li>
            </ul>
        </p>
        <p>Настоящий номер телефона: <span id="phone-number">***-***-***</span></p>
        <button class="button-primary" id="confirm-show-phone">Показать номер</button>
    </div>
</div>

<script>
      document.addEventListener("DOMContentLoaded", function () {
        const searchToggle = document.querySelector(".search-toggle");
        const searchContainer = document.querySelector(".search-container");

        searchToggle.addEventListener("click", function () {
          searchContainer.classList.toggle("active");
        });
      });
    </script>

<script>
    const showPhoneButton = document.getElementById("show-phone-button");
    const phoneModal = document.getElementById("phone-modal");
    const closeModalButton = document.getElementById("close-modal-button");
    const confirmShowPhoneButton = document.getElementById("confirm-show-phone");
    const phoneNumberSpan = document.getElementById("phone-number");

    const actualPhoneNumber = "<?php echo htmlspecialchars($ad["phone"] ?? "Номер не указан"); ?>";

    function openModal() {
        phoneModal.style.display = "block";
    }

    function closeModal() {
        phoneModal.style.display = "none";
    }

    showPhoneButton.addEventListener("click", openModal);

    closeModalButton.addEventListener("click", closeModal);

    confirmShowPhoneButton.addEventListener("click", function () {
        phoneNumberSpan.textContent = actualPhoneNumber;
    });

    window.addEventListener("click", function (event) {
        if (event.target == phoneModal) {
            closeModal();
        }
    });
</script>
<script src="js/slider.js"></script>

<script src="js/check_session.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const contactSellerButton = document.getElementById('contact-seller');

    if (contactSellerButton) {
        contactSellerButton.addEventListener('click', function() {
            const sellerId = contactSellerButton.dataset.sellerId;

            if (!sellerId) {
                alert('Не удалось получить ID продавца.');
                return;
            }


            window.location.href = 'message.php?user_id=' + sellerId;
        });
    }
});
</script>
</body>
</html>

<?php
$conn->close();
?>
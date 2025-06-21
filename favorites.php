<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT ads.*, favorites.created_at AS favorite_added_at, ads.image_url
        FROM favorites
        JOIN ads ON favorites.ad_id = ads.id
        WHERE favorites.user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Ошибка при подготовке запроса: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="favicon.png" type="image/x-icon" />
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
        <div class="container favorites">
            <h1>Избранное</h1>
            <?php if (isset($result) && $result->num_rows > 0): ?>
                <div class="favorites-list">
                    <?php while ($favorite_ad = $result->fetch_assoc()): ?>
                      <div class="favorite-ad">
                      <?php
                        $image_urls = explode(",", $favorite_ad['image_url']);
                        $first_image = !empty($image_urls) ? trim($image_urls[0]) : '';
                      ?>
                      <?php if (!empty($first_image)): ?>
                          <img src="php/<?php echo htmlspecialchars($first_image); ?>" alt="Изображение объявления">
                      <?php endif; ?>
                        <h3><?php echo htmlspecialchars($favorite_ad['title']); ?></h3>
                        <p>Цена: <?php echo htmlspecialchars($favorite_ad['price']); ?> руб.</p>
                        <p>Дата добавления в избранное: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($favorite_ad['favorite_added_at']))); ?></p>
                        <a href="ad.php?id=<?php echo $favorite_ad['id']; ?>">Подробнее...</a>
                        <form method="post" action="php/remove_from_favorites.php">
                            <input type="hidden" name="ad_id" value="<?php echo $favorite_ad['id']; ?>">
                            <button type="submit" class="remove-from-favorites-button">Удалить из избранного</button>
                        </form>
                      </div>
                    <?php endwhile; ?>
                </div>
              <?php else: ?>
                <p>У вас нет избранных объявлений.</p>
              <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Руки.ру</p>
        </div>
    </footer>
    <script src="js/check_session.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const searchToggle = document.querySelector(".search-toggle");
        const searchContainer = document.querySelector(".search-container");

        searchToggle.addEventListener("click", function () {
          searchContainer.classList.toggle("active");
        });
      });
    </script>
</body>
</html>
<?php
if (isset($stmt)) {
    $stmt->close();
}
if ($conn) {
    $conn->close();
}
?>
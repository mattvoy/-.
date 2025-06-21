<?php
ini_set('display_errors', 0);
session_start();
require_once 'php/db.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * , created_at FROM ads WHERE user_id = ?";
$ads = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $image_urls = explode(",", $row['image_url']);
            $first_image = !empty($image_urls[0]) ? trim($image_urls[0]) : '';
            if (strpos($first_image, "../") === 0) {
              $first_image = substr($first_image, 3);
            }

            $ads[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'price' => $row['price'],
                'created_at' => $row['created_at'],
                'first_image' => $first_image,
            ];
        }
    }
    $stmt->close();
} else {
    echo "Ошибка подготовки запроса: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Личный кабинет - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="favicon.png" type="image/x-icon" />
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
      <div class="container prof">
        <h1>Личный кабинет</h1>
        <div class="profile-content">
          <button class="profile-sidebar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
          <div class="profile-container">
            <aside class="profile-sidebar">
              <h2>Меню</h2>
              <ul>
                <li><a href="#">Мои объявления</a></li>
                <li><a href="favorites.php">Избранные</a></li>
                <li><a href="message.php">Сообщения</a></li>
                <li><a href="php/logout.php">Выйти</a></li>
              </ul>
            </aside>
          </div>
          <section class="profile-main">
            <h2>Мои объявления</h2>
            <div class="dec-grid">
            <?php if (count($ads) > 0): ?>
            <?php foreach ($ads as $ad): ?>
                <div class="dec-item">
                    <a href="ad.php?id=<?php echo $ad['id']; ?>">
                        <img class="dec-image" src="php/<?php echo htmlspecialchars($ad['first_image']); ?>" alt="Товар">
                        <div class="dec-info">
                            <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <p class="price"><?php echo htmlspecialchars($ad['price']); ?> руб.</p>
                        </div>
                    </a>
                    <form method="POST" class="dec__bottom" action="php/delete_ad.php" >
                    <p class="date__public"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($ad['created_at']))); ?></p>
                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                        <button type="submit" class="delete-ad-button" onclick="return confirm('Вы уверены, что хотите удалить это объявление?');">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>У вас пока нет объявлений.</p>
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

    <script src="js/check_session.js"></script>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const profileToggle = document.querySelector(".profile-sidebar-toggle");
        const profileContainer = document.querySelector(".profile-container");

        profileToggle.addEventListener("click", function () {
          profileContainer.classList.toggle("active");
        });
      });
    </script>

  </body>
</html>

<?php
  session_start();
  require_once 'php/db.php';

  $sql = "SELECT id, title, description, price, town, image_url FROM ads ORDER BY id DESC LIMIT 8";
$result = $conn->query($sql);
$ads = [];

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
            'town' => $row['town'],
            'first_image' => $first_image,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" href="favicon.png" type="image/x-icon" />
    <title>Руки.ру</title>
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
        <section class="categories">
          <h2>Категории</h2>
          <div class="category-grid">
            <a href="category.php?category=Одежда" class="category-item">
              <img src="img/wear.svg" alt="Одежда" />
              <span>Одежда</span>
            </a>
            <a href="category.php?category=Электроника" class="category-item">
              <img src="img/electronic.svg" alt="Электроника" />
              <span>Электроника</span>
            </a>
            <a href="category.php?category=Мебель" class="category-item">
              <img src="img/farniture.svg" alt="Мебель" />
              <span>Мебель</span>
            </a>
            <a href="category.php?category=Авто" class="category-item">
              <img src="img/car.svg" alt="Авто" />
              <span>Авто</span>
            </a>
            <a href="category.php?category=Работа" class="category-item">
              <img src="img/job.svg" alt="Работа" />
              <span>Работа</span>
            </a>
            <a href="category.php?category=Другое" class="category-item">
              <img src="img/more.svg" alt="Другое" />
              <span>Другое/Всё</span>
            </a>
          </div>
        </section>

        <section class="latest-dec">
          <h2>Последние объявления</h2>
          <div class="dec-grid">
          <?php if (count($ads) > 0): ?>
                    <?php foreach ($ads as $ad): ?>
                        <div class="dec-item">
                            <a href="ad.php?id=<?php echo $ad['id']; ?>">
                              <img src="php/<?php echo htmlspecialchars($ad['first_image']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" />
                                <div class="dec-info">
                                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                                    <p class="price"><?php echo htmlspecialchars($ad['price']); ?> руб.</p>
                                    <p class="location"><?php echo htmlspecialchars($ad['town']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Объявлений пока нет.</p>
                <?php endif; ?>
          </div>
        </section>
      </div>
    </main>

    <footer>
      <div class="container">
        <p>&copy; 2025 Руки.ру</p>
      </div>
    </footer>

    <script src="js/check_session.js"></script>
  </body>

  <script>
      document.addEventListener("DOMContentLoaded", function () {
        const searchToggle = document.querySelector(".search-toggle");
        const searchContainer = document.querySelector(".search-container");

        searchToggle.addEventListener("click", function () {
          searchContainer.classList.toggle("active");
        });
      });
    </script>
</html>

<?php
  $conn->close();
?>
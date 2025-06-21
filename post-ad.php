<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Разместить объявление - Руки.ру</title>
    <link rel="stylesheet" href="css/style.css" />
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
      <div class="container">
        <h1>Разместить объявление</h1>

        <form method="post" action="php/post_ad_process.php" class="post-ad-form" enctype="multipart/form-data">
          <div class="form-group">
            <label for="category">Категория:</label>
            <select name="category" id="category">
              <option value="">Выберите категорию</option>
              <option value="одежда">Одежда</option>
              <option value="электроника">Электроника</option>
              <option value="мебель">Мебель</option>
              <option value="авто">Авто</option>
              <option value="работа">Работа</option>
              <option value="другое">Другое</option>
            </select>
          </div>

          <div class="form-group">
            <label for="title">Название товара:</label>
            <input name="title" type="text" id="title" placeholder="Введите название" />
          </div>

          <div class="form-group">
            <label for="description">Описание:</label>
            <textarea name="description"
              id="description"
              rows="5"
              placeholder="Подробное описание товара"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="images">Фотографии (до 3-х):</label>
            <input name="images[]" multiple accept="image/*" type="file" id="images"/>
          </div>

          <div class="form-group">
            <label for="price">Цена (руб.):</label>
            <input name="price" type="number" id="price" placeholder="Введите цену" />
          </div>

          <div class="form-group">
            <label for="condition">Состояние:</label>
            <select name="condition" id="condition">
              <option value="">Выберите состояние</option>
              <option value="new">Новый</option>
              <option value="used">Б/у</option>
              <option value="excellent">Отличное</option>
              <option value="middle">Среднее</option>
              <option value="bad">Плохое</option>
            </select>
          </div>

          <div class="form-group">
            <label for="city">Город:</label>
            <input name="town"
              type="text"
              id="city"
              placeholder="Введите название города"
            />
            <div class="autocomplete-items" id="autocomplete-items"></div>
          </div>

          <script src="js/post-ad.js"></script>

          <button type="submit" class="button-primary">Опубликовать</button>
        </form>
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

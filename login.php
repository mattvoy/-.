<?php
  session_start();

  ini_set('display_errors', 0);

  if($_SESSION['user']) {
    header('Location: profile.php');
  }

?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Авторизация - Руки.ру</title>
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
          <a href="login.php" class="button-primary">Войти</a>
          <a class="post-dec" href="post-ad.php">Разместить объявление</a>
        </div>
      </div>
    </header>

    <main>
      <div class="container auth">
        <h1>Авторизация</h1>
        <form action="php/singin.php" method="post" class="auth-form">
          <?php 
            if ($_SESSION['message']) {
              echo '<p class="msg"> ' . $_SESSION['message'] . ' </p>';
            }
            unset($_SESSION['message']);
          ?>
          <div class="form-group">
            <label for="login-email">Email:</label>
            <input name="email" type="email" id="login-email" placeholder="Введите email" />
          </div>
          <div class="form-group">
            <label for="login-password">Пароль:</label>
            <input name="password"
              type="password"
              id="login-password"
              placeholder="Введите пароль"
            />
          </div>
          <button type="submit" class="button-primary">Войти</button>
          <p>
            Еще нет аккаунта? - <a href="register.php">Зарегистрироваться</a>
          </p>
        </form>
      </div>
    </main>

    <footer class="footer__auth">
      <div class="container">
        <p>&copy; 2025 Руки.ру</p>
      </div>
    </footer>
  </body>
</html>

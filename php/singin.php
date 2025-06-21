<?php 
  session_start();
  require_once 'db.php';

  $email = $_POST['email'];
  $password = $_POST['password'];

  if (empty($email) || empty($password)) {
    $_SESSION['message'] = 'Заполните все поля!';
    header("Location: ../login.php");
    exit();
  }

  $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Неверный email или пароль!';
    header("Location: ../login.php");
    exit();
  }

  $user = $result->fetch_assoc();
  $hashedPassword = $user['password'];

  if (password_verify($password, $hashedPassword)) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    header("Location: ../profile.php");
  } else {
    $_SESSION['message'] = 'Неверный email или пароль!';
    header("Location: ../login.php");
  }

  $stmt->close();
  $conn->close();
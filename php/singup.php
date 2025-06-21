<?php 
  session_start();
  require_once 'db.php';

  $name = $_POST['name'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['password-confirm'];
  

  if (empty($email) || empty($password) || empty($confirmPassword) || empty($username)) {
    $_SESSION['message'] = 'Заполните все поля!';
    header("Location: ../register.php");
      exit();
  }
  
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Неккоректный формат email!';
    header("Location: ../register.php");
      exit();
  }
  
  if ($password !== $confirmPassword) {
    $_SESSION['message'] = 'Пароли не совпадают!';
    header("Location: ../register.php");
      exit();
  }
  
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    $_SESSION['message'] = 'Пользователь с таким email уже существует!';
    header("Location: ../register.php");
    exit();
  }
  
  $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
  
  $stmt = $conn->prepare("INSERT INTO `users` (`name`, `phone`, `email`, `password`) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $phone, $email, $hashedPassword);
  
  if ($stmt->execute()) {
    $_SESSION['message'] = 'Вы успешно зарегистрировались!';
    header("Location: ../login.php");
  } else {
    $_SESSION['message'] = 'Произошла неизвестная ошибка!';
    header("Location: ../register.php");
  }
  
  $stmt->close();
  $conn->close();
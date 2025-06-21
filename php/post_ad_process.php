<?php 
  require_once 'db.php';
  session_start();

  $title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$town = $_POST['town'];
$category = $_POST['category'];
$condition = $_POST['condition'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Вы не авторизованы."]);
    exit();
}

$user_id = $_SESSION['user_id'];

$errors = [];

if (empty($title)) {
    $errors[] = "Заполните название товара.";
}
if (empty($description)) {
    $errors[] = "Заполните описание товара.";
}
if (empty($price) || !is_numeric($price)) {
    $errors[] = "Укажите корректную цену.";
}
if (empty($town)) {
    $errors[] = "Выберите город.";
}
if (empty($category)) {
    $errors[] = "Выберите категорию.";
}
if (empty($condition)) {
    $errors[] = "Выберите состояние товара.";
}

$image_urls = [];
  $max_images = 3;
  $upload_dir = "uploads/";

  if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0777, true);
  }

  if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
      $image_count = count($_FILES['images']['name']);
      if ($image_count > $max_images) {
          $errors[] = "Можно загрузить не более $max_images изображений.";
      } else {
          for ($i = 0; $i < $image_count; $i++) {
              $image_name = $_FILES['images']['name'][$i];
              $image_tmp = $_FILES['images']['tmp_name'][$i];
              $image_size = $_FILES['images']['size'][$i];
              $image_error = $_FILES['images']['error'][$i];

              if ($image_error === UPLOAD_ERR_OK) {
                  $file_info = pathinfo($image_name);
                  $file_extension = strtolower($file_info['extension']);
                  $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                  if (in_array($file_extension, $allowed_extensions)) {
                      $new_image_name = uniqid() . "." . $file_extension;
                      $destination = $upload_dir . $new_image_name;

                      if (move_uploaded_file($image_tmp, $destination)) {
                          $image_urls[] = $destination;
                      } else {
                          $errors[] = "Ошибка при загрузке изображения $image_name.";
                      }
                  } else {
                      $errors[] = "Неверный формат файла $image_name. Разрешены: " . implode(", ", $allowed_extensions) . ".";
                  }
              } else {
                  $errors[] = "Ошибка при загрузке изображения $image_name. Код ошибки: " . $image_error;
              }
          }
      }
  }

  if (empty($errors)) {
      $image_urls_str = implode(",", $image_urls);

      $sql = "INSERT INTO `ads` ( `title`, `description`, `price`, `town`, `category`, `condition`, `image_url`, `user_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
      if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("sssssssi", $title, $description, $price, $town, $category, $condition, $image_urls_str, $user_id);
          if ($stmt->execute()) {

            header("Location: ../index.php");

          } else {
            header("Location: ../post-ad.php");
          }
          $stmt->close();
      } else {
        header("Location: ../post-ad.php");
      }
  } else {
    header("Location: ../post-ad.php");
  }
  $conn->close();
<?php
  session_start();

  $loggedIn = isset($_SESSION['user_id']);

  echo json_encode(['loggedIn' => $loggedIn]);
?>
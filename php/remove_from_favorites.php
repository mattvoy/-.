<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_id = $_POST['ad_id'];

if (!is_numeric($ad_id)) {
    header("Location: ../favorites.php?error=invalid_ad_id");
    exit;
}

$sql = "DELETE FROM favorites WHERE user_id = ? AND ad_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $ad_id);
    if ($stmt->execute()) {
        header("Location: ../favorites.php?success=removed");
    } else {
        header("Location: ../favorites.php?error=remove_failed");
    }
    $stmt->close();
} else {
    header("Location: ../favorites.php?error=prepare_failed");
}
$conn->close();
?>
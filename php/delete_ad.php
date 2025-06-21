<?php 
session_start();
require_once 'db.php';

if (!isset($_POST['ad_id']) || !is_numeric($_POST['ad_id'])) {
    header("Location: ../profile.php");
    exit();
}
$ad_id = $_POST['ad_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: ../profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$check_sql = "SELECT user_id FROM ads WHERE id = ? AND user_id = ?";
if ($check_stmt = $conn->prepare($check_sql)) {
    $check_stmt->bind_param("ii", $ad_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        header("Location: ../profile.php");
        exit();
    }
    $check_stmt->close();
} else {
    header("Location: ../profile.php");
    exit();
}


$delete_sql = "DELETE FROM ads WHERE id = ?";
if ($delete_stmt = $conn->prepare($delete_sql)) {
    $delete_stmt->bind_param("i", $ad_id);
    if ($delete_stmt->execute()) {
        header("Location: ../profile.php");
        exit();
    } else {
        header("Location: ../profile.php");
        exit();
    }
    $delete_stmt->close();
} else {
    header("Location: ../profile.php");
    exit();
}

$conn->close();
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
    header("Location: ../ad.php?id=" . $ad_id . "&error=invalid_ad_id");
    exit;
}

$sql_check = "SELECT id FROM favorites WHERE user_id = ? AND ad_id = ?";
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param("ii", $user_id, $ad_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header("Location: ../ad.php?id=" . $ad_id . "&error=already_in_favorites");
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();
} else {
    header("Location: ../ad.php?id=" . $ad_id . "&error=check_failed");
    exit;
}

$sql = "INSERT INTO favorites (user_id, ad_id) VALUES (?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $ad_id);
    if ($stmt->execute()) {
        header("Location: ../ad.php?id=" . $ad_id . "&success=added_to_favorites");
    } else {
        header("Location: ../ad.php?id=" . $ad_id . "&error=add_failed");
    }
    $stmt->close();
} else {
    header("Location: ../ad.php?id=" . $ad_id . "&error=prepare_failed");
}
$conn->close();
?>
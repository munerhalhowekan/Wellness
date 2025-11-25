<?php
session_start();
include 'db-connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["remaining" => 0]);
    exit();
}

$userID = $_SESSION['user_id'];
$today = date("l");

// نجيب السعرات المتبقية لليوم الحالي
$stmt = $conn->prepare("
    SELECT remaining_kcal 
    FROM diet_progress
    WHERE userID = ? AND day = ?
    LIMIT 1
");
$stmt->bind_param("is", $userID, $today);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    "remaining" => (int)($res['remaining_kcal'] ?? 0)
]);
?>

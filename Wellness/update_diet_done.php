<?php
session_start();
include 'db-connection.php';

// منع الوصول بدون تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "no-user"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$health  = $_SESSION['health_condition'] ?? null;

// تحديد جدول الدايت حسب المرض
if ($health === 'PCOS') {
    $dietTable = 'diet_pcos';
} elseif ($health === 'Insulin Resistance') {
    $dietTable = 'diet_insulin_resist';
} elseif ($health === 'Gluten Intolerance') {
    $dietTable = 'diet_glutenfree';
} else {
    $dietTable = 'diet_pcos';
}

// اليوم الحالي مثال: Monday
$day = date('l');

// جلب السعرات اليومية من جدول الدايت
$getCal = $conn->prepare("SELECT total_calories_per_day FROM $dietTable WHERE day = ?");
$getCal->bind_param("s", $day);
$getCal->execute();
$calRow = $getCal->get_result()->fetch_assoc();
$getCal->close();

$dailyKcal = (int)($calRow['total_calories_per_day'] ?? 0);

// تصفير السعرات
$reset = $conn->prepare("
    UPDATE diet_progress
    SET remaining_kcal = 0
    WHERE userID = ? AND day = ?
");
$reset->bind_param("is", $user_id, $day);
$reset->execute();
$reset->close();

// إرجاع القيمة للجافاسكربت
echo json_encode([
    "day" => $day,
    "remaining" => 0
]);
?>

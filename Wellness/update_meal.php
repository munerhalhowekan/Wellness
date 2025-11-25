<?php
session_start();
include 'db-connection.php';

// منع الوصول بدون تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "no-user"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$health = $_SESSION['health_condition'] ?? NULL;

$day   = $_POST['day']  ?? date('l'); 
$meal  = $_POST['meal'] ?? '';
$column = $meal . "_done"; // breakfast_done / lunch_done / dinner_done

// تحديد جدول الدايت حسب الحالة الصحية
if ($health === 'PCOS') {
    $dietTable = 'diet_pcos';
} elseif ($health === 'Insulin Resistance') {
    $dietTable = 'diet_insulin_resist';
} elseif ($health === 'Gluten Intolerance') {
    $dietTable = 'diet_glutenfree';
} else {
    $dietTable = 'diet_pcos';
}

// 1) قلب حالة الوجبة
$toggle = $conn->prepare("
    UPDATE diet_progress 
    SET $column = NOT $column 
    WHERE userID = ? AND day = ?
");
$toggle->bind_param("is", $user_id, $day);
$toggle->execute();
$toggle->close();

// 1.5) إعادة جلب القيم الجديدة بعد التعديل
$getProg2 = $conn->prepare("
    SELECT breakfast_done, lunch_done, dinner_done 
    FROM diet_progress 
    WHERE userID = ? AND day = ?
");
$getProg2->bind_param("is", $user_id, $day);
$getProg2->execute();
$prog = $getProg2->get_result()->fetch_assoc();
$getProg2->close();

// 2) جلب السعرات
$getMeals = $conn->prepare("
    SELECT b_calories, l_calories, d_calories, total_calories_per_day 
    FROM $dietTable 
    WHERE day = ?
");
$getMeals->bind_param("s", $day);
$getMeals->execute();
$mealInfo = $getMeals->get_result()->fetch_assoc();
$getMeals->close();

// 3) حساب السعرات المتبقية
$remaining = $mealInfo['total_calories_per_day'];

if ($prog['breakfast_done']) $remaining -= $mealInfo['b_calories'];
if ($prog['lunch_done'])     $remaining -= $mealInfo['l_calories'];
if ($prog['dinner_done'])    $remaining -= $mealInfo['d_calories'];

// 4) تخزين السعرات الجديدة
$save = $conn->prepare("
    UPDATE diet_progress 
    SET remaining_kcal = ? 
    WHERE userID = ? AND day = ?
");

$save->bind_param("iis", $remaining, $user_id, $day);
$save->execute();
$save->close();

// 5) إرجاع JSON للجافاسكربت
echo json_encode([
    "day"            => $day,
    "breakfast_done" => (int)$prog['breakfast_done'],
    "lunch_done"     => (int)$prog['lunch_done'],
    "dinner_done"    => (int)$prog['dinner_done'],
    "remaining"      => $remaining
]);
?>

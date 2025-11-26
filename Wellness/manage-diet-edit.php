<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db-connection.php";

// التأكد من وجود البيانات
if (!isset($_POST['table']) || !isset($_POST['id'])) {
    exit("❌ Missing parameters");
}

$table  = $_POST['table'];
$id     = intval($_POST['id']);

$breakfast      = $_POST['breakfast'] ?? "";
$b_calories     = intval($_POST['b_calories'] ?? 0);

$lunch          = $_POST['lunch'] ?? "";
$l_calories     = intval($_POST['l_calories'] ?? 0);

$dinner         = $_POST['dinner'] ?? "";
$d_calories     = intval($_POST['d_calories'] ?? 0);

// حساب السعرات النهائية
$total = $b_calories + $l_calories + $d_calories;

// تحديد اسم العمود الأساسي لكل جدول
$pk = "";
if ($table == "diet_pcos")            $pk = "PcosID";
if ($table == "diet_insulin_resist")  $pk = "InsulinID";
if ($table == "diet_glutenfree")      $pk = "GlutenfreeID";

if ($pk == "") exit("❌ Invalid table name");

// تجهيز الاستعلام
$stmt = $conn->prepare("
    UPDATE $table 
    SET breakfast=?, b_calories=?, 
        lunch=?, l_calories=?, 
        dinner=?, d_calories=?, 
        total_calories_per_day=?
    WHERE $pk=?
");

$stmt->bind_param(
    "sisisiii",
    $breakfast, $b_calories,
    $lunch, $l_calories,
    $dinner, $d_calories,
    $total, $id
);

if ($stmt->execute()) {
    echo "✅ Diet plan updated successfully.";
} else {
    echo "❌ Error updating plan: " . $stmt->error;
}

$stmt->close();
$conn->close();

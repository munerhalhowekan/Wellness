<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db-connection.php";

// التأكد من البيانات
if (!isset($_POST['table']) || !isset($_POST['id'])) {
    exit("❌ Missing parameters");
}

$table = $_POST['table'];
$id    = intval($_POST['id']);

$pk = "";
if ($table == "diet_pcos")            $pk = "PcosID";
if ($table == "diet_insulin_resist")  $pk = "InsulinID";
if ($table == "diet_glutenfree")      $pk = "GlutenfreeID";

if ($pk == "") exit("❌ Invalid table name");

// تنفيذ الحذف
$stmt = $conn->prepare("DELETE FROM $table WHERE $pk=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "🗑️ Deleted successfully.";
} else {
    echo "❌ Error deleting: " . $stmt->error;
}

$stmt->close();
$conn->close();

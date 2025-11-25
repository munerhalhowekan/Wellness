<?php
// أضيفي هذه الأسطر الثلاثة لإظهار أي أخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
 * ========================================
 * ملف إعدادات اللياقة (مدموج)
 * ========================================
 */
session_start();
include 'db-connection.php';

// --- حماية الصفحة ---
// إذا لم يكن المستخدم مسجلاً، أعده لصفحة الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: user-login.php");
    exit();
}

// --- معالجة الفورم ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // جلب الاختيار
    $fitnessLevel = $_POST['fitnessLevel'] ?? NULL;
    $user_id = $_SESSION['user_id'];

    if ($fitnessLevel) {
        // تحديث قاعدة البيانات
        $stmt = $conn->prepare("UPDATE users SET fitness_level = ? WHERE UserID = ?");
        $stmt->bind_param("si", $fitnessLevel, $user_id);
        $stmt->execute();
        $stmt->close();

        // توجيه للخطوة التالية (صفحة اختيار المرض)
        header("Location: user-illness.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Choose Fitness Level - Wellness</title>
  <link rel="stylesheet" href="style.css">
  
  <style>
    .options-container { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 40px; }
    .option-card { background-color: #1f1f1f; border: 2px solid #333; border-radius: 15px; width: 150px; height: 160px; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #fff; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 10px rgba(0,0,0,0.4); }
    .option-card:hover { border-color: #ff6600; box-shadow: 0 0 20px rgba(255, 102, 0, 0.4); transform: translateY(-5px); }
    .option-card.selected { border-color: #ff6600; box-shadow: 0 0 25px rgba(255, 102, 0, 0.6); }
    .option-card img { width: 60px; margin-bottom: 10px; }
    .next-btn { margin-top: 40px; width: 200px; }
    @media (max-width: 600px) { .option-card { width: 120px; height: 140px; } }
  </style>
  
 
  <script>
    function selectOption(element, level) {
      document.querySelectorAll(".option-card").forEach(card => {
        card.classList.remove("selected");
      });
      element.classList.add("selected");
      document.getElementById("selectedLevel").value = level;
    }
  </script>
</head>
<body>
  <div class="form-container">
    <h1>Select Your Fitness Level</h1>
    <p class="subtitle">Choose the level that best matches your experience</p>

    <form action="user-options.php" method="post">
      <input type="hidden" id="selectedLevel" name="fitnessLevel" required>

      <div class="options-container">
        <div class="option-card" onclick="selectOption(this, 'beginner')">
          <img src="https://cdn-icons-png.flaticon.com/512/825/825540.png" alt="Beginner Icon">
          <span>Beginner</span>
        </div>

        <div class="option-card" onclick="selectOption(this, 'intermediate')">
          <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Intermediate Icon">
          <span>Intermediate</span>
        </div>

        <div class="option-card" onclick="selectOption(this, 'advanced')">
          <img src="https://cdn-icons-png.flaticon.com/512/1048/1048941.png" alt="Advanced Icon">
          <span>Advanced</span>
        </div>
      </div>

      <button type="submit" class="btn form-btn next-btn">Next</button>
    </form>
  </div>
  
  </body>
</html>
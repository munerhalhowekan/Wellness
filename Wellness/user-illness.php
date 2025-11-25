<?php
// إظهار الأخطاء أثناء التطوير (ممكن تشيلينها بعدين)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db-connection.php';

// حماية الصفحة: لازم يكون مستخدم عادي "user"
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: user-login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$error = "";

// معالجة الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasIllness       = $_POST['hasIllness']       ?? '';
    $healthCondition  = $_POST['health_condition'] ?? null;

    // لو قال "No"
    if ($hasIllness === 'no') {
        // نخلي health_condition = NULL
        $stmt = $conn->prepare("UPDATE users SET health_condition = NULL WHERE UserID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['health_condition'] = null;

        header("Location: user-dashboard.php");
        exit();
    }

    // لو قال "Yes" لازم يختار مرض من الثلاثة
    if ($hasIllness === 'yes') {
        $allowed = ['PCOS', 'Insulin Resistance', 'Gluten Intolerance'];

        if (!in_array($healthCondition, $allowed, true)) {
            $error = "Please select your health condition.";
        } else {
            // نحدّث الداتابيس
            $stmt = $conn->prepare("UPDATE users SET health_condition = ? WHERE UserID = ?");
            $stmt->bind_param("si", $healthCondition, $userId);
            $stmt->execute();
            $stmt->close();

            // نحدّث السيشن
            $_SESSION['health_condition'] = $healthCondition;

            // نوجّه المستخدم للداشبورد (اللي يختار جدول الدايت بناءً على health_condition)
            header("Location: user-dashboard.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Health Information - Wellness</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background-color: #1e1e1e;
      color: white;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    .container {
      background-color: #2b2b2b;
      padding: 40px 30px;
      border-radius: 25px;
      box-shadow: 0 0 20px rgba(255, 102, 0, 0.4);
      text-align: center;
      width: 90%;
      max-width: 600px;
      transition: all 0.3s ease;
    }

    h1 {
      color: #ff6600;
      margin-bottom: 10px;
      font-size: 2rem;
    }

    h2 {
      color: #fff;
      font-weight: 500;
      margin-bottom: 25px;
    }

    .error {
      color: #ff8080;
      margin-bottom: 15px;
      font-size: 0.95rem;
    }

    .options {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    .option-card {
      background-color: #333;
      border: 2px solid transparent;
      border-radius: 15px;
      padding: 20px 24px;
      min-width: 140px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .option-card:hover {
      border-color: #ff6600;
      transform: scale(1.05);
    }

    .option-card p {
      color: #fff;
      font-weight: 500;
      margin: 0;
    }

    .hidden {
      display: none;
    }

    .next-btn {
      margin-top: 30px;
      background-color: #ff6600;
      color: white;
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .next-btn:hover {
      background-color: #ff8533;
    }
  </style>

  <script>
    function showIllnessOptions() {
      const yesNoSection   = document.getElementById("yesNoSection");
      const illnessSection = document.getElementById("illnessSection");

      // نحجز إن المستخدم عنده مرض
      document.getElementById("hasIllness").value = "yes";

      yesNoSection.classList.add("hidden");
      illnessSection.classList.remove("hidden");
    }

    function submitNoIllness() {
      document.getElementById("hasIllness").value = "no";
      document.getElementById("healthCondition").value = "";
      document.getElementById("illnessForm").submit();
    }

    function selectIllness(illness) {
      document.getElementById("hasIllness").value = "yes";
      document.getElementById("healthCondition").value = illness;
      document.getElementById("illnessForm").submit();
    }
  </script>
</head>
<body>
  <div class="container">
    <h1>Wellness</h1>
    <h2>Do you suffer from an illness?</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- فورم واحد يتحكم في كل الخيارات -->
    <form id="illnessForm" method="post" action="user-illness.php">
      <input type="hidden" name="hasIllness" id="hasIllness">
      <input type="hidden" name="health_condition" id="healthCondition">

      <!-- Yes / No options -->
      <div id="yesNoSection" class="options">
        <div class="option-card" onclick="showIllnessOptions()">
          <p>Yes</p>
        </div>

        <div class="option-card" onclick="submitNoIllness()">
          <p>No</p>
        </div>
      </div>

      <!-- Illness options (الثلاثة فقط) -->
      <div id="illnessSection" class="options hidden">
        <div class="option-card" onclick="selectIllness('PCOS')">
          <p>PCOS</p>
        </div>

        <div class="option-card" onclick="selectIllness('Insulin Resistance')">
          <p>Insulin Resistance</p>
        </div>

        <div class="option-card" onclick="selectIllness('Gluten Intolerance')">
          <p>Gluten Intolerance</p>
        </div>
      </div>
    </form>
  </div>
</body>
</html>

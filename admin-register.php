<?php
// إظهار الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * ========================================
 * ملف تسجيل الأدمن (مدموج)
 * ========================================
 */
include 'db-connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $userType = 'admin';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ---------------------------------------------------------
    // 1. التحقق من قيود كلمة المرور (Validation)
    // ---------------------------------------------------------
    if (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long!";
    }
    elseif (!preg_match("/[A-Z]/", $password) || 
            !preg_match("/[a-z]/", $password) || 
            !preg_match("/[0-9]/", $password) || 
            !preg_match("/[\W]/", $password)) {
        
        $error_message = "Password must include uppercase, lowercase, number, and special character!";
    }

    // ---------------------------------------------------------
    // 2. إذا لم يكن هناك أخطاء، نكمل التسجيل
    // ---------------------------------------------------------
    if (empty($error_message)) {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT UserID FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This email is already in use!";
        } else {
            $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $fname, $lname, $email, $password_hash, $userType);
            
            if ($insert->execute()) {
                // توجيه لصفحة الدخول مع رسالة نجاح
                header("Location: admin-login.php?success=1");
                exit();
            } else {
                $error_message = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Registration - Wellness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Create Your Account</h1>
    <p class="subtitle">Join FitTrack and start your fitness journey</p>

    <form action="admin-register.php" method="post">
      <div class="input-group">
        <label for="fname">First Name</label>
        <input type="text" id="fname" name="fname" required>
      </div>
      <div class="input-group">
        <label for="lname">Last Name</label>
        <input type="text" id="lname" name="lname" required>
      </div>
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <small style="color:#aaa; font-size:12px;">(Must contain 8+ chars, Uppercase, Lowercase, Number, Symbol)</small>
      </div>

      <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
      <?php endif; ?>

      <button type="submit" class="btn form-btn">Register</button>
    </form>

    <p class="login-text">
      Already have an account? <a href="admin-login.php">Login here</a>
    </p>
  </div>
</body>
</html>
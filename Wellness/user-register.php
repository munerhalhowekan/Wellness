<?php
// أضيفي هذه الأسطر الثلاثة لإظهار أي أخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/*
 * ========================================
 * ملف تسجيل المستخدم (مدموج)
 * ========================================
 */
session_start();
include 'db-connection.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $userType = 'user';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT UserID FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error_message = "This email is already in use!";
    } else {
        // --- استخدام الحقول الجديدة "firstName" و "lastName" ---
        $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("sssss", $fname, $lname, $email, $password_hash, $userType);
        
        if ($insert->execute()) {
            // تسجيل الدخول تلقائياً
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['role'] = $userType;
            $_SESSION['name'] = $fname; // نستخدم الاسم الأول للترحيب

            // توجيه لصفحة الإعدادات
            header("Location: user-options.php");
            exit();
        } else {
            $error_message = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration - Wellness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Create Your Account</h1>
    <p class="subtitle">Join Wellness and start your fitness journey</p>

    <form action="user-register.php" method="post">
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
      </div>

      <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
      <?php endif; ?>

      <button type="submit" class="btn form-btn">Register</button>
    </form>

    <p class="login-text">
      Already have an account? <a href="user-login.php">Login here</a>
    </p>
  </div>
</body>
</html>

<?php
// أضيفي هذه الأسطر الثلاثة لإظهار أي أخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ...
/*
 * ========================================
 * ملف دخول الأدمن (مدموج)
 * ========================================
 */
session_start();
include 'db-connection.php'; 

$error_message = '';

// تحقق إذا كان قادماً من تسجيل ناجح
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $error_message = '<p style="color: green; text-align: center;">Registration successful! You can now log in.</p>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['firstName']; // نستخدم الاسم الأول للترحيب

            header("Location: admin-dashboard.php"); // (صفحة زملائك)
            exit;
        } else {
           $error_message = '<p style="color: red; text-align: center;">Incorrect password!</p>';
        }
    } else {
        $error_message = '<p style="color: red; text-align: center;">This email (admin) does not exist!</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Wellness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Welcome Back to Wellness</h1>
    <p class="subtitle">Login to continue your fitness journey</p>

    <form action="admin-login.php" method="post" class="register-form">
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <?php echo $error_message; // طباعة رسالة الخطأ أو النجاح ?>

      <button type="submit" class="btn form-btn">Login</button>
    </form>

    <p class="login-text">
      Don’t have an account? <a href="admin-register.php">Register here</a>
    </p>
  </div>
</body>
</html>

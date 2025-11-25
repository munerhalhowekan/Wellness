<?php
// أضيفي هذه الأسطر الثلاثة لإظهار أي أخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
 * ========================================
 * ملف دخول المستخدم (مدموج)
 * ========================================
 */
session_start();
include 'db-connection.php'; 

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    session_unset();
session_destroy();
session_start();

    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password_hash'])) {
            
            session_regenerate_id(true);

            
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['firstName']; // نستخدم الاسم الأول

           
$_SESSION['health_condition'] = $row['health_condition'];

            // توجيه بناءً على إكمال التسجيل
            if ($row['fitness_level'] == NULL) {
                header("Location: user-options.php");
            } else {
                header("Location: user-dashboard.php"); // (صفحة زميلتك)
            }
            exit;
        } else {
           $error_message = "Incorrect password!";
        }
    } else {
        $error_message = "This email (user) does not exist!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Login - Wellness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Welcome Back to Wellness</h1>
    <p class="subtitle">Login to continue your fitness journey</p>

    <form action="user-login.php" method="post" class="register-form">
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

      <button type="submit" class="btn form-btn">Login</button>
    </form>

    <p class="login-text">
      Don’t have an account? <a href="user-register.php">Register here</a>
    </p>
  </div>
</body>
</html>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db-connection.php'; 

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // المتغير الافتراضي للنجاح
    $login_success = false;
    $user_data = null;

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // التحقق من الباسوورد
        if (password_verify($password, $row['password_hash'])) {
            $login_success = true;
            $user_data = $row;
        }
    }

    // -----------------------------------------------------------
    // التعامل مع النتيجة (إما نجاح كامل أو فشل عام)
    // -----------------------------------------------------------
    if ($login_success) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_data['UserID'];
        $_SESSION['role'] = $user_data['role'];
        $_SESSION['name'] = $user_data['firstName']; 
        $_SESSION['health_condition'] = $user_data['health_condition'];

        if (empty($user_data['fitness_level'])) {
            header("Location: user-options.php");
        } else {
            header("Location: user-dashboard.php"); 
        }
        exit;
    } else {
        // 🔒 رسالة أمنية موحدة لا تكشف سبب الخطأ
        $error_message = "Incorrect email or password!";
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
      <div class="input-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="input-group"><label>Password</label><input type="password" name="password" required></div>
      <?php if (!empty($error_message)): ?><p style="color: red; text-align: center;"><?php echo $error_message; ?></p><?php endif; ?>
      <button type="submit" class="btn form-btn">Login</button>
    </form>
    <p class="login-text">Don’t have an account? <a href="user-register.php">Register here</a></p>
  </div>
</body>
</html>
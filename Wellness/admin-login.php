<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db-connection.php'; 
$error_message = '';

if (isset($_GET['success'])) {
    $error_message = '<p style="color: green;">Registration successful! Please log in.</p>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $login_success = false;
    $admin_data = null;

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            $login_success = true;
            $admin_data = $row;
        }
    }

    // -----------------------------------------------------------
    // التعامل مع النتيجة
    // -----------------------------------------------------------
    if ($login_success) {
        $_SESSION['user_id'] = $admin_data['UserID'];
        $_SESSION['role'] = 'admin';
        header("Location: admin-dashboard.php"); 
        exit;
    } else {
        // 🔒 رسالة أمنية موحدة
        $error_message = "Incorrect email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Admin Login</h1>
    <form action="admin-login.php" method="post" class="register-form">
      <div class="input-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="input-group"><label>Password</label><input type="password" name="password" required></div>
      <?php if (!empty($error_message)): ?><p style="color: red; text-align: center;"><?php echo $error_message; ?></p><?php endif; ?>
      <button type="submit" class="btn form-btn">Login</button>
    </form>
    <p class="login-text">Create Admin? <a href="admin-register.php">Register here</a></p>
  </div>
</body>
</html>
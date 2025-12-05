<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db-connection.php';

$error_message = '';

// تعريف المتغيرات بقيم فارغة افتراضياً لتجنب الأخطاء عند فتح الصفحة لأول مرة
$fname = '';
$lname = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userType = 'user';
    // هنا نحتفظ بالقيم التي أدخلها المستخدم
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 1. التحقق من قوة الباسوورد
    if (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long!";
    } elseif (!preg_match("/[A-Z]/", $password) || 
              !preg_match("/[a-z]/", $password) || 
              !preg_match("/[0-9]/", $password) || 
              !preg_match("/[\W]/", $password)) {
        $error_message = "Password must include uppercase, lowercase, number, and special character!";
    }

    // 2. إذا الباسوورد سليم، نكمل
    if (empty($error_message)) {
        $check = $conn->prepare("SELECT UserID FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error_message = "This email is already in use!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $fname, $lname, $email, $password_hash, $userType);
            
            if ($insert->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['role'] = $userType;
                $_SESSION['name'] = $fname;
                
                header("Location: user-options.php");
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
  <title>User Registration - Wellness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Create Your Account</h1>
    <p class="subtitle">Join Wellness and start your fitness journey</p>
    <form action="user-register.php" method="post">
      <div class="input-group">
        <label>First Name</label>
        <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required>
      </div>
      <div class="input-group">
        <label>Last Name</label>
        <input type="text" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required>
      </div>
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
        <small style="color:#aaa; font-size:12px;">(8+ chars, Uppercase, Lowercase, Number, Symbol)</small>
      </div>
      <?php if (!empty($error_message)): ?><p style="color: red; text-align: center;"><?php echo $error_message; ?></p><?php endif; ?>
      <button type="submit" class="btn form-btn">Register</button>
    </form>
    <p class="login-text">Already have an account? <a href="user-login.php">Login here</a></p>
  </div>
</body>
</html>
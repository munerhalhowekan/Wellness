<?php
// أضيفي هذه الأسطر الثلاثة لإظهار أي أخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
session_unset();   
session_destroy(); 

// توجيه المستخدم لصفحة البداية
header("Location: Register.html"); 
exit;
?>s
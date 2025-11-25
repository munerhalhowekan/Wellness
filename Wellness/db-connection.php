<?php
/*
 * ملف الاتصال بقاعدة البيانات (Database Connection)
 * هذه هي الإعدادات الافتراضية. قد تحتاج لتغييرها بناءً على جهازك.
 */

// اسم السيرفر (عادة 'localhost')
$servername = "localhost"; 

// اسم المستخدم (عادة 'root' في XAMPP)
$username = "root"; 

// كلمة المرور (عادة فارغة '' في XAMPP)
$password = ""; 

// اسم قاعدة البيانات كما في ملف wellness.sql
$dbname = "wellness"; 

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
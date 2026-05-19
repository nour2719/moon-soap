<?php
session_start();

// حذف كوكي "تذكرني"
if(isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

session_destroy();
header('Location: admin-login.php');  // ← تم التعديل هنا
exit();
?>
<?php
session_start(); // بدء الجلسة
session_destroy(); // تدمير الجلسة الحالية

header('location: index.php'); // إعادة التوجيه إلى الصفحة الرئيسية
?>

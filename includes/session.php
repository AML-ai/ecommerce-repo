<?php
include __DIR__.'/conn.php'; // تضمين ملف الاتصال بقاعدة البيانات
session_start(); // بدء جلسة جديدة أو استئناف جلسة حالية

if (isset($_SESSION['admin'])) {
    // إذا كان المستخدم مديرًا، يتم توجيهه إلى صفحة لوحة التحكم الخاصة بالمدير
    header('location: admin/home.php');
}

if (isset($_SESSION['user'])) {
    // إذا كان المستخدم عاديًا، يتم جلب بياناته من قاعدة البيانات
    try {
        $conn = $pdo->open(); // فتح اتصال بقاعدة البيانات
        $stmt = $conn->prepare("SELECT * FROM users WHERE id=:id"); // تحضير استعلام لجلب بيانات المستخدم

        $stmt->execute(['id' => $_SESSION['user']]); // تنفيذ الاستعلام باستخدام معرف المستخدم في الجلسة
        $user = $stmt->fetch(); // جلب بيانات المستخدم
    } catch (PDOException $e) {
        // في حال حدوث خطأ أثناء الاتصال
        echo "There is some problem in connection: " . $e->getMessage();
    }

    $pdo->close(); // إغلاق الاتصال بقاعدة البيانات
} else {
    // إذا لم يكن المستخدم مسجل الدخول، يتم التحقق من وجود السلة
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array(); // إنشاء سلة جديدة إذا لم تكن موجودة
    }

    if (empty($_SESSION['cart'])) {
        $cart_count = 0; // إذا كانت السلة فارغة، يتم ضبط العدد إلى 0
    } else {
        $cart_count = count($_SESSION['cart']); // حساب عدد العناصر في السلة
    }
}

?>
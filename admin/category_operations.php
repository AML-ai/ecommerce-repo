<?php

include 'includes/session.php'; // بدء الجلسة
$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

try {
    if (isset($_POST['add'])) {
        $name = $_POST['name']; // الحصول على اسم الفئة من النموذج
        $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM category WHERE name=:name");
        $stmt->execute(['name' => $name]); // التحقق إذا كانت الفئة موجودة
        $row = $stmt->fetch();

        if ($row['numrows'] > 0) {
            $_SESSION['error'] = 'Category already exist'; // في حال وجود الفئة
        } else {
            $stmt = $conn->prepare("INSERT INTO category (name) VALUES (:name)"); // إضافة الفئة إلى قاعدة البيانات
            $stmt->execute(['name' => $name]);
            $_SESSION['success'] = 'Category added successfully'; // رسالة نجاح
        }

    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id']; // الحصول على معرف الفئة المراد حذفها
        $stmt = $conn->prepare("DELETE FROM category WHERE id=:id"); // حذف الفئة من قاعدة البيانات
        $stmt->execute(['id' => $id]);
        $_SESSION['success'] = 'Category deleted successfully'; // رسالة نجاح

    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id']; // الحصول على معرف الفئة
        $name = $_POST['name']; // الحصول على الاسم المعدل للفئة
        $stmt = $conn->prepare("UPDATE category SET name=:name WHERE id=:id"); // تحديث بيانات الفئة
        $stmt->execute(['name' => $name, 'id' => $id]);
        $_SESSION['success'] = 'Category updated successfully'; // رسالة نجاح
    } else {
        $_SESSION['error'] = 'Fill up category form first'; // في حال عدم ملء النموذج
    }
} catch (PDOException $e) {
    $_SESSION['error'] = $e->getMessage(); // في حال حدوث خطأ في قاعدة البيانات
}
$pdo->close(); // إغلاق الاتصال بقاعدة البيانات

header('location: category.php'); // إعادة التوجيه إلى صفحة الفئات

?>

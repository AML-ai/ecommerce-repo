<?php
include 'includes/session.php'; // بدء الجلسة
// إنشاء معرف فريد للمعاملة
$payid = 'PAY-' . strtoupper(uniqid()) . '-' . strtoupper(bin2hex(random_bytes(5))) . '-' . time();
$date = date('Y-m-d'); // الحصول على تاريخ اليوم

// فتح الاتصال بقاعدة البيانات
$conn = $pdo->open();

try {
    // إدخال المعاملة في جدول المبيعات
    $stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
    $stmt->execute(['user_id' => $user['id'], 'pay_id' => $payid, 'sales_date' => $date]);
    $salesid = $conn->lastInsertId(); // الحصول على معرف المعاملة الذي تم إدخاله

    try {
        // جلب تفاصيل السلة الخاصة بالمستخدم وإضافتها إلى جدول التفاصيل
        $stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
        $stmt->execute(['user_id' => $user['id']]);

        foreach ($stmt as $row) {
            $stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
            $stmt->execute(['sales_id' => $salesid, 'product_id' => $row['product_id'], 'quantity' => $row['quantity']]);
        }

        // مسح السلة بعد إتمام عملية الشراء
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
        $stmt->execute(['user_id' => $user['id']]);

        // رسالة تأكيد للنجاح
        $_SESSION['success'] = 'Transaction successful. Thank you.';
    } catch (PDOException $e) {
        // في حال حدوث خطأ في عملية إضافة التفاصيل
        $_SESSION['error'] = $e->getMessage();
    }
} catch (PDOException $e) {
    // في حال حدوث خطأ في عملية إدخال المبيعات
    $_SESSION['error'] = $e->getMessage();
}

$pdo->close(); // إغلاق الاتصال بقاعدة البيانات
header('location: transactions.php'); // إعادة التوجيه إلى صفحة الملف الشخصي
?>

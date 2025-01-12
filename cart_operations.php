<?php
include 'includes/session.php'; // بدء الجلسة و تضمين ملف الجلسة

$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

$output = array('error' => false); // مصفوفة لتخزين الرسالة والحالة
$pid = $_POST['pid']; // الحصول على نوع العملية (إضافة، تحديث، حذف)
$id = $_POST['id']; // الحصول على معرّف المنتج أو السلة


try {
    // إذا كانت العملية هي تحديث الكمية في السلة
    if ($pid == 'update') {
        $qty = $_POST['qty']; // الحصول على الكمية الجديدة
        if (isset($_SESSION['user'])) { // إذا كان المستخدم مسجلاً الدخول
            // تحديث الكمية في قاعدة البيانات
            $stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id");
            $stmt->execute(['quantity' => $qty, 'id' => $id]);
        } else {
            // إذا كان المستخدم غير مسجل، يتم تحديث الكمية في الجلسة
            foreach ($_SESSION['cart'] as $key => $row) {
                if ($row['productid'] == $id) { // العثور على المنتج في السلة
                    $_SESSION['cart'][$key]['quantity'] = $qty; // تحديث الكمية
                    $output['message'] = 'Updated'; // تعيين الرسالة إلى "تم التحديث"
                }
            }
        }
        $output['message'] = 'Updated'; // رسالة تأكيد التحديث
    }
    // إذا كانت العملية هي حذف المنتج من السلة
    elseif ($pid == 'delete') {
        if (isset($_SESSION['user'])) { // إذا كان المستخدم مسجلاً
            // حذف المنتج من السلة في قاعدة البيانات
            $stmt = $conn->prepare("DELETE FROM cart WHERE id=:id");
            $stmt->execute(['id' => $id]);
            $output['message'] = 'Deleted'; // تعيين الرسالة إلى "تم الحذف"
        } else {
            // إذا كان المستخدم غير مسجل، يتم حذف المنتج من السلة في الجلسة
            foreach ($_SESSION['cart'] as $key => $row) {
                if ($row['productid'] == $id) { // العثور على المنتج في السلة
                    unset($_SESSION['cart'][$key]); // إزالة المنتج من السلة
                    $output['message'] = 'Deleted'; // تعيين الرسالة إلى "تم الحذف"
                }
            }
        }
    }
    // إذا كانت العملية هي إضافة منتج إلى السلة
    elseif ($pid == 'add') {
        $quantity = $_POST['quantity']; // الحصول على الكمية المطلوبة من المنتج

        if (isset($_SESSION['user'])) { // إذا كان المستخدم مسجلاً
            // التحقق من وجود المنتج في السلة باستخدام قاعدة البيانات
            $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM cart WHERE user_id=:user_id AND product_id=:product_id");
            $stmt->execute(['user_id' => $user['id'], 'product_id' => $id]);
            $row = $stmt->fetch();
            if ($row['numrows'] < 1) { // إذا لم يكن المنتج موجوداً في السلة
                // إضافة المنتج إلى السلة في قاعدة البيانات
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $stmt->execute(['user_id' => $user['id'], 'product_id' => $id, 'quantity' => $quantity]);
                $output['message'] = 'Item added to cart'; // تعيين الرسالة إلى "تم إضافة المنتج"
            } else {
                // إذا كان المنتج موجودًا بالفعل في السلة
                $output['error'] = true;
                $output['message'] = 'Product already in cart'; // تعيين الرسالة إلى "المنتج موجود بالفعل في السلة"
            }
        } else {
            // إذا كان المستخدم غير مسجل، يتم التحقق من وجود المنتج في السلة المخزنة في الجلسة
            $exist = array();

            foreach ($_SESSION['cart'] as $row) {
                array_push($exist, $row['productid']); // إضافة معرفات المنتجات في السلة إلى مصفوفة
            }

            // إذا كان المنتج موجودًا في السلة
            if (in_array($id, $exist)) {
                $output['error'] = true;
                $output['message'] = 'Product already in cart'; // تعيين الرسالة إلى "المنتج موجود بالفعل في السلة"
            } else {
                // إضافة المنتج إلى السلة في الجلسة
                $data['productid'] = $id;
                $data['quantity'] = $quantity;

                if (array_push($_SESSION['cart'], $data)) { // إضافة المنتج بنجاح إلى السلة
                    $output['message'] = 'Item added to cart'; // تعيين الرسالة إلى "تم إضافة المنتج"
                } else {
                    // إذا حدث خطأ أثناء إضافة المنتج
                    $output['error'] = true;
                    $output['message'] = 'Cannot add item to cart'; // تعيين الرسالة إلى "تعذر إضافة المنتج إلى السلة"
                }
            }
        }
    }
} catch (PDOException $e) {
    // في حال حدوث خطأ أثناء تنفيذ الاستعلام
    $output['message'] = $e->getMessage(); // إرجاع رسالة الخطأ
}

$pdo->close(); // إغلاق الاتصال بقاعدة البيانات
echo json_encode($output); // إرجاع النتيجة بصيغة JSON
?>

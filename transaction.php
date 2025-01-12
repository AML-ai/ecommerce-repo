<?php
include 'includes/session.php'; // بدء الجلسة

$id = $_POST['id']; // الحصول على معرف المعاملة من الطلب

$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

$output = array('list'=>'');

// جلب تفاصيل المعاملة من جدول التفاصيل والمنتجات
$stmt = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id LEFT JOIN sales ON sales.id=details.sales_id WHERE details.sales_id=:id");
$stmt->execute(['id'=>$id]);

$total = 0; // متغير لحساب الإجمالي
foreach($stmt as $row){
    $output['transaction'] = $row['pay_id']; // رقم المعاملة
    $output['date'] = date('M d, Y', strtotime($row['sales_date'])); // تاريخ المعاملة
    $subtotal = $row['price'] * $row['quantity']; // الحساب الجزئي
    $total += $subtotal;
    $output['list'] .= "
			<tr class='prepend_items'>
				<td>".$row['name']."</td>
				<td>&#36; ".number_format($row['price'], 2)."</td>
				<td>".$row['quantity']."</td>
				<td>&#36; ".number_format($subtotal, 2)."</td>
			</tr>
		";
}

// إضافة الإجمالي الكلي
$output['total'] = '<b>&#36; '.number_format($total, 2).'<b>';
$pdo->close(); // إغلاق الاتصال
echo json_encode($output); // إرجاع البيانات على شكل JSON
?>

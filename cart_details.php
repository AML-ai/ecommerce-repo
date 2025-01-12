<?php
$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

$output = ''; // متغير لتجميع مخرجات جدول السلة
$total = 0; // إجمالي السعر

if (isset($_SESSION['user'])) {
	// إذا كان المستخدم مسجلاً الدخول
	if (isset($_SESSION['cart'])) {
		// إذا كان هناك منتجات في الجلسة، يتم دمجها مع السلة في قاعدة البيانات
		foreach ($_SESSION['cart'] as $row) {
			$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM cart WHERE user_id=:user_id AND product_id=:product_id");
			$stmt->execute(['user_id' => $user['id'], 'product_id' => $row['productid']]);
			$crow = $stmt->fetch();
			if ($crow['numrows'] < 1) {
				// إذا لم يكن المنتج موجودًا في السلة، يتم إضافته
				$stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
				$stmt->execute(['user_id' => $user['id'], 'product_id' => $row['productid'], 'quantity' => $row['quantity']]);
			} else {
				// إذا كان موجودًا، يتم تحديث الكمية
				$stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE user_id=:user_id AND product_id=:product_id");
				$stmt->execute(['quantity' => $row['quantity'], 'user_id' => $user['id'], 'product_id' => $row['productid']]);
			}
		}
		unset($_SESSION['cart']); // مسح السلة من الجلسة
	}

	try {
		$stmt = $conn->prepare("SELECT *, cart.id AS cartid FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user");
		$stmt->execute(['user' => $user['id']]);
		foreach ($stmt as $row) {
			// إعداد تفاصيل المنتج
			$image = 'https://ecommerce-assetsbucket.s3.eu-north-1.amazonaws.com/products/' . (!empty($row['photo']) ? $row['photo'] : 'noimage.jpg');

			$subtotal = $row['price'] * $row['quantity'];
			$total += $subtotal;

			// إضافة المنتج إلى المخرجات
			$output .= "
                <tr>
                    <td><button type='button' data-id='".$row['cartid']."' class='btn btn-danger btn-flat cart_delete'><i class='fa fa-remove'></i></button></td>
                    <td><img src='".$image."' width='30px' height='30px'></td>
                    <td>".$row['name']."</td>
                    <td>&#36; ".number_format($row['price'], 2)."</td>
                    <td class='input-group'>
                        <span class='input-group-btn'>
                            <button type='button' id='minus' class='btn btn-default btn-flat minus' data-id='".$row['cartid']."'><i class='fa fa-minus'></i></button>
                        </span>
                        <input type='text' class='form-control' value='".$row['quantity']."' id='qty_".$row['cartid']."'>
                        <span class='input-group-btn'>
                            <button type='button' id='add' class='btn btn-default btn-flat add' data-id='".$row['cartid']."'><i class='fa fa-plus'></i></button>
                        </span>
                    </td>
                    <td>&#36; ".number_format($subtotal, 2)."</td>
                </tr>
            ";
		}
		$output .= "
            <tr>
                <td colspan='5' align='right'><b>Total</b></td>
                <td><b>&#36; ".number_format($total, 2)."</b></td>
            <tr>
        ";
	} catch (PDOException $e) {
		$output .= $e->getMessage(); // عرض خطأ الاتصال
	}
} else {
	// إذا لم يكن المستخدم مسجلاً الدخول
	if (count($_SESSION['cart']) != 0) {
		$total = 0;
		foreach ($_SESSION['cart'] as $row) {
			$stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname FROM products LEFT JOIN category ON category.id=products.category_id WHERE products.id=:id");
			$stmt->execute(['id' => $row['productid']]);
			$product = $stmt->fetch();
			$image = 'https://ecommerce-assetsbucket.s3.eu-north-1.amazonaws.com/products/' . (!empty($product['photo']) ? $product['photo'] : 'noimage.jpg');
			$subtotal = $product['price'] * $row['quantity'];
			$total += $subtotal;
			$output .= "
                <tr>
                    <td><button type='button' data-id='".$row['productid']."' class='btn btn-danger btn-flat cart_delete'><i class='fa fa-remove'></i></button></td>
                    <td><img src='".$image."' width='30px' height='30px'></td>
                    <td>".$product['name']."</td>
                    <td>&#36; ".number_format($product['price'], 2)."</td>
                    <td class='input-group'>
                        <span class='input-group-btn'>
                            <button type='button' id='minus' class='btn btn-default btn-flat minus' data-id='".$row['productid']."'><i class='fa fa-minus'></i></button>
                        </span>
                        <input type='text' class='form-control' value='".$row['quantity']."' id='qty_".$row['productid']."'>
                        <span class='input-group-btn'>
                            <button type='button' id='add' class='btn btn-default btn-flat add' data-id='".$row['productid']."'><i class='fa fa-plus'></i></button>
                        </span>
                    </td>
                    <td>&#36; ".number_format($subtotal, 2)."</td>
                </tr>
            ";
		}
		$output .= "
            <tr>
                <td colspan='5' align='right'><b>Total</b></td>
                <td><b>&#36; ".number_format($total, 2)."</b></td>
            <tr>
        ";
	} else {
		$output .= "
            <tr>
                <td colspan='6' align='center'>Shopping cart empty</td>
            <tr>
        ";
	}
}

$pdo->close(); // إغلاق الاتصال
echo $output; // طباعة المخرجات
?>

<?php include 'includes/session.php'; // بدء الجلسة ?>
<?php
$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

$slug = $_GET['product']; // الحصول على معرف المنتج من الرابط
try {
    // جلب تفاصيل المنتج بناءً على معرف الـ slug
    $stmt = $conn->prepare("SELECT *, products.name AS prodname, category.name AS catname, products.id AS prodid FROM products LEFT JOIN category ON category.id=products.category_id WHERE slug = :slug");
    $stmt->execute(['slug' => $slug]);
    $product = $stmt->fetch(); // تخزين تفاصيل المنتج
} catch (PDOException $e) {
    // في حال حدوث خطأ في الاستعلام
    echo "There is some problem in connection: " . $e->getMessage();
}

// تحديث عدد المشاهدات للمنتج في حال كان نفس اليوم
$now = date('Y-m-d');
if ($product['date_view'] == $now) {
    $stmt = $conn->prepare("UPDATE products SET counter=counter+1 WHERE id=:id");
    $stmt->execute(['id' => $product['prodid']]);
} else {
    // إذا كان اليوم مختلفًا، يتم إعادة تعيين العداد
    $stmt = $conn->prepare("UPDATE products SET counter=1, date_view=:now WHERE id=:id");
    $stmt->execute(['id' => $product['prodid'], 'now' => $now]);
}
?>
<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
<body class="hold-transition skin-blue layout-top-nav">

<div class="wrapper">

    <?php include 'includes/navbar.php'; ?> <!-- تضمين شريط التنقل -->

    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <div class="row">
                    <div class="col-sm-9">
                        <div class="callout" id="callout" style="display:none">
                            <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
                            <span class="message"></span>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <!-- عرض صورة المنتج -->
                                <img src="<?php echo 'https://ecommerce-assetsbucket.s3.eu-north-1.amazonaws.com/products/'.(!empty($product['photo']) ? $product['photo'] :'noimage.jpg'); ?>" width="100%" class="zoom" data-magnify-src="images/large-<?php echo $product['photo']; ?>">
                                <br><br>
                                <!-- نموذج إضافة المنتج إلى السلة -->
                                <form class="form-inline" id="productForm">
                                    <div class="form-group">
                                        <div class="input-group col-sm-5">
											<span class="input-group-btn">
												<button type="button" id="minus" class="btn btn-default btn-flat btn-lg"><i class="fa fa-minus"></i></button>
											</span>
                                            <input type="text" name="quantity" id="quantity" class="form-control input-lg" value="1">
                                            <span class="input-group-btn">
												<button type="button" id="add" class="btn btn-default btn-flat btn-lg"><i class="fa fa-plus"></i></button>
											</span>
                                            <input type="hidden" value="<?php echo $product['prodid']; ?>" name="id">
                                        </div>
                                        <!-- زر إضافة إلى السلة -->
                                        <button type="submit" class="btn btn-primary btn-lg btn-flat"><i class="fa fa-shopping-cart"></i> Add to Cart</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-6">
                                <!-- عرض تفاصيل المنتج -->
                                <h1 class="page-header"><?php echo $product['prodname']; ?></h1>
                                <h3><b>&#36; <?php echo number_format($product['price'], 2); ?></b></h3>
                                <p><b>Category:</b> <a href="category.php?category=<?php echo $product['cat_slug']; ?>"><?php echo $product['catname']; ?></a></p>
                                <p><b>Description:</b></p>
                                <p><?php echo $product['description']; ?></p>
                            </div>
                        </div>
                        <br>
                        <!-- قسم التعليقات على الفيسبوك -->
                        <div class="fb-comments" data-href="http://localhost/ecommerce/product.php?product=<?php echo $slug; ?>" data-numposts="10" width="100%"></div>
                    </div>
                    <div class="col-sm-3">
                        <?php include 'includes/sidebar.php'; ?> <!-- تضمين الشريط الجانبي -->
                    </div>
                </div>
            </section>
        </div>
    </div>
    <?php $pdo->close(); ?> <!-- إغلاق الاتصال بقاعدة البيانات -->
</div>

<?php include 'includes/scripts.php'; ?> <!-- تضمين السكربتات -->
<script>
    $(function(){
        // عند الضغط على زر "إضافة" لزيادة الكمية
        $('#add').click(function(e){
            e.preventDefault();
            var quantity = $('#quantity').val();
            quantity++;
            $('#quantity').val(quantity); // تحديث الكمية
        });
        // عند الضغط على زر "ناقص" لتقليل الكمية
        $('#minus').click(function(e){
            e.preventDefault();
            var quantity = $('#quantity').val();
            if (quantity > 1) {
                quantity--;
            }
            $('#quantity').val(quantity); // تحديث الكمية
        });
    });
</script>
</body>
</html>

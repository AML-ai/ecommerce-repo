<?php include 'includes/session.php'; ?>
<?php
// الحصول على الفئة من الرابط أو تعيين القيمة الافتراضية 'laptops' إذا لم يتم توفيرها
$slug = $_GET['category'] ?? 'laptops';

// فتح الاتصال بقاعدة البيانات
$conn = $pdo->open();

try {
    // جلب تفاصيل الفئة بناءً على slug الخاص بها
    $stmt = $conn->prepare("SELECT * FROM category WHERE cat_slug = :slug");
    $stmt->execute(['slug' => $slug]);
    $cat = $stmt->fetch(); // تخزين بيانات الفئة في متغير
    $catid = $cat['id']; // الحصول على معرف الفئة
} catch (PDOException $e) {
    // في حال حدوث خطأ أثناء الاتصال أو الاستعلام
    echo "There is some problem in connection: " . $e->getMessage();
}

// إغلاق الاتصال بقاعدة البيانات
$pdo->close();
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>

    <div class="content-wrapper">
        <div class="container">

            <!-- Main content -->
            <section class="content">

                <div class="row">

                    <div class="col-sm-9">
                        <div class="page-header">
                            <div class="dropdown ">
                                <!-- عرض اسم الفئة مع قائمة منسدلة للفئات الأخرى -->
                                <a href="#" class="dropdown-toggle h2" data-toggle="dropdown"><?php echo $cat['name']; ?> <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <?php
                                    try {
                                        // فتح الاتصال بقاعدة البيانات
                                        $conn = $pdo->open();

                                        // جلب جميع الفئات من الجدول
                                        $stmt = $conn->prepare("SELECT * FROM category");
                                        $stmt->execute();
                                        foreach ($stmt as $row) {
                                            // إنشاء رابط لكل فئة
                                            echo "
                                                <li><a href='category.php?category=" . $row['cat_slug'] . "'>" . $row['name'] . "</a></li>
                                            ";
                                        }
                                    } catch (PDOException $e) {
                                        // في حال حدوث خطأ أثناء الاتصال أو الاستعلام
                                        echo "There is some problem in connection: " . $e->getMessage();
                                    }

                                    // إغلاق الاتصال بقاعدة البيانات
                                    $pdo->close();
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <?php
                        // فتح الاتصال بقاعدة البيانات
                        $conn = $pdo->open();

                        try {
                            $inc = 3; // متغير لتنسيق العرض (عدد العناصر في الصف)
                            // جلب المنتجات التي تنتمي إلى الفئة الحالية
                            $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :catid");
                            $stmt->execute(['catid' => $catid]);
                            foreach ($stmt as $row) {
                                // إعداد رابط الصورة: إذا لم تكن الصورة موجودة، استخدم صورة افتراضية
                                $image = 'https://ecommerce-assetsbucket.s3.eu-north-1.amazonaws.com/products/' . (!empty($row['photo']) ? $row['photo'] : 'noimage.jpg');

                                // التحكم في بداية الصف الجديد
                                $inc = ($inc == 3) ? 1 : $inc + 1;
                                if ($inc == 1) echo "<div class='row'>";

                                // عرض المنتج في عمود
                                echo "
                                        <div class='col-sm-4'>
                                            <div class='box box-solid'>
                                                <div class='box-body prod-body'>
                                                    <img src='" . $image . "' width='100%' height='230px' class='thumbnail'>
                                                    <h5><a href='product.php?product=" . $row['slug'] . "'>" . $row['name'] . "</a></h5>
                                                </div>
                                                <div class='box-footer'>
                                                    <b>&#36; " . number_format($row['price'], 2) . "</b>
                                                </div>
                                            </div>
                                        </div>
                                    ";

                                // التحكم في نهاية الصف
                                if ($inc == 3) echo "</div>";
                            }
                            // في حال كان الصف غير مكتمل، اكمله بأعمدة فارغة
                            if ($inc == 1) echo "<div class='col-sm-4'></div><div class='col-sm-4'></div></div>";
                            if ($inc == 2) echo "<div class='col-sm-4'></div></div>";
                        } catch (PDOException $e) {
                            // في حال حدوث خطأ أثناء الاتصال أو الاستعلام
                            echo "There is some problem in connection: " . $e->getMessage();
                        }

                        // إغلاق الاتصال بقاعدة البيانات
                        $pdo->close();
                        ?>
                    </div>
                    <div class="col-sm-3">
                        <?php include 'includes/sidebar.php'; // تضمين الشريط الجانبي ?>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; // تضمين السكربتات ?>
</body>
</html>

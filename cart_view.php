<?php include 'includes/session.php'; // بدء الجلسة ?>
<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php include 'includes/navbar.php'; // تضمين شريط التنقل ?>

    <div class="content-wrapper">
        <div class="container">

            <!-- المحتوى الرئيسي -->
            <section class="content">
                <div class="row">
                    <div class="col-sm-9">
                        <h1 class="page-header">YOUR CART</h1>
                        <div class="box box-solid">
                            <div class="box-body">
                                <!-- جدول عرض محتويات السلة -->
                                <table class="table table-bordered">
                                    <thead>
                                    <th></th> <!-- زر الحذف -->
                                    <th>Photo</th> <!-- صورة المنتج -->
                                    <th>Name</th> <!-- اسم المنتج -->
                                    <th>Price</th> <!-- سعر المنتج -->
                                    <th width="20%">Quantity</th> <!-- الكمية -->
                                    <th>Subtotal</th> <!-- الإجمالي الجزئي -->
                                    </thead>
                                    <tbody id="tbody">
                                    <?php include 'cart_details.php'; // تضمين تفاصيل السلة ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                        // عرض زر تأكيد الطلب إذا كان المستخدم مسجلاً ولديه إجمالي في السلة
                        if (isset($_SESSION['user']) && $total > 0) {
                            echo "
                                    <a href='sales.php' id='pay-button' class='btn btn-primary'> Confirm Order</a>
                                ";
                        } elseif($total > 0) {
                            // عرض رسالة تسجيل الدخول إذا لم يكن المستخدم مسجلاً
                            echo "
                                    <h4>You need to <a href='login.php'>Login</a> to checkout.</h4>
                                ";
                        }
                        ?>
                    </div>
                    <div class="col-sm-3">
                        <?php include 'includes/sidebar.php'; // تضمين الشريط الجانبي ?>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <?php $pdo->close(); // إغلاق الاتصال بقاعدة البيانات ?>
</div>

<?php include 'includes/scripts.php'; // تضمين السكربتات ?>
<script>
    // تعريف المتغير الإجمالي بناءً على السلة
    var total = <?php echo $total;?>;

    $(function(){
        // حذف عنصر من السلة
        $(document).on('click', '.cart_delete', function(e){
            e.preventDefault();
            var id = $(this).data('id'); // الحصول على معرف المنتج
            $.ajax({
                type: 'POST',
                url: 'cart_operations.php', // الملف المسؤول عن عمليات السلة
                data: { pid: 'delete', id: id },
                dataType: 'json',
                success: function(response){
                    if (!response.error) {
                        location.reload(); // تحديث الصفحة عند النجاح
                    }
                }
            });
        });

        // تقليل الكمية
        $(document).on('click', '.minus', function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var qty = $('#qty_'+id).val(); // الكمية الحالية
            if (qty > 1) { qty--; }
            $('#qty_'+id).val(qty); // تحديث الكمية
            $.ajax({
                type: 'POST',
                url: 'cart_operations.php',
                data: { pid: 'update', id: id, qty: qty },
                dataType: 'json',
                success: function(response){
                    if (!response.error) {
                        location.reload();
                    }
                }
            });
        });

        // زيادة الكمية
        $(document).on('click', '.add', function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var qty = $('#qty_'+id).val();
            qty++;
            $('#qty_'+id).val(qty);
            $.ajax({
                type: 'POST',
                url: 'cart_operations.php',
                data: { pid: 'update', id: id, qty: qty },
                dataType: 'json',
                success: function(response){
                    if (!response.error) {
                        location.reload();
                    }
                }
            });
        });
    });
</script>

</body>
</html>

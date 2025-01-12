<?php include 'includes/session.php'; ?>
<?php
if (!isset($_SESSION['user'])) {
    header('location: index.php'); // إذا لم يكن المستخدم مسجلاً، يتم توجيهه إلى الصفحة الرئيسية
}
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
                        <?php
                        // عرض الرسائل (نجاح أو خطأ)
                        if (isset($_SESSION['error'])) {
                            echo "
                                    <div class='callout callout-danger'>
                                        " . $_SESSION['error'] . "
                                    </div>
                                ";
                            unset($_SESSION['error']);
                        }

                        if (isset($_SESSION['success'])) {
                            echo "
                                    <div class='callout callout-success'>
                                        " . $_SESSION['success'] . "
                                    </div>
                                ";
                            unset($_SESSION['success']);
                        }
                        ?>
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h4 class="box-title"><i class="fa fa-calendar"></i> <b>Transaction History</b></h4>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered" id="example1">
                                    <thead>
                                    <th class="hidden"></th>
                                    <th>Date</th>
                                    <th>Transaction#</th>
                                    <th>Amount</th>
                                    <th>Full Details</th>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $conn = $pdo->open();

                                    try {
                                        // جلب جميع المعاملات للمستخدم
                                        $stmt = $conn->prepare("SELECT * FROM sales WHERE user_id=:user_id ORDER BY sales_date DESC");
                                        $stmt->execute(['user_id' => $user['id']]);
                                        foreach ($stmt as $row) {
                                            // جلب تفاصيل المنتجات في المعاملة
                                            $stmt2 = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id WHERE sales_id=:id");
                                            $stmt2->execute(['id' => $row['id']]);
                                            $total = 0;
                                            foreach ($stmt2 as $row2) {
                                                $subtotal = $row2['price'] * $row2['quantity']; // حساب الإجمالي الجزئي
                                                $total += $subtotal;
                                            }
                                            // عرض المعاملات في الجدول
                                            echo "
                                                    <tr>
                                                        <td class='hidden'></td>
                                                        <td>" . date('M d, Y', strtotime($row['sales_date'])) . "</td>
                                                        <td>" . $row['pay_id'] . "</td>
                                                        <td>&#36; " . number_format($total, 2) . "</td>
                                                        <td><button class='btn btn-sm btn-flat btn-info transact' data-id='" . $row['id'] . "'><i class='fa fa-search'></i> View</button></td>
                                                    </tr>
                                                ";
                                        }

                                    } catch (PDOException $e) {
                                        echo "There is some problem in connection: " . $e->getMessage();
                                    }

                                    $pdo->close();
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <?php include 'includes/sidebar.php'; ?>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <?php include 'includes/profile_modal.php'; ?> <!-- تضمين نافذة التفاصيل -->
</div>

<?php include 'includes/scripts.php'; ?>
<script>
    $(function() {
        // عند النقر على زر التفاصيل، عرض التفاصيل في نافذة منبثقة
        $(document).on('click', '.transact', function(e) {
            e.preventDefault();
            $('#transaction').modal('show'); // عرض نافذة التفاصيل
            var id = $(this).data('id');
            $.ajax({
                type: 'POST',
                url: 'transaction.php',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    $('#date').html(response.date);
                    $('#transid').html(response.transaction);
                    $('#detail').prepend(response.list);
                    $('#total').html(response.total);
                }
            });
        });

        // عند إغلاق النافذة، مسح العناصر المضافة
        $("#transaction").on("hidden.bs.modal", function() {
            $('.prepend_items').remove();
        });
    });
</script>
</body>
</html>

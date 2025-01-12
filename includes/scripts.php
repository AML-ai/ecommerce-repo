<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- CK Editor -->
<script src="bower_components/ckeditor/ckeditor.js"></script>
<script>
    $(function () {
        // Datatable
        $('#example1').DataTable()
        //CK Editor
        CKEDITOR.replace('editor1')
    });
</script>

<!-- Custom Scripts -->
<script>
    $(function () {
        $('#productForm').submit(function (e) {
            e.preventDefault();
            $(this).append('<input type="hidden" name="pid" value="add">');
            var product = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: 'cart_operations.php',
                data: product,
                dataType: 'json',
                success: function (response) {
                    $('#callout').show();
                    $('.message').html(response.message);
                    if (response.error) {
                        $('#callout').removeClass('callout-success').addClass('callout-danger');
                    } else {
                        $('#callout').removeClass('callout-danger').addClass('callout-success');
                    }
                }
            });
        });

        $(document).on('click', '.close', function () {
            $('#callout').hide();
        });

    });

</script>
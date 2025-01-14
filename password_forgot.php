<?php include 'includes/session.php'; // بدء الجلسة ?>
<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
<body class="hold-transition login-page">
<div class="login-box">
    <?php
    // عرض رسائل الخطأ والنجاح إذا كانت موجودة في الجلسة
    if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        ";
        unset($_SESSION['error']);
    }
    if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        ";
        unset($_SESSION['success']);
    }
    ?>
    <div class="login-box-body">
        <p class="login-box-msg">Enter email associated with account</p>

        <!-- نموذج إدخال البريد الإلكتروني لإعادة تعيين كلمة المرور -->
        <form action="reset.php" method="POST">
            <div class="form-group has-feedback">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="reset"><i class="fa fa-mail-forward"></i> Send</button>
                </div>
            </div>
        </form>
        <br>
        <!-- رابط العودة إلى صفحة تسجيل الدخول -->
        <a href="login.php">I rememberd my password</a><br>
        <a href="index.php"><i class="fa fa-home"></i> Home</a>
    </div>
</div>

<?php include 'includes/scripts.php' ?> <!-- تضمين السكربتات -->
</body>
</html>

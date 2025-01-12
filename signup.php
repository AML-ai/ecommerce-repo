<?php include 'includes/session.php'; // بدء الجلسة ?>
<?php
if(isset($_SESSION['user'])){
    header('location: cart_view.php'); // إذا كان المستخدم مسجل دخوله مسبقًا، يتم توجيهه إلى صفحة السلة
}
?>
<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
<body class="hold-transition register-page">
<div class="register-box">
    <?php
    if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        "; // عرض رسالة الخطأ إذا كانت موجودة
        unset($_SESSION['error']); // مسح رسالة الخطأ بعد عرضها
    }

    if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        "; // عرض رسالة النجاح إذا كانت موجودة
        unset($_SESSION['success']); // مسح رسالة النجاح بعد عرضها
    }
    ?>
    <div class="register-box-body">
        <p class="login-box-msg">Register a new membership</p> <!-- نص العنوان -->

        <form action="register.php" method="POST">
            <!-- حقل الاسم الأول -->
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="firstname" placeholder="Firstname" value="<?php echo (isset($_SESSION['firstname'])) ? $_SESSION['firstname'] : '' ?>" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>

            <!-- حقل الاسم الأخير -->
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="lastname" placeholder="Lastname" value="<?php echo (isset($_SESSION['lastname'])) ? $_SESSION['lastname'] : '' ?>"  required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>

            <!-- حقل البريد الإلكتروني -->
            <div class="form-group has-feedback">
                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo (isset($_SESSION['email'])) ? $_SESSION['email'] : '' ?>" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <!-- حقل كلمة المرور -->
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <!-- حقل إعادة كلمة المرور -->
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="repassword" placeholder="Retype password" required>
                <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
            </div>

            <hr>

            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="signup"><i class="fa fa-pencil"></i> Sign Up</button> <!-- زر التسجيل -->
                </div>
            </div>
        </form>
        <br>
        <a href="login.php">I already have a membership</a><br> <!-- رابط تسجيل الدخول إذا كان المستخدم يملك حساب -->
        <a href="index.php"><i class="fa fa-home"></i> Home</a> <!-- رابط الصفحة الرئيسية -->
    </div>
</div>

<?php include 'includes/scripts.php' ?> <!-- تضمين السكربتات -->
</body>
</html>

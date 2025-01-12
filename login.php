<?php include 'includes/session.php'; // بدء الجلسة ?>
<?php
// إذا كان المستخدم مسجل دخوله بالفعل، يتم إعادة توجيهه إلى صفحة السلة
if(isset($_SESSION['user'])){
    header('location: cart_view.php');
}
?>
<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
<body class="hold-transition login-page">
<div class="login-box">
    <?php
    // إذا كانت هناك رسالة خطأ في الجلسة، يتم عرضها
    if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        ";
        unset($_SESSION['error']); // مسح رسالة الخطأ
    }
    // إذا كانت هناك رسالة نجاح في الجلسة، يتم عرضها
    if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        ";
        unset($_SESSION['success']); // مسح رسالة النجاح
    }
    ?>
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <!-- نموذج تسجيل الدخول -->
        <form action="verify.php" method="POST">
            <div class="form-group has-feedback">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="login"><i class="fa fa-sign-in"></i> Sign In</button>
                </div>
            </div>
        </form>
        <br>
        <!-- روابط مساعدة للمستخدمين مثل استرجاع كلمة المرور والتسجيل -->
        <a href="password_forgot.php">I forgot my password</a><br>
        <a href="signup.php" class="text-center">Register a new membership</a><br>
        <a href="index.php"><i class="fa fa-home"></i> Home</a>
    </div>
</div>

<?php include 'includes/scripts.php' ?> <!-- تضمين السكربتات -->
</body>
</html>

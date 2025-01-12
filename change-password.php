<?php
use Aws\Exception\AwsException;

require 'vendor/autoload.php'; // تحميل مكتبات AWS SDK
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // تحميل إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تحميل الدوال المساعدة لـ AWS

if (isset($_POST['change_password'])) { // إذا تم تقديم النموذج لتغيير كلمة المرور
    $email = $_SESSION['reset_email']; // الحصول على البريد الإلكتروني من الجلسة
    $currentPassword = trim($_POST['current_password']); // الحصول على كلمة المرور الحالية
    $newPassword = trim($_POST['new_password']); // الحصول على كلمة المرور الجديدة
    $confirmPassword = trim($_POST['confirm_password']); // الحصول على تأكيد كلمة المرور الجديدة

    // التحقق إذا كانت كلمة المرور الجديدة تطابق تأكيد كلمة المرور
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.'; // إذا لم تتطابق كلمات المرور
        header('location: change-password.php');
        exit();
    }

    try {
        // تهيئة عميل AWS Cognito
        $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

        // تغيير كلمة المرور باستخدام Cognito
        $cognitoClient->adminSetUserPassword([
            'UserPoolId' => COGNITO_USER_POOL_ID,
            'Username' => $email,
            'Password' => $newPassword,
            'Permanent' => true, // تعيين كلمة المرور الجديدة بشكل دائم
        ]);

        $_SESSION['success'] = 'Your password has been updated successfully. Please log in.'; // رسالة النجاح
        unset($_SESSION['reset_email']); // مسح البريد الإلكتروني من الجلسة
        header('location: login.php'); // إعادة التوجيه إلى صفحة تسجيل الدخول
        exit();
    } catch (AwsException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getAwsErrorMessage(); // إذا حدث خطأ أثناء الاتصال بـ AWS
    } catch (Exception $e) {
        $_SESSION['error'] = 'There was an error processing your request.'; // إذا حدث خطأ آخر
    }
}
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
<div class="login-box">
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger text-center'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success text-center'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    ?>
    <div class="login-box-body">
        <p class="login-box-msg">Change Your Password</p>
        <form action="change-password.php" method="POST">
            <div class="form-group has-feedback">
                <input type="password" class="form-control"  name="current_password" placeholder="Current Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="change_password">Change Password</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>

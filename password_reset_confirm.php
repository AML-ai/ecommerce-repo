<?php
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;

require 'vendor/autoload.php'; // تحميل مكتبات AWS SDK
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تضمين أدوات AWS

if (isset($_POST['confirm'])) {
    $email = $_SESSION['reset_email']; // استرجاع البريد الإلكتروني المخزن في الجلسة
    $code = trim($_POST['code']); // الحصول على رمز التفعيل من المستخدم
    $newPassword = trim($_POST['password']); // كلمة المرور الجديدة
    $confirmPassword = trim($_POST['confirm_password']); // تأكيد كلمة المرور الجديدة

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.'; // التحقق من تطابق كلمة المرور
        header('location: password_reset_confirm.php');
        exit();
    }

    try {
        // تهيئة عميل AWS Cognito
        $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

        // تأكيد عملية إعادة تعيين كلمة المرور في AWS Cognito
        $result = $cognitoClient->confirmForgotPassword([
            'ClientId' => COGNITO_APP_CLIENT_ID,
            'Username' => $email,
            'ConfirmationCode' => $code,
            'Password' => $newPassword,
        ]);

        // رسالة النجاح
        $_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
        unset($_SESSION['reset_email']); // مسح البريد الإلكتروني المخزن
        header('location: login.php'); // إعادة التوجيه إلى صفحة تسجيل الدخول
        exit();
    } catch (AwsException $e) {
        $errorCode = $e->getAwsErrorCode();
        if ($errorCode === 'ExpiredCodeException') {
            $_SESSION['error'] = 'The confirmation code has expired. Please request a new reset code.'; // الرمز منتهي الصلاحية
        } elseif ($errorCode === 'CodeMismatchException') {
            $_SESSION['error'] = 'Invalid confirmation code. Please try again.'; // الرمز غير صحيح
        } else {
            $_SESSION['error'] = 'Error: ' . $e->getAwsErrorMessage(); // رسالة خطأ من AWS
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'There was an error processing your request.'; // خطأ عام
    }
}

?>

<?php include 'includes/header.php'; // تضمين ملف الرأس ?>
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
        <p class="login-box-msg">Reset Your Password</p>
        <form action="password_reset_confirm.php" method="POST">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="code" placeholder="Confirmation Code" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="New Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat" name="confirm">Reset Password</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/scripts.php'; // تضمين السكربتات ?>
</body>
</html>

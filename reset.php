<?php
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;

require 'vendor/autoload.php'; // تحميل مكتبات AWS SDK
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تضمين أدوات AWS

if (isset($_POST['reset'])) {
    $email = trim($_POST['email']); // الحصول على البريد الإلكتروني المدخل

    try {
        // تهيئة عميل AWS Cognito
        $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

        // تنفيذ عملية "نسيت كلمة المرور" في AWS Cognito
        $result = $cognitoClient->forgotPassword([
            'ClientId' => COGNITO_APP_CLIENT_ID,
            'Username' => $email,
        ]);

        // رسالة النجاح
        $_SESSION['success'] = 'A password reset code has been sent to your email. Please check your inbox.';
        $_SESSION['reset_email'] = $email; // تخزين البريد الإلكتروني لإعادة التوجيه

        // التوجيه إلى صفحة تأكيد إعادة تعيين كلمة المرور
        header('location: password_reset_confirm.php');
        exit();
    } catch (AwsException $e) {
        $errorCode = $e->getAwsErrorCode();
        if ($errorCode === 'UserNotFoundException') {
            $_SESSION['error'] = 'Email not found.'; // البريد الإلكتروني غير موجود
        } else {
            $_SESSION['error'] = 'Error: ' . $e->getAwsErrorMessage(); // رسالة خطأ من AWS
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'There was an error processing your request.'; // خطأ عام
    }
} else {
    $_SESSION['error'] = 'Input the email associated with your account.'; // طلب إدخال البريد الإلكتروني
}

header('location: password_forgot.php'); // إعادة التوجيه إلى صفحة "نسيت كلمة المرور"
exit();
?>

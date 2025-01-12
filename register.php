<?php

require 'config/app.php'; // تضمين إعدادات التطبيق
require_once 'helpers/AwsHelper.php'; // تضمين ملفات المساعدة لـ AWS
require 'vendor/autoload.php'; // تحميل مكتبات Composer
include 'includes/session.php'; // بدء الجلسة

if (isset($_POST['signup'])) {
    // استلام بيانات التسجيل من النموذج
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    // حفظ البيانات في الجلسة لإظهارها في النموذج عند حدوث خطأ
    $_SESSION['firstname'] = $firstname;
    $_SESSION['lastname'] = $lastname;
    $_SESSION['email'] = $email;

    // التحقق من تطابق كلمة المرور
    if ($password != $repassword) {
        $_SESSION['error'] = 'Passwords did not match'; // إذا لم تتطابق كلمات المرور
        header('location: signup.php'); // إعادة التوجيه إلى صفحة التسجيل
    } else {
        try {
            // تهيئة عميل AWS Cognito
            $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

            // تسجيل المستخدم في Cognito
            $result = $cognitoClient->signUp([
                'ClientId' => COGNITO_APP_CLIENT_ID,
                'Username' => $email,
                'Password' => $password,
                'UserAttributes' => [
                    [
                        'Name' => 'email',
                        'Value' => $email,
                    ],
                ],
            ]);

            // إرسال رمز التحقق عبر البريد الإلكتروني
            $cognitoClient->resendConfirmationCode([
                'ClientId' => COGNITO_APP_CLIENT_ID,
                'Username' => $email,
            ]);
            $_SESSION['password'] = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور

            // إعادة التوجيه إلى صفحة تأكيد الحساب
            header('location: confirm-account.php');
            exit();
        } catch (\Aws\Exception\AwsException $e) {
            // التعامل مع استثناءات AWS
            $_SESSION['error'] = 'Error: ' . $e->getAwsErrorMessage();
            header('location: signup.php');
        } catch (\Exception $e) {
            // التعامل مع الاستثناءات العامة
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('location: signup.php');
        }
    }
} else {
    // إذا لم يتم إرسال البيانات من النموذج
    $_SESSION['error'] = 'Fill up signup form first';
    header('location: signup.php');
}
?>

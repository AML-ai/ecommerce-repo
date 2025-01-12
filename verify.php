<?php
use Aws\Exception\AwsException;

require 'vendor/autoload.php'; // تحميل مكتبات Composer
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // تضمين إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تضمين ملفات المساعدة لـ AWS

if (isset($_POST['login'])) {
    $email = trim($_POST['email']); // تقليم المسافات من البريد الإلكتروني
    $password = $_POST['password']; // الحصول على كلمة المرور

    try {
        // تهيئة عميل AWS Cognito
        $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

        // محاولة مصادقة المستخدم باستخدام Cognito
        $authResult = $cognitoClient->initiateAuth([
            'AuthFlow' => 'USER_PASSWORD_AUTH',
            'ClientId' => COGNITO_APP_CLIENT_ID,
            'AuthParameters' => [
                'USERNAME' => $email,
                'PASSWORD' => $password,
            ],
        ]);

        // إذا تم التوثيق بنجاح، تحقق من بيانات المستخدم في قاعدة البيانات المحلية
        if (isset($authResult['AuthenticationResult'])) {
            $conn = $pdo->open();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            $user = $stmt->fetch();

            if ($user) {
                if ($user['status'] == 1) {
                    // إذا كان المستخدم مفعلًا، تحديد متغيرات الجلسة
                    if ($user['type']) {
                        $_SESSION['admin'] = $user['id'];
                    } else {
                        $_SESSION['user'] = $user['id'];
                    }
                    $_SESSION['success'] = 'Logged in successfully.'; // رسالة نجاح
                } else {
                    // إذا لم يتم تفعيل الحساب
                    $_SESSION['error'] = 'Account not activated. Please confirm your email.';
                    header('location: confirm-account.php');
                    exit();
                }
            } else {
                // إذا لم يكن المستخدم موجودًا في قاعدة البيانات المحلية
                $_SESSION['error'] = 'User does not exist in the local database.';
            }

            $pdo->close();
        } else {
            // إذا كان من المطلوب تغيير كلمة المرور قبل الدخول
            $_SESSION['reset_email'] = $email;
            $_SESSION['error'] = 'You need to change your password before logging in.';
            header('location: change-password.php');
            exit();
        }
    } catch (AwsException $e) {
        // التعامل مع استثناءات AWS
        $errorCode = $e->getAwsErrorCode();
        $errorMessage = $e->getAwsErrorMessage();

        if ($errorCode === 'NotAuthorizedException') {
            // إذا كانت كلمة المرور أو البريد الإلكتروني غير صحيح
            $_SESSION['error'] = 'Incorrect email or password.';
        } elseif ($errorCode === 'UserNotConfirmedException') {
            // إذا لم يتم تأكيد الحساب
            $_SESSION['error'] = 'Account not activated. Please confirm your email.';
            $cognitoClient->resendConfirmationCode([
                'ClientId' => COGNITO_APP_CLIENT_ID,
                'Username' => $email,
            ]);
            header('location: confirm-account.php');
            exit();
        } elseif ($errorCode === 'PasswordChangeRequiredException') {
            // إذا كان من الضروري تغيير كلمة المرور
            $_SESSION['reset_email'] = $email;
            $_SESSION['error'] = 'You need to change your password before logging in.';
            header('location: change-password.php');
            exit();
        } elseif ($errorCode === 'UserNotFoundException') {
            // إذا كان المستخدم غير موجود
            $_SESSION['error'] = 'Email not found.';
        } else {
            // للأخطاء الأخرى
            $_SESSION['error'] = 'Error: ' . $errorMessage;
        }
    } catch (Exception $e) {
        // التعامل مع الاستثناءات العامة
        $_SESSION['error'] = 'There was an error processing your request.';
    }
} else {
    // إذا لم يتم ملء البيانات بشكل صحيح
    $_SESSION['error'] = 'Please fill in your login credentials first.';
}

header('location: login.php'); // إعادة التوجيه إلى صفحة الدخول
exit();
?>

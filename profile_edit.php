<?php
use Aws\S3\S3Client;
require 'vendor/autoload.php'; // تحميل مكتبات AWS SDK
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // تحميل إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تحميل الدوال المساعدة لـ AWS

$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

if (isset($_POST['edit'])) { // إذا تم تقديم النموذج لتحديث الحساب
    $curr_password = $_POST['curr_password']; // الحصول على كلمة المرور الحالية
    $email = trim($_POST['email']); // الحصول على البريد الإلكتروني
    $password = trim($_POST['password']); // الحصول على كلمة المرور الجديدة
    $firstname = trim($_POST['firstname']); // الحصول على الاسم الأول
    $lastname = trim($_POST['lastname']); // الحصول على الاسم الأخير
    $contact = trim($_POST['contact']); // الحصول على رقم الاتصال
    $address = trim($_POST['address']); // الحصول على العنوان
    $photo = $_FILES['photo']['name']; // الحصول على اسم صورة الملف

    // التحقق من كلمة المرور الحالية
    if (password_verify($curr_password, $user['password'])) {
        $filename = $user['photo']; // الحفاظ على صورة المستخدم القديمة إذا لم يتم تحميل صورة جديدة
        if (!empty($photo)) {
            $ext = pathinfo($photo, PATHINFO_EXTENSION); // الحصول على امتداد الصورة
            $new_filename = 'profile_' . $user['id'] . '.' . $ext; // إنشاء اسم جديد للصورة
            try {
                // رفع الصورة إلى AWS S3
                $s3 = AwsHelper::uploadProfileToS3($_FILES['photo'], $new_filename);

                $filename = $new_filename; // تخزين رابط الصورة الجديد
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error uploading photo: ' . $e->getMessage(); // في حال حدوث خطأ أثناء رفع الصورة
                header('location: profile.php');
                exit();
            }
        }

        $updateCognitoPassword = false; // متغير لتحديد إذا كان يجب تحديث كلمة المرور في Cognito
        if (!empty($password) && !password_verify($password, $user['password'])) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور الجديدة
            $updateCognitoPassword = true; // تعيين المتغير على true لتحديث كلمة المرور في Cognito
        } else {
            $hashedPassword = $user['password']; // الحفاظ على كلمة المرور الحالية إذا لم يتم تغييرها
        }

        try {
            // تحديث بيانات المستخدم في قاعدة البيانات
            $stmt = $conn->prepare("
                UPDATE users 
                SET email = :email, 
                    password = :password, 
                    firstname = :firstname, 
                    lastname = :lastname, 
                    contact_info = :contact, 
                    address = :address, 
                    photo = :photo 
                WHERE id = :id
            ");
            $stmt->execute([
                'email' => $email,
                'password' => $password,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'contact' => $contact,
                'address' => $address,
                'photo' => $filename,
                'id' => $user['id'],
            ]);
            if ($updateCognitoPassword) {
                try {
                    // تحديث كلمة المرور في AWS Cognito
                    $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

                    $cognitoClient->adminSetUserPassword([
                        'UserPoolId' => COGNITO_USER_POOL_ID,
                        'Username' => $email,
                        'Password' => $password,
                        'Permanent' => true,
                    ]);
                } catch (\Aws\Exception\AwsException $e) {
                    $_SESSION['error'] = 'Cognito password update error: ' . $e->getAwsErrorMessage(); // في حال حدوث خطأ أثناء تحديث كلمة المرور في Cognito
                    header('location: profile.php');
                    exit();
                }
            }
            $_SESSION['success'] = 'Account updated successfully.'; // رسالة النجاح
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating account: ' . $e->getMessage(); // في حال حدوث خطأ أثناء تحديث الحساب
        }
    } else {
        $_SESSION['error'] = 'Incorrect current password.'; // إذا كانت كلمة المرور الحالية غير صحيحة
    }
} else {
    $_SESSION['error'] = 'Fill up the edit form first.'; // إذا لم يتم تعبئة النموذج
}

$pdo->close(); // إغلاق الاتصال بقاعدة البيانات
header('location: transactions.php');
?>

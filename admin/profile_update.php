<?php
include 'includes/session.php'; // بدء الجلسة
include '../config/app.php'; // تضمين إعدادات التطبيق
require_once '../helpers/AwsHelper.php'; // تضمين ملف المساعد للتفاعل مع AWS
require '../vendor/autoload.php'; // تضمين التبعيات الخاصة بـ Composer

// التحقق من إذا كانت هناك قيمة للإرجاع في الرابط (للإعادة بعد حفظ التعديلات)
if (isset($_GET['return'])) {
    $return = $_GET['return'];
} else {
    $return = 'home.php'; // تعيين الصفحة الافتراضية للإرجاع
}

// التحقق من الضغط على زر "حفظ" في النموذج
if (isset($_POST['save'])) {
    $curr_password = $_POST['curr_password']; // كلمة المرور الحالية المدخلة
    $email = trim($_POST['email']); // البريد الإلكتروني المدخل
    $password = trim($_POST['password']); // كلمة المرور الجديدة المدخلة
    $firstname = trim($_POST['firstname']); // الاسم الأول المدخل
    $lastname = trim($_POST['lastname']); // الاسم الأخير المدخل
    $photo = $_FILES['photo']['name']; // اسم الملف الصورة الجديد

    // التحقق من صحة كلمة المرور الحالية
    if (password_verify($curr_password, $admin['password'])) {
        // معالجة تحميل الصورة الجديدة
        $filename = $admin['photo']; // إذا لم يتم تحديد صورة جديدة، يتم استخدام الصورة الحالية
        if (!empty($photo)) {
            // تحديد امتداد الصورة
            $ext = pathinfo($photo, PATHINFO_EXTENSION);
            // إنشاء اسم جديد للملف
            $new_filename = $firstname . $lastname . '_' . time() . '.' . $ext;

            try {
                // رفع الصورة الجديدة إلى S3
                $photo_url = AwsHelper::uploadProfileToS3($_FILES['photo'], $new_filename);

                // حذف الصورة القديمة إذا كانت موجودة على S3
                if (!empty($admin['photo'])) {
                    AwsHelper::deleteProfileFromS3($admin['photo']);
                }
                $filename = $new_filename; // استخدام الاسم الجديد للصورة

            } catch (\Aws\Exception\AwsException $e) {
                // في حال حدوث خطأ أثناء رفع الصورة إلى S3
                $_SESSION['error'] = 'Error uploading photo: ' . $e->getAwsErrorMessage();
                header('location:' . $return); // إعادة التوجيه مع رسالة الخطأ
                exit();
            }
        }

        // التعامل مع تحديث كلمة المرور
        $updateCognitoPassword = false;
        if (!empty($password) && !password_verify($password, $admin['password'])) {
            // إذا كانت كلمة المرور جديدة ولا تطابق كلمة المرور الحالية
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // تشفير كلمة المرور الجديدة
            $updateCognitoPassword = true; // تعيين متغير لتحديث كلمة المرور في Cognito
        } else {
            $hashedPassword = $admin['password']; // إذا كانت كلمة المرور غير متغيرة
        }

        $conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

        try {
            // تحديث تفاصيل المستخدم في قاعدة البيانات
            $stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, photo=:photo WHERE id=:id");
            $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'photo' => $filename,
                'id' => $admin['id']
            ]);

            // تحديث كلمة المرور في AWS Cognito إذا تم تغييرها
            if ($updateCognitoPassword) {
                try {
                    $cognitoClient = AwsHelper::CognitoIdentityProviderClient(); // إنشاء عميل لـ Cognito
                    $cognitoClient->adminSetUserPassword([
                        'UserPoolId' => COGNITO_USER_POOL_ID, // استخدام مجمع المستخدمين
                        'Username' => $email, // اسم المستخدم (البريد الإلكتروني)
                        'Password' => $password, // كلمة المرور الجديدة
                        'Permanent' => true, // تعيين كلمة المرور الجديدة ككلمة مرور دائمة
                    ]);
                } catch (\Aws\Exception\AwsException $e) {
                    // في حال حدوث خطأ أثناء تحديث كلمة المرور في Cognito
                    $_SESSION['error'] = 'Cognito password update error: ' . $e->getAwsErrorMessage();
                    header('location:' . $return); // إعادة التوجيه مع رسالة الخطأ
                    exit();
                }
            }

            // رسالة نجاح التحديث
            $_SESSION['success'] = 'Account updated successfully.';
        } catch (PDOException $e) {
            // في حال حدوث خطأ أثناء تحديث البيانات في قاعدة البيانات
            $_SESSION['error'] = 'Error updating account: ' . $e->getMessage();
        }

        $pdo->close(); // إغلاق الاتصال بقاعدة البيانات
    } else {
        // إذا كانت كلمة المرور الحالية غير صحيحة
        $_SESSION['error'] = 'Incorrect current password.';
    }
} else {
    // إذا لم يتم تقديم البيانات اللازمة
    $_SESSION['error'] = 'Fill up required details first.';
}

header('location:' . $return); // إعادة التوجيه إلى الصفحة المحددة بعد المعالجة
?>

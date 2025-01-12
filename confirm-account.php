<?php
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

require 'vendor/autoload.php'; // تحميل مكتبات AWS SDK
include 'includes/session.php'; // بدء الجلسة
require 'config/app.php'; // تحميل إعدادات التطبيق
require 'helpers/AwsHelper.php'; // تحميل الدوال المساعدة لـ AWS

if (isset($_POST['confirm'])) { // إذا تم تقديم النموذج لتأكيد الحساب
    $email = $_SESSION['email']; // الحصول على البريد الإلكتروني من الجلسة
    $code = $_POST['code']; // الحصول على كود التأكيد

    try {
        // تهيئة عميل AWS Cognito
        $cognitoClient = AwsHelper::CognitoIdentityProviderClient();

        // تأكيد الحساب باستخدام كود التفعيل
        $result = $cognitoClient->confirmSignUp([
            'ClientId' => COGNITO_APP_CLIENT_ID,
            'Username' => $email,
            'ConfirmationCode' => $code,
        ]);

        $_SESSION['success'] = 'Account successfully verified. You can now log in.'; // رسالة النجاح
        $now = date('Y-m-d'); // التاريخ الحالي

        // توليد كود تفعيل جديد
        $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = substr(str_shuffle($set), 0, 12);
        $conn = $pdo->open();

        // إدخال بيانات المستخدم في قاعدة البيانات بعد التفعيل
        $stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, activate_code, created_on, status) VALUES (:email, :password, :firstname, :lastname, :code, :now, :status)");
        $stmt->execute(['email' => $email, 'password' => $_SESSION['password'], 'firstname' => $_SESSION['firstname'], 'lastname' => $_SESSION['lastname'], 'code' => $code, 'now' => $now, 'status' => 1]);
        $userid = $conn->lastInsertId();

        // مسح الجلسة الخاصة بالمستخدم بعد التفعيل
        unset($_SESSION['firstname']);
        unset($_SESSION['lastname']);
        unset($_SESSION['email']);

        // إعادة التوجيه إلى صفحة تسجيل الدخول
        header('location: login.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Invalid verification code provided, please try again\n' . $e->getMessage(); // إذا كان الكود غير صحيح
    }
}
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition register-page">
<div class="register-box">
  	<?php
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
  	<div class="register-box-body">
    	<p class="login-box-msg">Confirm Your Account</p>

    	<form action="" method="POST">
            <input type="hidden" name="email" value="<?php echo (isset($_SESSION['email']));?>" required>

          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="code" placeholder="Code" required>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <hr>
      		<div class="row">
    			<div class="col-xs-4">
          			<button type="submit" class="btn btn-primary btn-block btn-flat"  name="confirm"><i class="fa fa-pencil"></i> Sign Up</button>
        		</div>
      		</div>
    	</form>
  	</div>
</div>

<?php include 'includes/scripts.php' ?>
</body>
</html>
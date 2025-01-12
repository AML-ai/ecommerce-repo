<?php

class Database {

    private $server; // خادم قاعدة البيانات (عنوان الـ RDS)
    private $username; // اسم المستخدم لقاعدة البيانات
    private $password; // كلمة المرور لقاعدة البيانات
    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // خيار لإظهار الأخطاء بشكل استثناءات
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // خيار لجلب النتائج كصفوف مرتبطة
    );
    protected $conn; // متغير لحفظ الاتصال بقاعدة البيانات

    public function __construct() {
        // إعدادات الاتصال بخادم AWS RDS
        $this->server = "mysql:host=ecomm.c3aq2myoilpw.eu-north-1.rds.amazonaws.com;dbname=ecomm";
        $this->username = "admin";
        $this->password = 'm97$gh$H';
    }

    public function open() {
        // فتح اتصال بقاعدة البيانات
        try {
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
            return $this->conn; // إرجاع الاتصال
        } catch (PDOException $e) {
            // في حال وجود خطأ في الاتصال
            echo "There is some problem in connection:" . $e->getMessage();
        }
    }

    public function close() {
        // إغلاق الاتصال بقاعدة البيانات
        $this->conn = null;
    }
}

// إنشاء كائن من الفئة Database
$pdo = new Database();


?>

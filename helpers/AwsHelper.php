<?php

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient; // استيراد عميل Cognito
use Aws\S3\S3Client; // استيراد عميل S3
class AwsHelper
{
    // إعدادات AWS المستخدمة في جميع العمليات
    private static $AWS_OPTIONS=[
        'region' => AWS_REGION, // المنطقة التي سيتم استخدامها في العمليات
        'version' => 'latest', // استخدام آخر نسخة من الـ SDK
        'credentials' => [
            'key'    => AWS_ACCESS_KEY, // المفتاح العام
            'secret' => AWS_SECRET_KEY, // المفتاح السري
        ],
        'http' => [
            'verify' => false, // تعطيل التحقق من SSL (مناسب للتطوير)
        ],
    ];

    // دالة لإنشاء عميل لـ Cognito
    public static function CognitoIdentityProviderClient(){
        return new CognitoIdentityProviderClient(static::$AWS_OPTIONS); // إرجاع العميل مع الإعدادات
    }

    // دالة لتحميل صورة المنتج إلى S3
    public static function uploadProductToS3($file, $key) {
        $s3 = new S3Client(static::$AWS_OPTIONS); // إنشاء عميل S3

        try {
            // تحميل الملف إلى S3
            $result = $s3->putObject([
                'Bucket' => S3_BUCKET, // تحديد الدلو
                'Key' => 'products/' . $key, // تحديد مسار الملف
                'SourceFile' => $file['tmp_name'], // تحديد مصدر الملف
                'ACL' => 'public-read', // تحديد صلاحيات الوصول (قراءة عامة)
            ]);

            return $result['ObjectURL']; // إرجاع رابط الملف الذي تم رفعه
        } catch (Exception $e) {
            // في حال حدوث خطأ أثناء رفع الملف
            throw new Exception('Error uploading file: ' . $e->getMessage());
        }
    }

    // دالة لحذف صورة المنتج من S3
    public static function deleteProductFromS3($key) {
        $s3 = new S3Client(static::$AWS_OPTIONS); // إنشاء عميل S3

        try {
            // حذف الملف من S3
            $s3->deleteObject([
                'Bucket' => S3_BUCKET, // تحديد الدلو
                'Key' => 'products/'.$key, // تحديد مسار الملف
            ]);
        } catch (Exception $e) {
            // في حال حدوث خطأ أثناء الحذف
            throw new Exception('Error deleting file: ' . $e->getMessage());
        }
    }

    // دالة لتحميل صورة الملف الشخصي إلى S3
    public static function uploadProfileToS3($file, $key) {
        $s3 = new S3Client(static::$AWS_OPTIONS); // إنشاء عميل S3

        try {
            // تحميل الملف إلى S3
            $result = $s3->putObject([
                'Bucket' => S3_BUCKET, // تحديد الدلو
                'Key' => 'profiles/' . $key, // تحديد مسار الملف
                'SourceFile' => $file['tmp_name'], // تحديد مصدر الملف
                'ACL' => 'public-read', // تحديد صلاحيات الوصول (قراءة عامة)
            ]);

            return $result['ObjectURL']; // إرجاع رابط الملف الذي تم رفعه
        } catch (Exception $e) {
            // في حال حدوث خطأ أثناء رفع الملف
            throw new Exception('Error uploading file: ' . $e->getMessage());
        }
    }

    // دالة لحذف صورة الملف الشخصي من S3
    public static function deleteProfileFromS3($key)
    {
        $s3 = new S3Client(static::$AWS_OPTIONS); // إنشاء عميل S3
        try {
            // حذف الملف من S3
            $s3->deleteObject([
                'Bucket' => S3_BUCKET, // تحديد الدلو
                'Key' => 'profiles/'.$key, // تحديد مسار الملف
            ]);
        } catch (Exception $e) {
            // في حال حدوث خطأ أثناء الحذف
            throw new Exception('Error deleting file: ' . $e->getMessage());
        }
    }

}

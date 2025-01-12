<?php
include 'includes/session.php'; // بدء الجلسة
include 'includes/slugify.php'; // استيراد دالة تحويل النص إلى slug
require '../config/app.php'; // استيراد إعدادات التطبيق
require_once '../helpers/AwsHelper.php'; // استيراد دالة مساعد AWS
require '../vendor/autoload.php'; // استيراد الملفات الضرورية لـ AWS SDK
$conn = $pdo->open(); // فتح الاتصال بقاعدة البيانات

try {
    if (isset($_POST['add'])) {
        $name = $_POST['name']; // الحصول على اسم المنتج
        $slug = slugify($name); // تحويل اسم المنتج إلى slug
        $category = $_POST['category']; // الحصول على الفئة
        $price = $_POST['price']; // الحصول على السعر
        $description = $_POST['description']; // الحصول على الوصف
        $filename = $_FILES['photo']['name']; // الحصول على اسم الصورة
        // التحقق إذا كان المنتج موجودًا بالفعل
        $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM products WHERE slug=:slug");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        $new_filename = '';
        if ($row['numrows'] > 0) {
            $_SESSION['error'] = 'Product already exists'; // إذا كان المنتج موجودًا بالفعل
        } else {
            if (!empty($filename)) {
                // إنشاء اسم فريد للصورة
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $new_filename = $row['slug'] . '_' . time() . '.' . $ext;

                // رفع الصورة إلى S3
                try {
                    $photo_url = AwsHelper::uploadProductToS3($_FILES['photo'], $new_filename);
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Error uploading photo: ' . $e->getMessage(); // في حال حدوث خطأ في رفع الصورة
                    header('location: products.php');
                    exit();
                }
            }
            // إدخال المنتج في قاعدة البيانات
            $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, slug, price, photo) VALUES (:category, :name, :description, :slug, :price, :photo)");
            $stmt->execute([
                'category' => $category,
                'name' => $name,
                'description' => $description,
                'slug' => $slug,
                'price' => $price,
                'photo' => $new_filename,
            ]);
            $_SESSION['success'] = 'Product added successfully'; // رسالة نجاح
        }
    } elseif (isset($_POST['upload'])) {
        $id = $_POST['id']; // الحصول على معرف المنتج
        $filename = $_FILES['photo']['name']; // الحصول على اسم الصورة الجديدة

        // جلب تفاصيل المنتج من قاعدة البيانات
        $stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            if (!empty($filename)) {
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $new_filename = $row['slug'] . '_' . time() . '.' . $ext;
                try {
                    // حذف الصورة القديمة من S3
                    if (!empty($row['photo'])) {
                        AwsHelper::deleteProductFromS3($row['photo']);
                    }
                    // رفع الصورة الجديدة إلى S3
                    $photo_url = AwsHelper::uploadProductToS3($_FILES['photo'], $new_filename);

                    $stmt = $conn->prepare("UPDATE products SET photo=:photo WHERE id=:id");
                    $stmt->execute(['photo' => $new_filename, 'id' => $id]);

                    $_SESSION['success'] = 'Product photo updated successfully'; // رسالة نجاح
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Error uploading photo: ' . $e->getMessage(); // في حال حدوث خطأ أثناء رفع الصورة
                }
            } else {
                $_SESSION['error'] = 'No photo file selected.'; // إذا لم يتم اختيار صورة
            }
        } else {
            $_SESSION['error'] = 'Product not found.'; // إذا لم يتم العثور على المنتج
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id']; // الحصول على معرف المنتج
        $name = $_POST['name']; // الحصول على اسم المنتج المعدل
        $slug = slugify($name); // تحويل الاسم المعدل إلى slug
        $category = $_POST['category']; // الحصول على الفئة
        $price = $_POST['price']; // الحصول على السعر
        $description = $_POST['description']; // الحصول على الوصف
        // تحديث المنتج في قاعدة البيانات
        $stmt = $conn->prepare("UPDATE products SET name=:name, slug=:slug, category_id=:category, price=:price, description=:description WHERE id=:id");
        $stmt->execute(['name' => $name, 'slug' => $slug, 'category' => $category, 'price' => $price, 'description' => $description, 'id' => $id]);
        $_SESSION['success'] = 'Product updated successfully'; // رسالة نجاح
    }
    elseif (isset($_POST['delete'])) {
        $id = $_POST['id']; // الحصول على معرف المنتج
        // جلب صورة المنتج من قاعدة البيانات
        $stmt = $conn->prepare("SELECT photo FROM products WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $photo = $stmt->fetch();

        if (!empty($photo['photo'])) {
            // حذف الصورة من S3
            AwsHelper::deleteProductFromS3($photo['photo']);
        }
        // حذف المنتج من قاعدة البيانات
        $stmt = $conn->prepare("DELETE FROM products WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $_SESSION['success'] = 'Product deleted successfully'; // رسالة نجاح
    } else {
        $_SESSION['error'] = 'Select product to delete first'; // في حال عدم اختيار منتج للحذف
    }
} catch (PDOException $e) {
    $_SESSION['error'] = $e->getMessage(); // في حال حدوث خطأ في قاعدة البيانات
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage(); // في حال حدوث خطأ عام
}
$pdo->close(); // إغلاق الاتصال بقاعدة البيانات

header('location: products.php'); // إعادة التوجيه إلى صفحة المنتجات

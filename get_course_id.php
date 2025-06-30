<?php
// إعدادات الاتصال بقاعدة البيانات - مستوحاة من test211.php
$host = "localhost";
$user = "root";
$password = "";
$dbName = "courses"; // اسم قاعدة البيانات هو "courses" كما هو محدد في test211.php

// تعيين رأس الاستجابة لـ JSON
header('Content-Type: application/json');

// افتراض أن الكورس سيتم إرساله عبر طلب POST
// التحقق مما إذا كان course_name قد تم إرساله
if (isset($_POST['course_name'])) {
    $course_name_to_find = $_POST['course_name'];
} else {
    // إذا لم يتم إرسال course_name، إرجاع خطأ
    echo json_encode(['success' => false, 'message' => 'Course name not provided.']);
    exit(); // إيقاف التنفيذ
}

// إنشاء اتصال بقاعدة البيانات
$conn = mysqli_connect($host, $user, $password, $dbName);

// التحقق من نجاح الاتصال
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error()]);
    exit();
}

// استخدام Prepared Statements لتجنب حقن SQL
// نفترض أن الجدول اسمه 'course' (وليس 'courses' كما كان في الكود السابق)
// وأن الأعمدة هي 'course_id' و 'course_name'
$sql = "SELECT course_id FROM course WHERE course_name = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'فشل في تجهيز الاستعلام: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit();
}

// ربط المتغير مع الاستعلام كـ string (s)
mysqli_stmt_bind_param($stmt, "s", $course_name_to_find);

// تنفيذ الاستعلام
mysqli_stmt_execute($stmt);

// ربط النتائج بمتغير
mysqli_stmt_bind_result($stmt, $course_id);

// جلب النتائج
if (mysqli_stmt_fetch($stmt)) {
    // تم العثور على course_id، إرجاعه بنجاح
    // تمت إضافة 'course_name' هنا للتأكد من الوصول إلى الملف
    echo json_encode(['success' => true, 'course_id' => $course_id, 'course_name' => $course_name_to_find]);
} else {
    // لم يتم العثور على الكورس
    echo json_encode(['success' => false, 'message' => 'لم يتم العثور على كورس باسم ' . $course_name_to_find . '.', 'course_name_received' => $course_name_to_find]);
}

// إغلاق العبارة والاتصال
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
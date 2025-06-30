<?php 
ini_set('display_errors', 0); // لمنع عرض الأخطاء للمستخدم
ini_set('log_errors', 1);     // لتسجيل الأخطاء في سجل PHP
// ini_set('error_log', '/path/to/your/custom-php-error.log'); // اختياري: حدد مسار سجل خاص
error_reporting(E_ALL);       // للإبلاغ عن جميع أنواع الأخطاء (للتصحيح)

session_start();

// إعدادات الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$password = "";
$dbName = "courses"; // اسم قاعدة البيانات هو "courses"

// تعيين رأس الاستجابة لـ JSON لسهولة التعامل من جانب JavaScript
header('Content-Type: application/json');

// التحقق مما إذا كانت البيانات المطلوبة (student_id و course_id) موجودة في طلب POST
if (isset($_POST['student_id']) && isset($_POST['course_id'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $reservation_date = date("Y-m-d"); // تاريخ اليوم بتنسيق YYYY-MM-DD
    $status ="active"; // الحالة الافتراضية للحجز

    // إنشاء اتصال بقاعدة البيانات
    $conn = mysqli_connect($host, $user, $password, $dbName);

    // التحقق من نجاح الاتصال
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error()]);
        exit();
    }

    // استخدام Prepared Statements لتجنب حقن SQL
    $sql = "INSERT INTO reservation (student_id, course_id, reservation_date, status) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'فشل في تجهيز الاستعلام: ' . mysqli_error($conn)]);
        mysqli_close($conn);
        exit();
    }

    // ربط المتغيرات مع الاستعلام
    // 'i' لـ integer (student_id, course_id)
    // 's' لـ string (reservation_date, status)
    mysqli_stmt_bind_param($stmt, "iiss", $student_id, $course_id, $reservation_date, $status);

    // ************* الجزء المعدل يبدأ من هنا *************
    try {
        if (mysqli_stmt_execute($stmt)) {
            // تم تسجيل الحجز بنجاح
            $reservation_id = mysqli_insert_id($conn);
            $reservation_details = null; // يمكنك جلب تفاصيل إضافية هنا إذا أردت

            // إعداد رسالة النجاح مع تفاصيل الحجز
            $response = [
                'success' => true,
                'message' => 'تم تسجيل الحجز بنجاح!',
                'reservation_id' => $reservation_id,
                'reservation_details' => $reservation_details,
                'debug_info' => [
                    'student_id' => $student_id,
                    'course_id' => $course_id,
                    'reservation_date' => $reservation_date,
                    'status' => $status
                ]
            ];
            
            echo json_encode($response);
        } else {
            // هذا الجزء لن يتم الوصول إليه إذا ألقى mysqli_stmt_execute استثناءً
            // ولكن نتركه كاحتياط
            echo json_encode(['success' => false, 'message' => 'فشل تسجيل الحجز غير معروف.']);
        }
    } catch (mysqli_sql_exception $e) {
        // تم التقاط الاستثناء بنجاح
        $error_code = $e->getCode(); // الحصول على رمز الخطأ
        $error_message = $e->getMessage(); // الحصول على رسالة الخطأ

        // تحقق إذا كان الخطأ بسبب تكرار قيد فريد (رمز الخطأ 1062 في MySQL)
        if ($error_code == 1062 && strpos($error_message, 'Duplicate entry') !== false) {
            echo json_encode(['success' => false, 'message' => 'تم تسجيل هذا الكورس للطالب من قبل.']);
        } else {
            // خطأ آخر غير تكرار البيانات، تسجيله وعرض رسالة عامة
            error_log("Reservation error: " . $error_message);
            echo json_encode(['success' => false, 'message' => 'فشل تسجيل الحجز: حدث خطأ غير متوقع.']);
        }
    }
    // ************* الجزء المعدل ينتهي هنا *************

    // إغلاق الاستعلام والاتصال
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    // إذا لم يتم إرسال البيانات المطلوبة
    echo json_encode(['success' => false, 'message' => 'البيانات المطلوبة غير متوفرة (student_id أو course_id).']);
}
?>
<?php
session_start();
header('Content-Type: application/json');
$host = "localhost";
$user = "root";
$password = "";
$dbName = "courses";
$conn = mysqli_connect($host, $user, $password, $dbName);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error()]);
    exit();
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'المستخدم غير مسجل الدخول.']);
    mysqli_close($conn);
    exit();
}
$student_id = $_SESSION['user_id'];
$course = [];
// استعلام لجلب كورسات الطالب من جدول reservation وربطها بجدول الكورسات (Courses)
$sql = "SELECT c.course_name, r.reservation_date
        FROM reservation r
        JOIN course c ON r.course_id = c.course_id
        WHERE r.student_id = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $course[] = $row;
    }
    echo json_encode(['success' => true, 'courses' => $course]);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في تجهيز الاستعلام: ' . mysqli_error($conn)]);
}
mysqli_close($conn);
?>
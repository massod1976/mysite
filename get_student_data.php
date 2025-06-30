<?php
// ملف get_student_data.php
session_start();
require_once 'db_connection.php'; // ملف الاتصال بقاعدة البيانات

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'لم يتم تسجيل الدخول']);
    exit();
}

$student_id = $_SESSION['user_id'];

try {
    // تصحيح أسماء الحقول لتتوافق مع الجدول
    $stmt = $conn->prepare("SELECT student_name AS name, email, 
        mobile AS phone, 
        regster_date AS registration_date 
    FROM students 
    WHERE student_id = ?");
    
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $student_data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على بيانات الطالب']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?>
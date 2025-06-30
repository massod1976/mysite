<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// التحقق من وجود بيانات الإدخال
if (!isset($_POST['course_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'لم يتم إرسال اسم الكورس'
    ]);
    exit();
}

$course_name = trim($_POST['course_name']);

try {
    // استعلام معدل للبحث باسم الكورس مع حماية من SQL Injection
    $stmt = $conn->prepare("
        SELECT 
            course_id,
            course_name,
            course_code,
            category,
            description,
            price,
            status
        FROM course
        WHERE course_name = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        
        // تنسيق البيانات قبل الإرسال
        echo json_encode([
            'success' => true,
            'course_id' => $course['course_id'],
            'course_name' => $course['course_name'],
            'course_code' => $course['course_code'],
            'category' => $course['category'],
            'description' => $course['description'] ?? 'لا يوجد وصف متاح',
            'price' => number_format((float)$course['price'], 2) . ' ر.س',
            'status' => $course['status'] === 'active' ? 'متاح' : 'غير متاح',
            'available' => $course['status'] === 'active'
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'لم يتم العثور على الكورس: ' . $course_name
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
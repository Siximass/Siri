<?php
header('Content-Type: application/json');
session_start();
require_once 'dbconnect.php';

$response = ['success' => false, 'error' => '', 'senderName' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $is_complaint = isset($_POST['is_complaint']) && $_POST['is_complaint'] === '1' ? 1 : 0;

    // ดึงชื่อผู้ใช้งาน
    $user_query = "SELECT fullname FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $senderName = $user_data['fullname'];

    $table = $is_complaint ? 'complaints' : 'messages';
    $sql = "INSERT INTO $table (user_id, " . ($is_complaint ? 'complaint_text' : 'message_text') . ", created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $message);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['senderName'] = $senderName;
    } else {
        $response['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อความ: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['error'] = 'กรุณาเข้าสู่ระบบก่อนส่งข้อความ';
}

echo json_encode($response);
exit;
?>

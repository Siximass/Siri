<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

error_log("Received request: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $is_complaint = isset($_POST['is_complaint']) && $_POST['is_complaint'] === '1' ? 1 : 0;

    $table = $is_complaint ? 'complaints' : 'messages';
    $sql = "INSERT INTO $table (user_id, " . ($is_complaint ? 'complaint_text' : 'message_text') . ", created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'เกิดข้อผิดพลาดในการบันทึกข้อความ']);
    }

    $stmt->close();
    error_log("Sending response: " . json_encode(['success' => true]));
} else {
    echo json_encode(['success' => false, 'error' => 'กรุณาเข้าสู่ระบบก่อนส่งข้อความ']);
}

$conn->close();
?>
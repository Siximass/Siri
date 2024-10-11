<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']));
}

if (!isset($_POST['post_id']) || !isset($_POST['comment'])) {
    die(json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']));
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];
$comment = trim($_POST['comment']);

if (empty($comment)) {
    die(json_encode(['success' => false, 'message' => 'กรุณากรอกความคิดเห็น']));
}

// เชื่อมต่อกับฐานข้อมูล
$db = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');

// บันทึกคอมเมนต์
$stmt = $db->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
if ($stmt->execute([$post_id, $user_id, $comment])) {
    echo json_encode(['success' => true, 'comment' => htmlspecialchars($comment)]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกคอมเมนต์']);
}
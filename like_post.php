<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']));
}

if (!isset($_POST['post_id'])) {
    die(json_encode(['success' => false, 'message' => 'ไม่พบรหัสโพสต์']));
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];

// เชื่อมต่อกับฐานข้อมูล
$db = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');

// ตรวจสอบว่าผู้ใช้เคยกดไลค์โพสต์นี้แล้วหรือยัง
$stmt = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);

if ($stmt->fetch()) {
    // ถ้าเคยกดไลค์แล้ว ให้ยกเลิกการไลค์
    $db->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?")->execute([$post_id, $user_id]);
} else {
    // ถ้ายังไม่เคยกดไลค์ ให้เพิ่มการไลค์
    $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$post_id, $user_id]);
}

// นับจำนวนไลค์ทั้งหมดของโพสต์
$stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$likes = $stmt->fetchColumn();

echo json_encode(['success' => true, 'likes' => $likes]);
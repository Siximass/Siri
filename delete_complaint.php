<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

// ตรวจสอบว่าผู้ใช้เป็น admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'ไม่มีสิทธิ์ในการลบข้อร้องเรียน']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $complaintId = mysqli_real_escape_string($conn, $_POST['id']);
    
    $sql = "DELETE FROM complaints WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $complaintId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'ไม่สามารถลบข้อร้องเรียนได้: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'คำขอไม่ถูกต้อง']);
}

$conn->close();
?>

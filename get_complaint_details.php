<?php
session_start();
require_once 'dbconnect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'ไม่มีสิทธิ์เข้าถึงข้อมูล']);
    exit();
}

if (isset($_POST['id'])) {
    $complaint_id = mysqli_real_escape_string($conn, $_POST['id']);
    error_log("Received complaint ID: " . $complaint_id); // เพิ่มบรรทัดนี้เพื่อตรวจสอบ
    
    $query = "SELECT c.*, u.fullname, u.profile_image 
              FROM complaints c 
              JOIN users u ON c.user_id = u.id 
              WHERE c.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $complaint = $result->fetch_assoc()) {
        $output = "<h4>รายละเอียดข้อร้องเรียน</h4>";
        $output .= "<p><strong>ผู้ร้องเรียน:</strong> " . htmlspecialchars($complaint['fullname']) . "</p>";
        $output .= "<p><strong>วันที่:</strong> " . date('d/m/Y H:i:s', strtotime($complaint['created_at'])) . "</p>";
        $output .= "<p><strong>ข้อร้องเรียน:</strong> " . nl2br(htmlspecialchars($complaint['complaint_text'])) . "</p>";
        echo json_encode(['success' => true, 'html' => $output]);
    } else {
        echo json_encode(['success' => false, 'error' => 'ไม่พบข้อมูลข้อร้องเรียน']);
    }
    $stmt->close();
} else {
    error_log("No complaint ID received"); // เพิ่มบรรทัดนี้เพื่อตรวจสอบ
    echo json_encode(['success' => false, 'error' => 'ไม่พบ ID ข้อร้องเรียน']);
}

$conn->close();
?>

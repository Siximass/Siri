<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit;
}

$query = "SELECT c.id, c.complaint_text, c.created_at, u.fullname, u.profile_image 
          FROM complaints c 
          JOIN users u ON c.user_id = u.id 
          ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'การค้นหาข้อมูลล้มเหลว: ' . mysqli_error($conn)]);
    exit;
}

$complaints = [];
while ($row = mysqli_fetch_assoc($result)) {
    $complaints[] = $row;
}

header('Content-Type: application/json');
echo json_encode($complaints);

mysqli_close($conn);
?>

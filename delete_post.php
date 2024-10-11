<?php
session_start();
require_once 'dbconnect.php';

// ตรวจสอบว่าผู้ใช้เป็น admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);

    // ลบโพสต์
    $delete_post_query = "DELETE FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_post_query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['delete_success'] = "ลบโพสต์เรียบร้อยแล้ว";
    } else {
        $_SESSION['delete_error'] = "เกิดข้อผิดพลาดในการลบโพสต์: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);

    // กลับไปยังหน้า admin.php
    header("Location: admin.php");
    exit();
} else {
    header("Location: admin.php");
    exit();
}
?>

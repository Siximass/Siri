<?php
session_start();
require_once 'dbconnect.php';

// ตรวจสอบว่าผู้ใช้เป็น admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // ลบข้อร้องเรียนของผู้ใช้ก่อน
    $delete_complaints_query = "DELETE FROM complaints WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $delete_complaints_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    // ลบโพสต์ของผู้ใช้
    $delete_posts_query = "DELETE FROM posts WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $delete_posts_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    // ลบผู้ใช้
    $delete_user_query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_user_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['delete_success'] = "ลบผู้ใช้เรียบร้อยแล้ว";
    } else {
        $_SESSION['delete_error'] = "เกิดข้อผิดพลาดในการลบผู้ใช้: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

header("Location: admin.php");
exit();
?>

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
    
    // อัปเดตค่า profile_image เป็นค่าว่าง
    $query = "UPDATE users SET profile_image = '' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // ลบไฟล์รูปภาพ (ถ้าต้องการ)
        $query = "SELECT profile_image FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if (!empty($row['profile_image'])) {
            $imagePath = 'uploads/' . basename($row['profile_image']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        header("Location: admin.php?message=profile_image_removed");
    } else {
        header("Location: admin.php?error=failed_to_remove_profile_image");
    }
} else {
    header("Location: admin.php?error=invalid_user_id");
}

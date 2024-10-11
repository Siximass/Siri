<?php
session_start();
$imageUrl = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default.jpg'; // ใช้รูปภาพเริ่มต้นถ้าไม่มี
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <style>
        /* CSS สำหรับป๊อปอัพ */
        .profile-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8); /* สีพื้นหลังเป็นสีดำโปร่งใส */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(20px); /* เพิ่มเอฟเฟกต์เบลอ */
            cursor: pointer; /* แสดงเคอร์เซอร์ชี้เมื่อชี้ไปที่ป๊อปอัพ */
        }
        .profile-popup img {
            max-width: 98%; /* เพิ่มขนาดสูงสุดของความกว้าง */
            max-height: 98%; /* เพิ่มขนาดสูงสุดของความสูง */
            border-radius: 10px;
            object-fit: contain; /* รักษาสัดส่วนของรูปภาพ */
        }
    </style>
</head>
<body>
    <div class="profile-popup" onclick="window.location.href='home.php';"> <!-- คลิกที่ป๊อปอัพเพื่อไปที่หน้า Home -->
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Profile Image">
    </div>
</body>
</html>

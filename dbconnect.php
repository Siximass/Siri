<?php
$servername = "localhost"; // หรือที่อยู่เซิร์ฟเวอร์ของคุณ
$username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$password = ""; // รหัสผ่าน (ถ้าไม่มีให้เว้นว่าง)
$dbname = "web"; // ตรวจสอบว่าชื่อนี้ตรงกับฐานข้อมูลจริงของคุณ

// สร้างการเชื่อมต่อ
$conn = mysqli_connect($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("การเชื่อมต่อล้มเหลว: " . mysqli_connect_error());
}

// เพิ่มบรรทัดนี้เพื่อตั้งค่า charset
mysqli_set_charset($conn, "utf8mb4");
?>
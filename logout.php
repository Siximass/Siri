<?php
session_start();
require_once 'dbconnect.php';

$user_fullname = 'ผู้ใช้';
$profile_image = 'https://via.placeholder.com/150';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT fullname, profile_image FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $user_fullname = $row['fullname'];
        $profile_image = $row['profile_image'] ? $row['profile_image'] : 'https://via.placeholder.com/150';
    }
    
    mysqli_stmt_close($stmt);
}

// ทำการ logout
session_destroy();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logout-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .icon {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .user-profile {
            margin-bottom: 20px;
        }
        .user-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4CAF50;
        }
        .user-name {
            font-size: 1.2em;
            font-weight: 500;
            color: #333;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="user-profile">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="รูปโปรไฟล์">
            <div class="user-name"><?php echo htmlspecialchars($user_fullname); ?></div>
        </div>
        <i class="fas fa-check-circle icon"></i>
        <h1>ออกจากระบบสำเร็จ</h1>
        <p>ขอบคุณที่ใช้บริการ คุณได้ออกจากระบบเรียบร้อยแล้ว</p>
        <a href="login.php" class="btn">กลับสู่หน้าเข้าสู่ระบบ</a>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000); // รอ 5 วินาทีก่อน redirect
    </script>
</body>
</html>
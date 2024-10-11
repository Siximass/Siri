<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $update_query = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssi", $fullname, $email, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user_fullname'] = $fullname;
        $success_message = "ข้อมูลของคุณถูกอัปเดตเรียบร้อยแล้ว";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
    }

    // จัดการอัปโหลดรูปภาพโปรไฟล์ใหม่
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $update_image_query = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_image_query);
            mysqli_stmt_bind_param($stmt, "si", $target_file, $user_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['profile_image'] = $target_file;
        }
    }
}

// ดึงข้อมูลปัจจุบันของผู้ใช้
$query = "SELECT fullname, email, profile_image FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4, #45B7D1, #FDCB6E);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        .edit-profile-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(10px);
        }
        h2 {
            color: #2D3436;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2D3436;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #45B7D1;
            outline: none;
        }
        input[type="file"] {
            padding: 0.5rem;
            background-color: #f1f2f6;
        }
        .profile-image-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #45B7D1;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .submit-btn {
            background-color: #45B7D1;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.1s ease;
            font-weight: 500;
        }
        .submit-btn:hover {
            background-color: #3CA3BB;
        }
        .submit-btn:active {
            transform: scale(0.98);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #2D3436;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #45B7D1;
        }
        .success, .error {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .success {
            background-color: #55efc4;
            color: #00b894;
        }
        .error {
            background-color: #fab1a0;
            color: #d63031;
        }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .file-input-label {
            display: inline-block;
            padding: 0.75rem 1rem;
            background-color: #f1f2f6;
            color: #2D3436;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .file-input-label:hover {
            background-color: #e2e5e9;
        }
        .file-input:focus + .file-input-label {
            outline: 2px solid #45B7D1;
        }
    </style>
</head>
<body>
    <div class="edit-profile-container">
        <h2>แก้ไขข้อมูลส่วนตัว</h2>
        <?php
        if (isset($success_message)) {
            echo "<p class='success'>$success_message</p>";
        }
        if (isset($error_message)) {
            echo "<p class='error'>$error_message</p>";
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-image-container">
                <img src="<?php echo !empty($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : 'https://via.placeholder.com/150'; ?>" 
                     alt="Current Profile Image" 
                     class="profile-image">
            </div>
            <div class="form-group">
                <label for="fullname"><i class="fas fa-user"></i> ชื่อ-นามสกุล:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> อีเมล:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="profile_image"><i class="fas fa-image"></i> รูปโปรไฟล์:</label>
                <div class="file-input-wrapper">
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input">
                    <label for="profile_image" class="file-input-label">เลือกไฟล์</label>
                </div>
            </div>
            <button type="submit" class="submit-btn">บันทึกการเปลี่ยนแปลง</button>
        </form>
        <a href="mini.php" class="back-link">กลับไปหน้าหลัก</a>
    </div>
</body>
</html>
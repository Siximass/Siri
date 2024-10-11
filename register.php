<?php
session_start();
require_once 'dbconnect.php';

// สร้างโฟลเดอร์ uploads/ ถ้ายังไม่มี
$upload_dir = 'uploads/';
if (!file_exists($upload_dir) && !is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['fullname']) && isset($_POST['email'])) {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $profile_image = '';

        if ($password !== $confirm_password) {
            $error = "รหัสผ่านไม่ตรงกัน กรุณาลองใหม่";
        } else {
            // ตรวจสอบว่าชื่อหรืออีเมลซ้ำหรือไม่
            $stmt = mysqli_prepare($conn, "SELECT fullname, email FROM users WHERE fullname = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $fullname, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                if ($row['fullname'] == $fullname) {
                    $error = "ชื่อนี้ถูกใช้งานแล้ว กรุณาใช้ชื่ออื่น";
                } elseif ($row['email'] == $email) {
                    $error = "อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น";
                }
            } else {
                // ตรวจสอบและอัพโหลดรูปโปรไฟล์
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $target_file = $upload_dir . basename($_FILES["profile_image"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    
                    // ตรวจสอบว่าเป็นไฟล์รูปภาพจริงหรือไม่
                    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
                    if ($check !== false) {
                        // ตรวจสอบขนาดไฟล์ (ตัวอย่าง: จำกัดที่ 5MB)
                        if ($_FILES["profile_image"]["size"] > 5000000) {
                            $error = "ขออภัย, ไฟล์ของคุณมีขนาดใหญ่เกินไป.";
                        } else {
                            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                                $profile_image = $target_file;
                            } else {
                                $error = "ขออภัย, เกิดข้อผิดพลาดในการอัพโหลดไฟล์ของคุณ.";
                            }
                        }
                    } else {
                        $error = "ไฟล์ที่อัพโหลดไม่ใช่รูปภาพ.";
                    }
                }

                if (!isset($error)) {
                    // เข้ารหัสรหัสผ่าน
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // บันทึกข้อมูลลงฐานข้อมูล
                    $stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, email, password, profile_image) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $hashed_password, $profile_image);

                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['register_success'] = true;
                        $_SESSION['registered_email'] = $email;
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "เกิดข้อผิดพลาดในการลงทะเบียน: " . mysqli_stmt_error($stmt);
                    }
                }
            }
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            background-image: url('https://images.pexels.com/photos/3845077/pexels-photo-3845077.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: 'Kanit', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            /* ขยายความกว้างของฟอร์ม */
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #283593;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .login-link {
            margin-top: 15px;
        }

        .login-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .profile-image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .file-input {
            margin-top: 10px;
        }
    </style>


    <style type="text/css">
        body {
            background-color: #55F8FF;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>ลงทะเบียนสมาชิก</h2>
        <?php if (isset($error)) {
            echo "<p class='error'>$error</p>";
        } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="input-group">
                <img id="profile-image-preview" src="https://via.placeholder.com/150" alt="รูปโปรไฟล์" class="profile-image-preview">
                <label for="profile_image" class="custom-file-upload">
                    <i class="fas fa-upload"></i> เลือกรูปโปรไฟล์
                </label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input" onchange="previewImage(event)" style="display:none;">
            </div>
            <div class="input-group">
                <label for="fullname">ชื่อ-นามสกุล</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="input-group">
                <label for="email">อีเมล</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">ลงทะเบียน</button>
        </form>
        <div class="login-link">
            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </div>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('profile-image-preview');
                output.src = reader.result;
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>

</html>
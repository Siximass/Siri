<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, fullname, password, role, profile_image FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_image'] = $row['profile_image'];

            if ($row['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: mini.php");
            }
            exit();
        } else {
            $error = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "ไม่พบบัญชีผู้ใช้";
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-image: url('https://images.pexels.com/photos/1139541/pexels-photo-1139541.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            transition: all 0.3s ease-in-out;
            backdrop-filter: blur(10px);
        }
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        h2 {
            color: #fff;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .input-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            text-align: left;
        }
        input[type="email"], input[type="password"] {
            width: 90%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f4f7f6;
            transition: all 0.2s ease-in-out;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #4CAF50;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        .btn {
            background-color: #3b50ce; /* เปลี่ยนสีพื้นหลังของปุ่มที่นี่ */
            color: white; /* เปลี่ยนสีข้อความของปุ่มที่นี่ */
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #e64a19; /* เปลี่ยนสีพื้นหลังของปุ่มเมื่อ hover ที่นี่ */
        }
        .register-link, .home-link {
            margin-top: 20px;
        }
        .register-link a, .home-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .register-link a:hover, .home-link a:hover {
            color: #388E3C;
        }
        .error {
            color: #ff0000;
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 15px;
        }
        .input-icon {
            position: relative;
        }
        .input-icon i {
            position: absolute;
            left: 10px;
            top: 14px;
            color: #666;
        }
        .input-icon input {
            padding-left: 35px;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 30px;
            }
            h2 {
                font-size: 2em;
            }
            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
        .home-link {
            margin-top: 20px;
        }
        .home-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .home-link a:hover {
            color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>เข้าสู่ระบบ</h2>
        <?php 
        if(isset($_SESSION['register_success'])) {
            echo "<p class='success'>ลงทะเบียนสำเร็จ กรุณาเข้าสู่ระบบด้วยอีเมล: " . htmlspecialchars($_SESSION['registered_email']) . "</p>";
            unset($_SESSION['register_success']);
            unset($_SESSION['registered_email']);
        }
        if(isset($error)) { echo "<p class='error'>$error</p>"; } 
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="input-group">
                <label for="email">อีเมล</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            <div class="input-group">
                <label for="password">รหัสผ่าน</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
            </button>
        </form>
        <div class="register-link">
            <p>ยังไม่มีบัญชี? <a href="register.php">ลงทะเบียน</a></p>
        </div>
        <div class="home-link">
            <p><a href="mini.php"><i class="fas fa-home"></i> กลับสู่หน้าหลัก</a></p>
        </div>
    </div>
</body>
</html>
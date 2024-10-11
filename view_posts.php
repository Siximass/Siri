<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_GET['user_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_GET['user_id']); // ดึง user_id จาก URL

// ดึงข้อมูลผู้ใช้
$query_user = "SELECT fullname FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);

if (!$result_user || mysqli_num_rows($result_user) == 0) {
    die("ไม่พบผู้ใช้งาน");
}

$user = mysqli_fetch_assoc($result_user);
$fullname = htmlspecialchars($user['fullname']);

// ดึงโพสต์ของผู้ใช้ที่เฉพาะเจาะจง โดยใช้ user_id
$query_posts = "SELECT p.*, u.fullname 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.user_id = $user_id 
                ORDER BY p.created_at DESC";
$result_posts = mysqli_query($conn, $query_posts);

if (!$result_posts) {
    die("การดึงข้อมูลโพสต์ผิดพลาด: " . mysqli_error($conn));
}
$query_posts = "SELECT p.*, u.fullname 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.user_id = ? 
                ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($conn, $query_posts);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_posts = mysqli_stmt_get_result($stmt);
?>

<?php
if (isset($_SESSION['delete_success'])) {
    echo "<p class='success-message'>" . $_SESSION['delete_success'] . "</p>";
    unset($_SESSION['delete_success']);
} elseif (isset($_SESSION['delete_error'])) {
    echo "<p class='error-message'>" . $_SESSION['delete_error'] . "</p>";
    unset($_SESSION['delete_error']);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Posts ทั้งหมด</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap');
        
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .post-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            transform: translateY(-10px);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover; /* ทำให้ภาพพอดีกับกรอบ */
            object-position: center; /* จัดตำแหน่งภาพให้อยู่ตรงกลาง */
        }

        .post-content {
            padding: 15px;
            flex: 1; /* ให้เนื้อหาด้านล่างขยายเพื่อเติมเต็มการ์ด */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .post-user {
            font-weight: bold;
            color: #3498db;
            font-size: 1.2em;
        }

        .post-text {
            margin-top: 10px;
            font-size: 0.9em;
            color: #333;
            line-height: 1.4;
        }

        .post-date {
            font-size: 0.8em;
            color: #999;
            margin-top: 10px;
            align-self: flex-end; /* ทำให้วันที่อยู่ล่างสุดของการ์ด */
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #c0392b;
        }

        .delete-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .success-message, .error-message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .success-message {
            background-color: #2ecc71;
            color: white;
        }

        .error-message {
            background-color: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Posts ทั้งหมด</h1>
    <div class="post-grid">
        <?php if (mysqli_num_rows($result_posts) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($result_posts)) : ?>
                <div class="post-card">
                    <?php if (isset($row['image_path']) && !empty($row['image_path'])) : ?>
                        <a href="<?php echo htmlspecialchars($row['image_path']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Post Image" class="post-image">
                        </a>
                    <?php else : ?>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <!-- แสดงชื่อผู้ใช้จากฐานข้อมูล -->
                        <div class="post-user"><?php echo htmlspecialchars($row['fullname']); ?></div>

                        <?php if (!empty($row['content'])) : ?>
                            <div class="post-text">
                                <?php echo htmlspecialchars(substr($row['content'], 0, 100)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-date"><?php echo htmlspecialchars($row['created_at']); ?></div>
                    </div>
                    
                    <a href="delete_post.php?id=<?php echo $row['id']; ?>&user_id=<?php echo $user_id; ?>" class="delete-btn" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบโพสต์นี้?');">ลบโพสต์</a>
                </div>
            <?php endwhile; ?>
        <?php } else { ?>
            <p>ไม่พบโพสต์สำหรับผู้ใช้นี้</p>
        <?php } ?>
    </div>

    <a href="admin.php" class="back-btn">กลับไปหน้า Admin</a>
</body>
</html>

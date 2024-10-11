<?php
// สร้างโฟลเดอร์ uploads/ ถ้ายังไม่มี
$upload_dir = 'uploads/';
if (!file_exists($upload_dir) && !is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

session_start();
require_once 'dbconnect.php';

date_default_timezone_set('Asia/Bangkok'); // หรือโซนเวลาที่คุณต้องการ

// เพิ่มโค้ดนี้ที่ส่วนบนของไฟล์ PHP ของคุณ
if (isset($_GET['delete_post']) && isset($_SESSION['user_id'])) {
    $post_id = mysqli_real_escape_string($conn, $_GET['delete_post']);
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM posts WHERE id = '$post_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        // ลบโพสต์สำเร็จ
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // มีข้อผิดพลาดในการลบโพสต์
        echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}

// ฟังก์ชันสำหรับดึงโพสต์ทั้งหมดพร้อมคอมเมนต์
function getPosts($conn)
{
    $sql = "SELECT posts.*, users.fullname, users.profile_image, 
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS likes_count 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    foreach ($posts as &$post) {
        $post_id = $post['id'];
        $comment_sql = "SELECT comments.*, users.fullname, users.profile_image FROM comments 
                        JOIN users ON comments.user_id = users.id 
                        WHERE post_id = $post_id ORDER BY created_at ASC";
        $comment_result = mysqli_query($conn, $comment_sql);
        $comments = mysqli_fetch_all($comment_result, MYSQLI_ASSOC);

        // แปลงวันที่สำหรับแต่ละคอมเมนต์
        foreach ($comments as &$comment) {
            $comment['formatted_date'] = formatDate($comment['created_at']);
        }

        $post['comments'] = $comments;

        // แปลงวันที่เป็นรูปแบบที่อ่านง่าย
        $post['formatted_date'] = formatDate($post['created_at']);
    }

    return $posts;
}

// เพิ่มฟังก์ชันใหม่สำหรับจัดรูปแบบวันที่
function formatDate($date)
{
    $now = new DateTime();
    $postDate = new DateTime($date);
    $interval = $now->diff($postDate);

    if ($interval->y > 0) {
        return $interval->y . " ปีที่แล้ว";
    } elseif ($interval->m > 0) {
        return $interval->m . " เดือนที่แล้ว";
    } elseif ($interval->d > 0) {
        return $interval->d . " วันที่แล้ว";
    } elseif ($interval->h > 0) {
        return $interval->h . " ชั่วโมงที่แล้ว";
    } elseif ($interval->i > 0) {
        return $interval->i . " นาทีที่แล้ว";
    } else {
        return "เมื่อสักครู่";
    }
}

// เพิ่มฟังก์ชันนี้หลังจากฟังก์ชัน getPosts
function getLikesCount($conn, $postId)
{
    $sql = "SELECT COUNT(*) as likes FROM likes WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['likes'];
}

// เพิ่มฟังก์ชันนี้หลังจากฟังก์ชัน getLikesCount
function createNotification($conn, $user_id, $post_id, $type)
{
    $sql = "INSERT INTO notifications (user_id, post_id, type, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $post_id, $type);
    $stmt->execute();
}

// สร้างโพสต์ใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    if (!isset($_SESSION['user_id'])) {
        // ถ้ายังไม่เข้าสู่ระบบ ให้เด้งไปยังหน้าเข้าสู่ระบบ
        header("Location: login.php");
        exit();
    }

    $post_content = mysqli_real_escape_string($conn, $_POST['post_content']);
    $user_id = $_SESSION['user_id'];
    $image_path = '';

    // อัพโหลดรูปภาพ
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["post_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // ตรวจสอบว่าเป็นไฟล์รูปภาพจริงหรือไม่
        $check = getimagesize($_FILES["post_image"]["tmp_name"]);
        if ($check !== false) {
            // ตรวจสอบขนาดไฟล์ (ตัวอย่าง: จำกัดที่ 5MB)
            if ($_FILES["post_image"]["size"] > 5000000) {
                echo "ขออภัย, ไฟล์ของคุณมีขนาดใหญ่เกินไป.";
            } else {
                if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                } else {
                    echo "ขออภัย, เกิดข้อผิดพลาดในการอัพโหลดไฟล์ของคุณ.";
                }
            }
        } else {
            echo "ไฟล์ที่อัพโหลดไม่ใช่รูปภาพ.";
        }
    }

    $sql = "INSERT INTO posts (user_id, content, image_path) VALUES ('$user_id', '$post_content', '$image_path')";
    mysqli_query($conn, $sql);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// แก้ไขส่วนของการกดไลค์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'กรุณาเข้าสู่ระบบ']);
        exit;
    }

    $post_id = mysqli_real_escape_string($conn, $_POST['post_id']);
    $user_id = $_SESSION['user_id'];

    $check_sql = "SELECT * FROM likes WHERE post_id = '$post_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) == 0) {
        $sql = "INSERT INTO likes (post_id, user_id) VALUES ('$post_id', '$user_id')";
        mysqli_query($conn, $sql);
        $liked = true;
    } else {
        $sql = "DELETE FROM likes WHERE post_id = '$post_id' AND user_id = '$user_id'";
        mysqli_query($conn, $sql);
        $liked = false;
    }

    $likes_count = getLikesCount($conn, $post_id);
    echo json_encode(['likes' => $likes_count, 'liked' => $liked]);
    exit;
}

// เพิ่มคอมเมนต์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['comment_content']) && isset($_POST['post_id'])) {
    $comment_content = mysqli_real_escape_string($conn, $_POST['comment_content']);
    $post_id = mysqli_real_escape_string($conn, $_POST['post_id']);
    $user_id = $_SESSION['user_id'];
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES ('$post_id', '$user_id', '$comment_content')";
    if (mysqli_query($conn, $sql)) {
        // ความคิดเห็นถูกเพิ่มสำเร็จ
        header("Location: " . $_SERVER['PHP_SELF'] . "#post_" . $post_id);
        exit();
    } else {
        // มีข้อผิดพลาดในการเพิ่มความคิดเห็น
        echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}

$posts = getPosts($conn);

// ในส่วนของการแสดงโพสต์ ไม่จำเป็นต้องเพิ่ม likes_count อีก เพราะเราได้รวมไว้ในฟังก์ชัน getPosts แล้ว
$posts = getPosts($conn);

// เพิ่มการดึงข้อมูลรูปโปรไฟล์ของผู้ใช้
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT fullname, profile_image FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
    $_SESSION['user_fullname'] = $user_data['fullname'];
    $_SESSION['user_profile_image'] = $user_data['profile_image'];
}

// ฟังก์ชันสำหรับบันทึกคอมเมนต์
function saveComment($postId, $userId, $content)
{
    global $conn;
    $currentTime = date('Y-m-d H:i:s'); // ใช้เวลาปัจจุบันของเซิร์ฟเวอร์

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $postId, $userId, $content, $currentTime);
    $stmt->execute();
    $stmt->close();
}

// ฟังก์ชันสำหรับดึงข้อมูลคอมเมนต์
function getComments($conn, $postId)
{
    $sql = "SELECT comments.*, users.fullname, users.profile_image 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            ORDER BY comments.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// เพิ่มฟังก์ชันนี้ที่ด้านบนของไฟล์
function deletePost($conn, $post_id, $user_id)
{
    $sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    return $stmt->execute();
}

// เพิ่มโค้ดนี้ในส่วนที่จัดการ POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'กรุณาเข้าสู่ระบบ']);
        exit;
    }

    $post_id = mysqli_real_escape_string($conn, $_POST['post_id']);
    $user_id = $_SESSION['user_id'];

    if (deletePost($conn, $post_id, $user_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'ไม่สามารถลบโพสต์ได้']);
    }
    exit;
}

// เพิ่มฟังก์ชันนี้ที่ส่วนบนของไฟล์ PHP
function searchPosts($conn, $searchTerm)
{
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
    $sql = "SELECT posts.*, users.fullname, users.profile_image FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.content LIKE ? OR users.fullname LIKE ?
            ORDER BY posts.created_at DESC";

    $stmt = $conn->prepare($sql);
    $searchParam = "%$searchTerm%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($posts as &$post) {
        $post['comments'] = getComments($conn, $post['id']);
        $post['likes_count'] = getLikesCount($conn, $post['id']);
        $post['formatted_date'] = formatDate($post['created_at']);
    }

    return $posts;
}

// แก้ไขส่วนการดึงโพสต์ในส่วนหลักของ PHP
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $posts = searchPosts($conn, $searchTerm);
} else {
    $posts = getPosts($conn);
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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

    <style>
        body,
        html {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            padding: 0;
            height: 100%;
            background-image: url('');
            /* รูปพื้นหลัง */
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
        }


        header {
            background-color: #000;
            /* เปลี่ยนสีแถบเมนูเป็นสีดำ */
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            width: 60px;
            height: 60px;
            margin-right: 10px;
        }

        .logo a {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .logout-btn {
            color: white;
            /* สีไอคอน */
            padding: 5px;
            text-decoration: none;
        }

        .logout-btn i {
            font-size: 20px;
            /* ขนาดไอคอน */
            margin-right: 0px;
            /* เพิ่มระยะห่างระหว่างไอคอนกับข้อความ */

        }


        .container {
            display: flex;
            flex-grow: 1;
            justify-content: center;
            /* จัดกลาง */
            align-items: flex-start;
            /* จัดให้เริ่มต้นที่ด้านบน */
            background-color: rgba(255, 255, 255, 0.8);
        }

        .main-content {
            width: 100%;
            max-width: 900px;
            /* กำหนดความกว้างสูงสุด */
            padding: 20px;
        }

        .post-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .post {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .post-content {
            margin-bottom: 15px;
        }

        .post-date,
        .comment-date {
            font-size: 0.8em;
            color: #666;
            margin-left: 10px;
        }

        footer {
            background-color: #000;
            /* เปลี่ยนสีแถบเครดิตเป็นสีดำ */
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .search-bar {
            position: relative;
            flex: 1;
            margin: 0 20px;
            display: flex;
            align-items: center;
        }

        .search-bar form {
            width: 100%;
            display: flex;
            align-items: center;
        }

        .search-bar input {
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            color: black;
            padding-right: 40px;
        }

        .search-bar button {
            background: none;
            border: none;
            position: absolute;
            left: 350px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .search-bar i {
            color: gray;
        }

        .popup {
            position: fixed;
            bottom: 60px;
            right: 20px;
            background-color: #fff;
            color: black;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: none;
            max-height: 300px;
            overflow-y: auto;
            width: 200px;
        }

        .menu {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            position: absolute;
            left: 0;
            margin-top: 10px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: none;
            max-height: 300px;
            overflow-y: auto;
            width: 200px;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            color: black;
        }

        .dropdown-item:hover {
            background-color: #f0f0f0;
        }

        /* เพิ่ม margin-left ให้กับไอคอนป๊อปอัพ */
        .relative i {
            margin-left: 10px;
            /* ปรับระยะห่างระหว่างไอคอน */
        }

        .relative {
            display: inline-block;
        }

        /* สำหรับแยกกันระหว่างไอคอน */
        .chat-popup {
            position: fixed;
            /* ให้กล่องแชทอยู่ที่ตำแหน่งคงที่ */
            bottom: 0;
            /* ยึดไว้ที่ด้านล่างของหน้าจอ */
            right: 20px;
            /* ห่างจากขอบขวา */
            width: 300px;
            /* กำหนดความกว้าง */
            height: 400px;
            /* กำหนดความสูง */
            border: 1px solid #e2e8f0;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            /* เพื่อให้อยู่เหนือองค์ประกอบอื่น */
            border-radius: 10px;
            /* ให้ขอบโค้งมน */
        }

        .chat-header {
            background-color: #4a5568;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f7fafc;
            max-height: 300px;
            /* กำหนดความสูงสูงสุดของส่วนข้อความ */
        }

        .chat-message {
            background-color: #edf2f7;
            border-radius: 18px;
            padding: 10px 15px;
            margin-bottom: 10px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.sent {
            background-color: #4299e1;
            color: white;
            align-self: flex-end;
            margin-left: auto;
        }

        #chat-form {
            display: flex;
            flex-direction: column;
            padding: 15px;
            background-color: #fff;
            border-top: 1px solid #e2e8f0;
        }

        #chat-input {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 10px 15px;
            margin-bottom: 10px;
            resize: none;
        }

        #chat-form button {
            background-color: #4a5568;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 5px;
        }

        #chat-form button:hover {
            background-color: #2d3748;
        }

        .complaint-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .complaint-checkbox input[type="checkbox"] {
            margin-right: 10px;
        }

        .complaint-checkbox label {
            color: #e53e3e;
            font-weight: bold;
        }


        .post-form textarea {
            width: 100%;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 10px;
        }

        .post-form input[type="file"] {
            margin-bottom: 10px;
        }

        .post-button {
            background-color: #000;
            /* เปลี่ยนสีปุ่มโพสต์เป็นสีดำ */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .post-button:hover {
            background-color: #333;
            /* เปลี่ยนสีเมื่อชี้เมาส์ */
        }

        .post-image {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        .post-actions {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }


        .like-button,
        .comment-button {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        .like-count {
            margin-left: 5px;
        }

        .comment-section {
            margin-top: 10px;
        }

        .comment-section textarea {
            width: 100%;
            height: 80px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .submit-comment {
            background-color: #000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-comment:hover {
            background-color: #333;
        }

        .author-image {
            width: 40px;
            /* ขนาดของรูปโปรไฟล์ */
            height: 40px;
            /* ขนาดของรูปโปรไฟล์ */
            border-radius: 50%;
            /* ทำให้รูปเป็นวงกลม */
            object-fit: cover;
            /* ปรับขนาดรูปให้พอดีกับกรอบ */
        }

        .author-name {
            font-size: 1rem;
            /* ขนาดตัวอักษร */
            font-weight: bold;
            /* ทำให้ตัวหนา */
            margin-left: 10px;
            /* ระยะห่างระหว่างรูปและชื่อ */
        }

        .comment-form {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .comment-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            resize: none;
            font-size: 14px;
            margin-right: 10px;
            transition: border-color 0.3s;
        }

        .comment-input:focus {
            border-color: #007bff;
            /* เปลี่ยนสีเมื่อโฟกัส */
            outline: none;
        }

        .comment-button {
            background-color: #007bff;
            /* สีพื้นหลัง */
            color: white;
            /* สีตัวอักษร */
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .comment-button:hover {
            background-color: #0056b3;
            /* สีเมื่อเลื่อนเมาส์ */
        }

        .comment {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .commenter-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .comment-content {
            flex: 1;
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 10px;
        }

        .comment-author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .comment-text {
            margin: 0;
        }

        .post-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .image-upload-label {
            cursor: pointer;
            color: #007bff;
        }

        .image-upload-label:hover {
            color: #0056b3;
        }

        .image-upload-label i {
            font-size: 24px;
        }

        .delete-post {
            color: #ff0000;
            cursor: pointer;
            float: right;
            margin-left: 350px;
            /* ปรับระยะห่างจากขอบขวาตามต้องการ */
        }

        .delete-post:hover {
            color: #cc0000;
        }

        .delete-comment {
            color: #ff0000;
            cursor: pointer;
            float: right;
            margin-left: 10px;
        }

        .delete-comment:hover {
            color: #cc0000;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <a href="#">Dee Talk</a>
        </div>

        <div class="search-bar">
            <form action="" method="GET">
                <input type="text" name="search" placeholder="ค้นหา..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="material-icons">search</i></button>
            </form>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- ส่วนแสดงผลสำหรับผู้ใช้ที่เข้าสู่ระบบแล้ว -->
            <div class="relative" style="margin-right: 10px;">
                <a href="edit_profile.php" class="action-btn">
                    <img src="<?php echo !empty($_SESSION['profile_image']) ? htmlspecialchars($_SESSION['profile_image']) : 'https://via.placeholder.com/50'; ?>"
                        alt="รูปโปรไฟล์" class="author-image" id="user-profile-image" style="cursor: pointer;">
                </a>
            </div>

            <!--admin-->
            <i class="material-icons" id="chat-icon" style="cursor:pointer;">chat_bubble</i> <!-- ไอคอนแชท -->
            <div class="chat-popup" id="chat-popup" style="display:none;">
                <div class="chat-header">
                    <h3>ติดต่อ Admin</h3>
                    <button id="close-chat">X</button>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <!-- ข้อความแชทจะถูกเพิ่มที่นี่ -->
                </div>
                <form id="chat-form">
                    <textarea id="chat-input" style="color: black; background-color: white;" placeholder="พิมพ์ข้อความ..."
                        required></textarea>
                    <div style="display: flex; align-items: center; margin-top: 10px;">
                        <input type="checkbox" id="is-complaint" required>
                        <label for="is-complaint"
                            style="margin-left: 5px; color: red; font-weight: bold;">ยืนยันการส่งข้อร้องเรียน</label>
                    </div>
                    <button type="submit"
                        style="color: white; background-color: #007bff; margin-top: 10px;">ส่งข้อร้องเรียน</button>
                </form>
            </div>
            <!--/admin-->

            <a href="logout.php" class="logout-btn">
                <i class="material-icons" style="margin-left: 10px;">exit_to_app</i>
            </a>

            <div class="relative" style="margin-left: 0px;">
                <div class="menu" style="margin-left: 0px;">
                    <i class="material-icons" id="menu-button" style="cursor:pointer;">menu</i>
                    <div id="menu" class="dropdown-menu">
                        <ul>
                            <li class="dropdown-item"><a href="Home.php"><i class="material-icons">home</i></a></li>
                            <li class="dropdown-item"><a href="About.php"><i class="material-icons">info</i></a></li>
                        </ul>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ส่วนแสดงผลสำหรับผู้ใช้ที่ยังไม่ได้เข้าสู่ระบบ -->
            <div style="margin-left: auto; display: flex; align-items: center;">
                <a href="login.php" class="action-btn" style="margin-right: 10px;">
                    <i class="material-icons">account_circle</i>
                </a>
                <div class="relative" style="margin-left: 0px;">
                    <div class="menu" style="margin-left: 0px;">
                        <i class="material-icons" id="menu-button" style="cursor:pointer;">menu</i>
                        <div id="menu" class="dropdown-menu">
                            <ul>
                                <li class="dropdown-item"><a href="Home.php"><i class="material-icons">home</i></a></li>
                                <li class="dropdown-item"><a href="About.php"><i class="material-icons">info</i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
    </header>

    <div class="container">
        <div class="main-content">

            <!-- ฟอร์มสำหรับสร้างโพสต์ใหม่ -->
            <div class="post-form" style="margin-bottom: 15px;">
                <form method="POST" enctype="multipart/form-data" onsubmit="return checkLoginForPost();">
                    <textarea name="post_content" placeholder="คุณกำลังคิดอะไรอยู่?" required></textarea>
                    <div class="post-form-actions">
                        <label for="post_image" class="image-upload-label">
                            <i class="material-icons">image</i>
                        </label>
                        <input type="file" name="post_image" id="post_image" accept="image/*" style="display: none;">
                        <button type="submit" class="post-button">โพสต์</button>
                    </div>
                </form>
            </div>

            <!-- แสดงโพสต์ทั้งหมด -->
            <?php foreach ($posts as $post): ?>
                <div class="post" id="post_<?php echo htmlspecialchars($post['id']); ?>">
                    <div class="post-header">
                        <img src="<?php echo !empty($post['profile_image']) ? htmlspecialchars($post['profile_image']) : 'https://via.placeholder.com/50'; ?>"
                            alt="Profile Image" class="author-image">
                        <span class="author-name"><?php echo htmlspecialchars($post['fullname']); ?></span>
                        <span class="post-date" data-timestamp="<?php echo strtotime($post['created_at']); ?>">
                            <?php echo formatDate($post['created_at']); ?>
                        </span>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                            <span class="delete-post" onclick="deletePost(<?php echo $post['id']; ?>)">
                                <i class="material-icons" style="margin-left: 300px;">delete</i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <?php if (!empty($post['image_path'])): ?>
                            <center><img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="post-image"></center>
                        <?php endif; ?>
                    </div>
                    <div class="post-actions">
                        <button class="like-button" data-post-id="<?php echo $post['id']; ?>">
                            <i class="material-icons">thumb_up</i>
                            <span
                                class="like-count"><?php echo isset($post['likes_count']) ? $post['likes_count'] : 0; ?></span>
                        </button>
                        <button class="comment-button" onclick="toggleComments(<?php echo $post['id']; ?>)">
                            <i class="material-icons">comment</i>
                            คอมเมนต์
                        </button>
                    </div>
                    <div class="comment-section" id="comments_<?php echo $post['id']; ?>" style="display: none;">
                        <?php if (isset($post['comments'])): ?>
                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="comment">
                                    <img src="<?php echo !empty($comment['profile_image']) ? htmlspecialchars($comment['profile_image']) : 'https://via.placeholder.com/40'; ?>"
                                        alt="Commenter Image" class="commenter-image">
                                    <div class="comment-content">

                                        <div class="comment-author"><?php echo htmlspecialchars($comment['fullname']); ?>
                                            <span class="comment-date"
                                                data-timestamp="<?php echo strtotime($comment['created_at']); ?>">
                                                <?php echo formatDate($comment['created_at']); ?>
                                            </span>
                                        </div>
                                        <p class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <form method="POST" class="comment-form" onsubmit="return checkLogin();">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <textarea name="comment_content" class="comment-input" placeholder="แสดงความคิดเห็น..."
                                required></textarea>
                            <button type="submit" class="comment-button">ส่ง</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <script>
        function toggleComments(postId) {
            var commentSection = document.getElementById('comments_' + postId);
            if (commentSection.style.display === 'none') {
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }
        }
    </script>

    <script>
        document.getElementById('menu-button').addEventListener('click', function () {
            const menu = document.getElementById('menu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        });

        // ปิดเมนูเมื่อคลิกนอกเมนู
        document.addEventListener('click', function (event) {
            const menu = document.getElementById('menu');
            const menuButton = document.getElementById('menu-button');
            if (!menu.contains(event.target) && event.target !== menuButton) {
                menu.style.display = 'none';
            }
        });

        // ป้องกันการปิดเมนูเมื่อคลิกภายในเมนู
        document.getElementById('menu').addEventListener('click', function (event) {
            event.stopPropagation();
        });
    </script>

    <script>
        document.getElementById('user-profile-image').addEventListener('click', function () {
            window.location.href = 'edit_profile.php';
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chatIcon = document.getElementById('chat-icon');
            const chatPopup = document.getElementById('chat-popup');
            const closeChat = document.getElementById('close-chat');
            const chatForm = document.getElementById('chat-form');
            const chatInput = document.getElementById('chat-input');
            const chatMessages = document.getElementById('chat-messages');

            chatIcon.addEventListener('click', function () {
                chatPopup.style.display = 'block';
            });

            closeChat.addEventListener('click', function () {
                chatPopup.style.display = 'none';
            });

            chatForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const message = chatInput.value;
                const isComplaint = document.getElementById('is-complaint').checked;
                if (message.trim() !== '') {
                    sendMessage(message, isComplaint);
                    chatInput.value = '';
                    document.getElementById('is-complaint').checked = false;
                }
            });

            function sendMessage(message, isComplaint) {
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message=' + encodeURIComponent(message) + '&is_complaint=' + (isComplaint ? '1' : '0')
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            addMessageToChat(message, isComplaint, data.senderName);
                        } else {
                            if (data.error === 'กรุณาเข้าสู่ระบบก่อนส่งข้อความ') {
                                alert('กรุณาเข้าสู่ระบบก่อนส่งข้อความ');
                                window.location.href = 'login.php'; // ส่งผู้ใช้ไปยังหน้าล็อกอิน
                            } else {
                                console.error('Error:', data.error);
                                alert('เกิดข้อผิดพลาดในการส่งข้อความ: ' + data.error);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการส่งข้อความ');
                    });
            }

            //admin-->
            function addMessageToChat(message, isComplaint, senderName) {
                const messageElement = document.createElement('div');
                messageElement.className = 'chat-message';

                const nameElement = document.createElement('strong');
                nameElement.textContent = senderName + ': ';
                nameElement.style.color = 'black';
                messageElement.appendChild(nameElement);

                const contentElement = document.createElement('span');
                contentElement.textContent = '[ข้อร้องเรียน] ' + message;
                contentElement.style.color = 'black';
                messageElement.appendChild(contentElement);

                chatMessages.appendChild(messageElement);

                const divider = document.createElement('hr');
                divider.className = 'message-divider';
                chatMessages.appendChild(divider);

                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
    <!--/admin-->
    <script>
        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', function () {
                const postId = this.getAttribute('data-post-id');
                const likeCount = this.querySelector('.like-count');

                fetch('mini.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=like&post_id=' + postId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.redirect) {
                            // ถ้าต้องเข้าสู่ระบบ ให้เปลี่ยนเส้นทางไปยังหน้า login.php
                            window.location.href = data.redirect;
                        } else if (data.likes !== undefined) {
                            // อัปเดตจำนวนไลค์
                            likeCount.textContent = data.likes;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>

    <script>
        function checkLogin() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('กรุณาเข้าสู่ระบบก่อนแสดงความคิดเห็น');
                window.location.href = 'login.php';
                return false;
            <?php endif; ?>
            return true;
        }
    </script>

    <script>
        function checkLoginForPost() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('กรุณาเข้าสู่ระบบก่อนโพสต์');
                window.location.href = 'login.php';
                return false;
            <?php endif; ?>
            return true;
        }
    </script>

    <script>
        function deletePost(postId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบโพสต์นี้?')) {
                fetch('mini.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_post&post_id=' + postId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('post_' + postId).remove();
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบโพสต์');
                        }
                    });
            }
        }
    </script>
    <script>
        function updateTime(element, pastTime) {
            // คำนวณเวลาปัจจุบันและเวลาที่ผ่านมา
            let now = Date.now();
            let diff = now - pastTime; // คำนวณความแตกต่างระหว่างเวลาปัจจุบันและเวลาในอดีต
            let timeString;

            if (diff < 60000) { // น้อยกว่า 1 นาที
                timeString = 'เมื่อสักครู่';
            } else if (diff < 3600000) { // น้อยกว่า 1 ชั่วโมง
                timeString = Math.floor(diff / 60000) + ' นาทีที่แล้ว';
            } else if (diff < 86400000) { // น้อยกว่า 1 วัน
                timeString = Math.floor(diff / 3600000) + ' ชั่วโมงที่แล้ว';
            } else if (diff < 2592000000) { // น้อยกว่า 30 วัน
                timeString = Math.floor(diff / 86400000) + ' วันที่แล้ว';
            } else if (diff < 31536000000) { // น้อยกว่า 1 ปี
                timeString = Math.floor(diff / 2592000000) + ' เดือนที่แล้ว';
            } else {
                timeString = Math.floor(diff / 31536000000) + ' ปีที่แล้ว';
            }

            // แสดงข้อความที่คำนวณได้ใน element
            element.textContent = timeString;
        }

        let element = document.getElementById('timeDisplay');
        let pastTime = new Date('2023-01-01').getTime(); // ตัวอย่างเวลาในอดีต
        updateTime(element, pastTime);

    </script>


    </header>
    <div class="container">
        <div class="main-content">
</body>

</html>
<div class="main-content">
    </body>

    </html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'dbconnect.php';
// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // ถ้ายังไม่ล็อกอินให้ไปที่หน้าเข้าสู่ระบบ
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลโปรไฟล์ผู้ใช้
$sql = "SELECT fullname, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="mini.php"> <!-- เพิ่มการเชื่อมโยงกับ mini.php -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <style>
       body {
    background-color: black;
    color: white;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

header, footer {
    background-color: #000;
    padding: 5px;
    text-align: center;
    color: white;
}

main {
    padding: 40px;
    border: 1px solid #444;
    margin: 20px;
    background-color: #222;
    color: white;
    min-height: 80vh;
}

#post-area {
    background-color: white;
    color: black;
    padding: 20px;
    border-radius: 5px;
}

.profile, .profile-info {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin-bottom: 20px;
}

.profile-popup {
    background: rgba(255, 255, 255, 0.85);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    width: 80%;
    max-width: 600px;
    text-align: left;
    backdrop-filter: blur(10px);
}

.profile-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 20px;
}

.profile-info img {
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    width: 120px;
    height: 120px;
    object-fit: cover;
    margin-right: 20px;
}

.cover-photo img {
    border-radius: 10px;
    width: 100%;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 999;
}

.post-container {
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.post-actions {
    margin-top: 10px;
}

.post-button {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.post-button.delete {
    background: #dc3545;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.post-button.delete:hover {
    background: white;
}

.post-button.edit {
    background: #ffc107;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.like-comment {
    margin-top: 10px;
}

.comment-section {
    margin-top: 20px;
}

.basic-info {
    margin-top: 20px;
}

.logo {
    display: flex;
    align-items: center;
}

.logo img {
    width: 65px;
    height: 65px;
    margin-right: 10px;
}

.logo a {
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
    text-decoration: none;
}

.main-content {
    max-width: 800px;
    margin: 0 auto;
    z-index: 1;
}

footer {
    position: relative;
    width: 100%;
    display: none;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-items {
    display: flex;
    flex-grow: 1;
}

.nav-item {
    margin-left: 15px;
    margin-right: 15px;
}

.post {
    border: 1px solid #ccc;
    padding: 10px;
    margin: 10px 0;
    background-color: #f9f9f9;
    position: relative;
    transition: background-color 0.3s;
    border-radius: 5px;
}

.username {
    font-family: 'Roboto', sans-serif;
    font-size: 1.5em;
    font-weight: bold;
    margin-left: 15px;
    color: white;
}

.home-icon {
    position: absolute;
    top: 20px;
    right: 10px;
    cursor: pointer;
    color: white;
    font-size: 24px;
    margin-right: 15px;
}

.black-text {
    color: black;
}

.edit-profile-btn {
    margin-top: 10px;
    padding: 5px 10px;
    background-color: #000;
    color: #fff;
    border: none;
    cursor: pointer;
}
.post-header .material-icons.more-vert,
.post-header .more-vert-text {
    color: black; /* เปลี่ยนสีของทั้งไอคอนและข้อความเป็นสีดำ */
}
.icon-black {
    color: black;
}

.profile-popup button {
    background-color: white;
    color: black;
    border: 1px solid black;
    padding: 5px 10px;
    cursor: pointer;
}

.profile-popup,
.profile-popup h3,
.profile-popup button,
.profile-popup #post-options-content {
    color: black !important;
}
.more-vert-icon {
    cursor: pointer; /* แสดงรูปเมาส์เป็นรูปชี้ */
}
.profile-container {
    position: relative;
}

.profile-img {
    cursor: pointer;
    width: 50px; /* ปรับขนาดตามที่ต้องการ */
    height: 50px; /* ปรับขนาดตามที่ต้องการ */
    border-radius: 50%; /* ทำให้รูปเป็นวงกลม */
}

.popup {
    display: none; /* ซ่อน popup */
    position: fixed; /* ให้ popup อยู่กลางจอ */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8); /* พื้นหลังครึ่งโปร่งใส */
    z-index: 1000; /* ให้อยู่ด้านบนสุด */
    justify-content: center; /* จัดกลางแนวนอน */
    align-items: center; /* จัดกลางแนวตั้ง */
}

.popup-img {
    max-width: 90%; /* กำหนดความกว้างสูงสุดของรูป */
    max-height: 90%; /* กำหนดความสูงสูงสุดของรูป */
    border-radius: 10px; /* ปรับให้มุมมนถ้าต้องการ */
}

.close {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 30px;
    color: white; /* สีของปุ่มปิด */
    cursor: pointer;
}

    </style>
</head>
<body>
<header>
        <div class="logo">
            <img src="logo.png" alt="Logo"> <!-- เปลี่ยนเป็นที่อยู่โลโก้จริง -->
            <a href="#">Dee Talk</a> <!-- เปลี่ยนเป็นชื่อเว็บจริง -->
        </div>
            <a href="mini.php" class="home-icon">
            <span class="material-icons" style="color: white;">home</span>
            </a>
    </header>

    <main>
                <h2>Profile</h2> <!-- คงเดิม -->
            <div class="profile-info" style="background-color: white; color: black; padding: 20px; border-radius: 5px;">
                <div class="profile-header" style="display: flex; align-items: center;"> <!-- ทำให้รูปโปรไฟล์และชื่อผู้ใช้อยู่ในบรรทัดเดียวกัน -->
                    <?php if (!empty($user['profile_image'])): ?>
                        <a href="popupproflie.php?image=<?php echo urlencode($user['profile_image']); ?>">
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" style="width: 150px; height: 150px; border-radius: 50%; margin-right: 15px; object-fit: cover;">
                        </a>
                    <?php endif; ?>
                    <h3 class="username black-text" style="font-size: 2em;"><?php echo htmlspecialchars($user['fullname']); ?></h3> <!-- ปรับขนาดตัวอักษร -->
                </div>
                <div class="profile-details" style="margin-top: 20px;"> <!-- เว้นระยะห่างระหว่างข้อมูลเพิ่มเติม -->
                    <button class="post-button" onclick="editProfile()" style="background-color: black; margin-bottom: 10px;">Edit Profile</button> <!-- ปุ่มแก้ไขโปรไฟล์ -->
                    <div style="background-color: white; padding: 10px; border-radius: 5px; margin-top: 5px;"> <!-- ลดระยะห่างระหว่างปุ่มและ textarea -->
                        <textarea id="description" rows="4" cols="50" disabled onfocus="clearPlaceholder()" onblur="setPlaceholder()"></textarea> <!-- ช่องแก้ไขคำอธิบาย -->
                    </div>
                </div>

        <div style="background-color: white; padding: 10px; border-radius: 5px; margin-top: 20px;"> <!-- เพิ่มพื้นหลังสีขาว -->
            <p id="friends-count" style="margin: 0; color: black;">Friends: 0</p> <!-- เปลี่ยนตัวหนังสือเป็นสีดำ -->
            <button class="post-button" onclick="viewFriends()" style="background-color: black; color: white; border: none; margin-top: 10px;">View Friends</button> <!-- คืนค่าปุ่มเป็นสีดำ -->
        </div>
        <br> <!-- เพิ่มวรรคลงมาอีกนิด -->
        <button class="post-button" onclick="createPost()" style="background-color: black; color:  #fff; border: 1px solid black;">Create New Post</button> <!-- เปลี่ยนปุ่มเป็นสีขาว ตัวหนังสือสีดำ -->
        <h2>Your Posts</h2> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
        <!-- index.html -->
                                <div class="post">
                            <div class="post-content">
                                <span style="color: black;">นี่คือเนื้อหาของโพสต์แรก</span>
                            </div>
                            <div class="more-vert-icon" onclick="showPostOptions('1')" style="cursor: pointer;"> <!-- เพิ่ม style cursor -->
                                <i class="material-icons icon-black">more_vert</i>
                            </div>

<!-- ป๊อปอัพสำหรับแสดงรูปโปรไฟล์ -->
<div id="profile-popup" class="profile-popup" style="display: none;" onclick="closeProfilePopup()">
    <img id="popup-image" src="" alt="Profile Image" style="max-width: 90%; max-height: 90%; border-radius: 10px; margin: auto; display: block;">
</div>


        <!-- ป๊อปอัพสำหรับแสดงตัวเลือกโพสต์ -->
        <div class="profile-popup" id="post-options-popup" style="display: none;">
            <div class="profile-header">
                <h3>ตัวเลือกโพสต์</h3>
                <br><br> 
                <br><br> <button onclick="closePostOptions()">ปิด</button>
            </div>
            <div id="post-options-content"></div>
        </div>
        <!-- ป๊อปอัพสำหรับแสดงรายละเอียดโพสต์ -->
                <div class="profile-popup" id="post-options-popup" style="display: none; color: black;">
                <div class="profile-header">
                    <h3 style="color: black;">ตัวเลือกโพสต์</h3>
                    <button onclick="closePostOptions()" style="color: black; margin-left: 10px;">ปิด</button> <!-- เพิ่ม margin-left -->
                </div>
                <div id="post-options-content" style="color: black;"></div>
            </div>
    </main>

    <footer style="position: fixed; bottom: 0; width: 100%;">
        <p>© 2024 Dee Talk</p> <!-- เปลี่ยนชื่อเว็บไซต์เป็น Dee Talk -->
    </footer>

    <script src="script.js"></script>
    <script>
      document.getElementById("profile-pic").onclick = function() {
    const popup = document.getElementById("popup");
    const popupImg = document.getElementById("popup-img");
    popupImg.src = this.src; // ใช้รูปจาก profile
    popup.style.display = "flex"; // แสดง popup แบบ flex
}

document.getElementById("close-popup").onclick = function() {
    const popup = document.getElementById("popup");
    popup.style.display = "none"; // ปิด popup
}

window.onclick = function(event) {
    const popup = document.getElementById("popup");
    if (event.target === popup) {
        popup.style.display = "none"; // ปิด popup ถ้าคลิกนอก
    }
}

        function createPost() {
            // โค้ดสำหรับการสร้างโพสต์ใหม่
            alert('สร้างโพสต์ใหม่');
        }

        function viewFriends() {
            // โค้ดสำหรับการดูเพื่อน
            alert('ดูเพื่อน');
        }

        function editPost(postId) {
            // รับเนื้อหาของโพสต์
            let postContent = document.getElementById(`post-content-${postId}`);
            let currentContent = postContent.innerText;

            // ให้ผู้ใช้แก้ไขเนื้อหา
            let newContent = prompt("แก้ไขเนื้อหาโพสต์:", currentContent);
            if (newContent !== null) {
                postContent.innerText = newContent; // แสดงเนื้อหาใหม่ที่ผู้ใช้แก้ไข
            }
        }

        function deletePost(postId) {
            // ยืนยันการลบโพสต์
            let confirmation = confirm("คุณแน่ใจว่าต้องการลบโพสต์นี้?");
            if (confirmation) {
                let postElement = document.getElementById(`post-${postId}`);
                postElement.remove(); // ลบโพสต์ออกจาก DOM
            }
        }

        function reportPost(postId) {
            // รายงานโพสต์
            let confirmation = confirm("คุณแน่ใจว่าต้องการรายงานโพสต์นี้?");
            if (confirmation) {
                alert("โพสต์นี้ถูกรายงานเรียบร้อยแล้ว");
                // เพิ่มโค้ดการจัดการเพื่อส่งข้อมูลการรายงานไปยังเซิร์ฟเวอร์ (ถ้ามี)
            }
        }

                function createPost() {
            let newPostContent = prompt("กรุณาใส่เนื้อหาโพสต์ใหม่:");
            if (newPostContent) {
                let postContainer = document.getElementById('post-container'); // ดึง post-container
                let postId = postContainer.children.length + 1; // สร้าง ID ใหม่สำหรับโพสต์
                let newPost = document.createElement('div');
                newPost.className = 'post';
                newPost.id = `post-${postId}`;
                newPost.innerHTML = `
                    <p id="post-content-${postId}">${newPostContent}</p>
                    <button onclick="editPost('${postId}')">แก้ไขโพสต์</button>
                    <button onclick="deletePost('${postId}')">ลบโพสต์</button>
                    <button onclick="reportPost('${postId}')">รายงานโพสต์</button>
                `;
                postContainer.appendChild(newPost); // เพิ่มโพสต์ใหม่ลงใน container
            }
        }

        document.getElementById('edit-button').onclick = function() {
        const descriptionField = document.getElementById('description');
        if (descriptionField.disabled) {
            descriptionField.disabled = false; // เปิดการแก้ไข
            this.textContent = 'บันทึก'; // เปลี่ยนข้อความปุ่มเป็น "บันทึก"
        } else {
            // ทำการบันทึกคำอธิบายที่แก้ไข (อาจจะส่งไปยังเซิร์ฟเวอร์)
            const description = descriptionField.value;
            alert('คำอธิบายถูกบันทึก: ' + description);
            descriptionField.disabled = true; // ปิดการแก้ไข
            this.textContent = 'แก้ไข'; // เปลี่ยนข้อความปุ่มกลับเป็น "แก้ไข"
        }
     };
        
        function editProfile() {
            const newUsername = prompt("กรุณาใส่ชื่อผู้ใช้ใหม่:", document.getElementById('username').innerText);
            const newEmail = prompt("กรุณาใส่อีเมลใหม่:", document.getElementById('email').innerText);
            const newAbout = prompt("กรุณาใส่ข้อมูลเกี่ยวกับผู้ใช้ใหม่:", document.getElementById('about').innerText.replace("เกี่ยวกับ: ", ""));

            if (newUsername) document.getElementById('username').innerText = newUsername;
            if (newEmail) document.getElementById('email').innerText = "อีเมล: " + newEmail;
            if (newAbout) document.getElementById('about').innerText = "เกี่ยวกับ: " + newAbout;
        }

        function editAbout() {
            const newAbout = prompt("กรุณาใส่ข้อมูลเกี่ยวกับผู้ใช้ใหม่:", document.getElementById('about').innerText.replace("เกี่ยวกับ: ", ""));
            if (newAbout) document.getElementById('about').innerText = "เกี่ยวกับ: " + newAbout;
        }

        function addDescription() {
            const newDescription = prompt("กรุณาใส่คำอธิบายเกี่ยวกับตัวเอง:", document.getElementById('description').innerText.replace("คำอธิบาย: ", ""));
            if (newDescription) document.getElementById('description').innerText = "คำอธิบาย: " + newDescription;
        }

        window.onscroll = function() {
            const footer = document.querySelector('footer');
            if (window.scrollY + window.innerHeight >= document.body.offsetHeight) {
                footer.style.display = 'block'; // แสดงแถบท้ายเมื่อเลื่อนถึงด้านล่าง
            } else {
                footer.style.display = 'none'; // ซ่อนแถบท้ายเมื่อไม่อยู่ที่ด้านล่าง
            }
        };
        
            function closePostDetails() {
            document.getElementById('post-details-popup').style.display = 'none'; // ซ่อนป๊อปอัพ
            }
                    function showPostOptions(postId) {
                const optionsContent = `
                    <button onclick="editPost('${postId}')">Edit Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                    <button onclick="deletePost('${postId}')">Delete Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                    <button onclick="reportPost('${postId}')">Report Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                `;
                document.getElementById('post-options-content').innerHTML = optionsContent;
                document.getElementById('post-options-popup').style.display = 'block';
            }
            function closePostDetails() {
            document.getElementById('post-details-popup').style.display = 'none'; // ซ่อนป๊อปอัพ
        }
                    function showPostOptions(postId) {
                const optionsContent = `
                    <button onclick="editPost('${postId}')">Edit Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                    <button onclick="deletePost('${postId}')">Delete Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                    <button onclick="reportPost('${postId}')">Report Post</button> <!-- เปลี่ยนเป็นภาษาอังกฤษ -->
                `;
                document.getElementById('post-options-content').innerHTML = optionsContent;
                document.getElementById('post-options-popup').style.display = 'block';
            }

            function closePostOptions() {
                document.getElementById('post-options-popup').style.display = 'none';
            }

            function editPost(postId) {
            let postContent = document.getElementById(`post-content-${postId}`);
            let currentContent = postContent.innerText;
            let newContent = prompt("แก้ไขเนื้อหาโพสต์:", currentContent);
            if (newContent !== null) {
                postContent.innerText = newContent;
            }
            closePostOptions();
        }

        function deletePost(postId) {
            let confirmation = confirm("คุณแน่ใจว่าต้องการลบโพสต์นี้?");
            if (confirmation) {
                let postElement = document.getElementById(`post-${postId}`);
                postElement.remove();
            }
            closePostOptions();
        }

        function reportPost(postId) {
            let confirmation = confirm("คุณแน่ใจว่าต้องการรายงานโพสต์นี้?");
            if (confirmation) {
                alert("โพสต์นี้ถูกรายงานเรียบร้อยแล้ว");
            }
            closePostOptions();
        }

        function editProfile() {
            const descriptionField = document.getElementById('description');
            const button = document.querySelector('.post-button');

            if (descriptionField.disabled) {
                descriptionField.disabled = false; // เปิดการแก้ไข
                button.textContent = 'Save'; // เปลี่ยนข้อความปุ่มเป็น "บันทึก"
            } else {
                // ทำการบันทึกคำอธิบายที่แก้ไข (อาจจะส่งไปยังเซิร์ฟเวอร์)
                const description = descriptionField.value;
                alert('คำอธิบายถูกบันทึก: ' + description);
                descriptionField.disabled = true; // ปิดการแก้ไข
                button.textContent = 'Edit Profile'; // เปลี่ยนข้อความปุ่มกลับเป็น "Edit Profile"
            }
        }

        // ... existing code ...
        // เพิ่มโค้ดนี้ในส่วน <script>
        window.onload = function() {
            const descriptionField = document.getElementById('description');
            // ตรวจสอบว่ามีค่าที่เก็บไว้ใน Local Storage หรือไม่
            const savedDescription = localStorage.getItem('description');
            if (savedDescription) {
                descriptionField.value = savedDescription; // แสดงค่าที่เก็บไว้ใน textarea
            }
        };

        document.getElementById('edit-button').onclick = function() {
            const descriptionField = document.getElementById('description');
            if (descriptionField.disabled) {
                descriptionField.disabled = false; // เปิดการแก้ไข
                this.textContent = 'Save'; // เปลี่ยนข้อความปุ่มเป็น "บันทึก"
            } else {
                // ทำการบันทึกคำอธิบายที่แก้ไข
                const description = descriptionField.value;
                localStorage.setItem('description', description); // เก็บค่าลง Local Storage
                alert('คำอธิบายถูกบันทึก: ' + description);
                descriptionField.disabled = true; // ปิดการแก้ไข
                this.textContent = 'Edit'; // เปลี่ยนข้อความปุ่มกลับเป็น "แก้ไข"
            }
        };
        // ... existing code ...

        const descriptionField = document.getElementById('description');
        const placeholderText = "กรุณาใส่คำอธิบายเกี่ยวกับตัวเอง..."; // ข้อความ placeholder

        // ตั้งค่า placeholder เมื่อ textarea ว่าง
        function setPlaceholder() {
            if (descriptionField.value.trim() === "") {
                descriptionField.value = placeholderText; // แสดงข้อความ placeholder
            }
        }

        // ลบ placeholder เมื่อ textarea ถูกคลิก
        function clearPlaceholder() {
            if (descriptionField.value === placeholderText) {
                descriptionField.value = ""; // ลบข้อความ placeholder
            }
        }

        // ตั้งค่า placeholder เริ่มต้น
        setPlaceholder();
    </script>
</body>
</html>
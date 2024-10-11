<?php
session_start();
require_once 'dbconnect.php';

// ตรวจสอบว่าผู้ใช้เป็น admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลผู้ใช้งานทั้งหมด
$query = "SELECT id, fullname, email, role, profile_image FROM users";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("การดึงข้อมูลผิดพลาด: " . mysqli_error($conn));
}

// Display success or error message after deletion
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
    <title>Admin By Dee Talk</title>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap');

        /* สไตล์สำหรับการเคลื่อนไหวพื้นหลัง */
        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(-45deg, #3498db, #2ecc71, #9b59b6, #e74c3c);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* สไตล์สำหรับตาราง */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        td img.profile-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .tab-container {
            max-width: 1300px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .tab-header {
            display: flex;
            background-color: #3498db;
        }

        .tab-button {
            flex: 1;
            padding: 15px;
            border: none;
            background-color: transparent;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .tab-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .tab-button.active {
            background-color: #2980b9;
        }

        .tab-content {
            padding: 20px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .view-posts-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .view-posts-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-left: 5px;
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

        .chat-messages, #messages-container {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .message {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .message strong {
            color: #3498db;
        }

        .message-timestamp {
            color: #888;
            font-size: 0.9em;
            margin-left: 10px;
        }

        .message-content {
            margin-top: 10px;
            line-height: 1.4;
        }

        .reply-button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .reply-button:hover {
            background-color: #27ae60;
        }

        .reply-form {
            margin-top: 10px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
        }

        .reply-form button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .reply-form button:hover {
            background-color: #2980b9;
        }

        .reply {
            background-color: #e8f6fe;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin-top: 10px;
            border-radius: 0 4px 4px 0;
        }

        .reply strong {
            color: #2980b9;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 5px;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>
    <!-- เพิ่มในส่วน <head> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" data-tab="tab1">หน้าหลัก</button>
            <button class="tab-button" data-tab="complaints">ข้อร้องเรียน</button>
        </div>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1">
                <h1>หน้าหลัก - ข้อมูลผู้ใช้งานทั้งหมด</h1>
                <table>
                    <thead>
                        <tr>
                            <th>รูปโปรไฟล์</th>
                            <th>ID</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td>
                                    <?php
                                    $uploadDir = 'uploads/';
                                    $defaultImage = 'path/to/default/image.jpg';
                                    
                                    if (!empty($row['profile_image'])) {
                                        $imagePath = $uploadDir . basename($row['profile_image']);
                                        if (file_exists($imagePath) && is_readable($imagePath)) {
                                            echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Profile' class='profile-image'>";
                                        } else {
                                            echo "<img src='" . htmlspecialchars($defaultImage) . "' alt='Default Profile' class='profile-image'>";
                                        }
                                    } else {
                                        echo "<img src='" . htmlspecialchars($defaultImage) . "' alt='Default Profile' class='profile-image'>";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td>
                                    <a href="view_posts.php?user_id=<?php echo $row['id']; ?>" class="view-posts-btn">ดู Posts</a>
                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?');">ลบ</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
            </div>

            <div class="tab-pane" id="complaints">
            <h3>ข้อร้องเรียน</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>รูปโปรไฟล์</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ข้อร้องเรียน</th>
                        <th>วันที่</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody id="complaintsTableBody">
                    <!-- ข้อมูลจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                </tbody>
            </table>
        </div>

    <div class="chat-popup" id="chat-popup" style="display:none;">
        <div class="chat-header">
            <h3>ติดต่อ Admin</h3>
            <button id="close-chat">X</button>
        </div>
        <div class="chat-messages" id="chat-messages">
            <!-- ข้อความแชทจะถูกเพิ่มที่นี่ -->
        </div>
        <form id="chat-form">
            <textarea id="chat-input" placeholder="พิมพ์ข้อความของคุณ..." required></textarea>
            <label>
                <input type="checkbox" id="is-complaint" name="is_complaint"> ส่งเป็นข้อร้องเรียน
            </label>
            <button type="submit">ส่ง</button>
        </form>
    </div>

    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="complaintDetails"></div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
    let autoUpdateInterval;

        // เปลี่ยนแท็บเมื่อคลิก
        $('.tab-button').click(function() {
            var tabId = $(this).data('tab');
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            $('.tab-pane').removeClass('active');
            $('#' + tabId).addClass('active');

            if (tabId === 'complaints') {
                loadComplaints();
            }
        });

        // โหลดข้อมูลข้อร้องเรียน
        document.querySelector('[data-tab="complaints"]').addEventListener('click', loadComplaints);
        function loadComplaints() {
        fetch('get_complaints.php')
        .then(response => response.json())
        .then(data => {
            console.log('ข้อมูลที่ได้รับ:', data);
            const tbody = document.getElementById('complaintsTableBody');
            tbody.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(complaint => {
                    console.log('Creating row for complaint:', complaint);
                    if (!complaint.id) {
                        console.error('ไม่พบ ID สำหรับข้อร้องเรียน:', complaint);
                    }
                    const row = `
                        <tr>
                            <td><img src="${complaint.profile_image || 'path/to/default/image.jpg'}" alt="รูปโปรไฟล์" class="profile-image"></td>
                            <td>${complaint.fullname || ''}</td>
                            <td>${complaint.complaint_text || ''}</td>
                            <td>${new Date(complaint.created_at).toLocaleString('th-TH')}</td>
                            <td>
                                <button class="btn btn-primary btn-sm view-complaint" data-id="${complaint.id}">ดูรายละเอียด</button>
                                <button class="btn btn-danger btn-sm delete-complaint" data-id="${complaint.id}">ลบ</button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row; 
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5">ไม่พบข้อร้องเรียน</td></tr>';
            }
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาด:', error);
            document.getElementById('complaintsTableBody').innerHTML = '<tr><td colspan="5">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
        });
    }
        // จัดการการคลิกปุ่ม "ดูรายละเอียด"
    $(document).ready(function() {
        let autoUpdateInterval;

        // จัดการการคลิกปุ่ม "ดูรายละเอียด"
        $('.tab-button').click(function() {
        var tabId = $(this).data('tab');
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        $('#' + tabId).addClass('active');
        if (tabId === 'complaints') {
            // เริ่มการอัพเดทอัตโนมัติเมื่อเปิดแท็บข้อร้องเรียน
            startAutoUpdate();
        } else {
            // หยุดการอัพเดทอัตโนมัติเมื่อออกจากแท็บข้อร้องเรียน
            stopAutoUpdate();
            }
        }); 

        $(document).on('click', '.view-complaint', function() {
        var complaintId = $(this).data('id');
        $.ajax({
            url: 'get_complaint_details.php',
            method: 'POST', // เปลี่ยนจาก GET เป็น POST
            data: { id: complaintId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showComplaintDetails(response.complaint);
                } else {
                    alert('เกิดข้อผิดพลาดในการโหลดรายละเอียดข้อร้องเรียน: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        });
    });

    $(document).on('click', '.delete-complaint', function() {
    var complaintId = $(this).data('id');
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบข้อร้องเรียนนี้?')) {
        $.ajax({
            url: 'delete_complaint.php',
            method: 'POST',
            data: { id: complaintId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('ลบข้อร้องเรียนเรียบร้อยแล้ว');
                    loadComplaints(); // โหลดข้อมูลใหม่หลังจากลบ
                } else {
                    alert('เกิดข้อผิดพลาดในการลบข้อร้องเรียน: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        });
    }
});

    function showComplaintDetails(complaint) {
        var detailsHtml = `
            <h2>รายละเอียดข้อร้องเรียน</h2>
            <p><strong>ผู้ร้องเรียน:</strong> ${complaint.fullname}</p>
            <p><strong>วันที่:</strong> ${new Date(complaint.created_at).toLocaleString('th-TH')}</p>
            <p><strong>ข้อร้องเรียน:</strong> ${complaint.complaint_text}</p>
        `;
        $('#complaintDetails').html(detailsHtml);
        $('#complaintModal').css('display', 'block');
    }

    // จัดการการคลิกปุ่ม "ลบ"
    $(document).on('click', '.view-complaint', function() {
        var complaintId = $(this).data('id');
        console.log('Clicking view complaint, ID:', complaintId);
        $.ajax({
            url: 'get_complaint_details.php',
            method: 'POST',
            data: { id: complaintId },
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                if (response.success) {
                    $('#complaintDetails').html(response.html);
                    $('#complaintModal').css('display', 'block');
                } else {
                    alert('เกิดข้อผิดพลาดในการโหลดรายละเอียดข้อร้องเรียน: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        });
    });

    // ปิด Modal เมื่อคลิกที่ปุ่มปิด
    $('.close').click(function() {
        $('#complaintModal').css('display', 'none');
    });

    // ปิด Modal เมื่อคลิกนอกพื้นที่ Modal
    $(window).click(function(event) {
        if (event.target == document.getElementById('complaintModal')) {
            $('#complaintModal').css('display', 'none');
        }
    });

    function showComplaintDetails(complaint) {
        var detailsHtml = `
            <h2>รายละเอียดข้อร้องเรียน</h2>
            <p><strong>ผู้ร้องเรียน:</strong> ${complaint.fullname}</p>
            <p><strong>วันที่:</strong> ${new Date(complaint.created_at).toLocaleString('th-TH')}</p>
            <p><strong>ข้อร้องเรียน:</strong> ${complaint.complaint_text}</p>
        `;
        $('#complaintDetails').html(detailsHtml);
        $('#complaintModal').css('display', 'block');
    }

        });

        // โหลดข้อร้องเรียนเมื่อคลิกที่แท็บ
        document.querySelector('[data-tab="complaints"]').addEventListener('click', loadComplaints);
    });
    </script>
</body>
</html>
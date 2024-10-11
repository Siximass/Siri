<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="imagelogo" href="logo.png">
    <title>Dee Talk</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="mini.php"> <!-- เพิ่มการเชื่อมโยงกับ mini.php -->
    <head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"> <!-- ฟอนต์ Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet"> <!-- ฟอนต์ Lobster -->
    </head>
    <style>
        body {
            background-color: black; /* พื้นหลังสีดำ */
            color: white; /* สีข้อความเป็นสีขาว */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header, footer {
            background-color: #000; /* เปลี่ยนสีพื้นหลังของหัวและท้ายเป็นสีดำเข้ม */
            padding: 5px; /* ขนาด padding ของ header */
            text-align: center;
            color: white; /* เปลี่ยนสีข้อความในหัวและท้ายเป็นสีขาว */
        }

        main {
            padding: 40px; /* เพิ่ม padding เพื่อให้เนื้อหาดูยาวขึ้น */
            border: 1px solid #444; /* ขอบที่ชัดเจน */
            margin: 20px;
            background-color: #222; /* สีพื้นหลังของเนื้อหา */
            color: white; /* เปลี่ยนสีข้อความในเนื้อหาเป็นสีขาว */
            min-height: 80vh; /* เพิ่มความสูงขั้นต่ำให้กับ main */
        }

        post-area {
            background-color: white; /* พื้นที่โพสต์เป็นสีขาว */
            color: black; /* สีข้อความในพื้นที่โพสต์เป็นสีดำ */
            padding: 20px;
            border-radius: 5px;
        }

   
        .profile-popup {
            background: rgba(255, 255, 255, 0.85); /* Background with slight transparency */
            padding: 20px;
            border-radius: 15px; /* Rounded corners */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4); /* Deeper shadow for clear separation */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            width: 80%;
            max-width: 600px;
            text-align: left;
            backdrop-filter: blur(10px); /* Blur effect for better contrast */
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
            background: rgba(0, 0, 0, 0.7); /* เปลี่ยนพื้นหลังเป็นสีดำ */
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาให้ปุ่ม */
        }

        .post-button.delete {
            background: #dc3545;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาให้ปุ่มลบ */
        }

        .post-button.edit {
            background: #ffc107;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาให้ปุ่มแก้ไข */
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
            width: 60px; /* เปลี่ยนขนาดโลโก้ให้ใหญ่ขึ้น */
            height: 60px; /* เปลี่ยนขนาดโลโก้ให้ใหญ่ขึ้น */
            margin-right: 10px;
        }

        .logo a {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }
        /* ปรับขนาดของเนื้อหาหลัก */
        .main-content {
            max-width: 800px; /* กำหนดความกว้างสูงสุด */
            margin: 0 auto; /* จัดกลาง */
            position: relative; /* เพื่อให้ดาวอยู่ด้านหลัง */
            z-index: 1; /* ให้เนื้อหาหลักอยู่ด้านหน้า */
        }

        footer {
            position: relative; /* เปลี่ยนเป็น relative เพื่อไม่ให้ติดอยู่ที่ด้านล่าง */
            width: 100%;
            display: none; /* ซ่อนแถบท้าย */
        }

        .navbar {
            display: flex;
            justify-content: space-between; /* จัดให้มีพื้นที่ระหว่างไอเท็ม */
            align-items: center;
        }

        .nav-items {
            display: flex; /* ทำให้ไอเท็มในแถบอยู่ในแนวนอน */
            flex-grow: 1; /* ทำให้พื้นที่ว่างระหว่างไอเท็ม */
        }

        .nav-item {
            margin-left: 15px; /* เพิ่มระยะห่างระหว่างไอเท็ม */
            margin-right: 15px; /* เพิ่มระยะห่างจากขอบขวา */
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo"> <!-- เปลี่ยน path/to/logo.png เป็นที่อยู่ของโลโก้ -->
            <a href="#" style="font-weight: 600; font-size: 1.5rem; letter-spacing: 1px;">Dee Talk</a> <!-- ปรับ font-weight, font-size และเพิ่ม letter-spacing -->
        </div>
        <!-- <button onclick="openProfilePopup()">Open Profile</button> --> <!-- ลบปุ่มออก -->
    </header>
    <div class="navbar">
        <div class="nav-items">
            <!-- ไอเท็มอื่น ๆ ในแถบ -->
        </div>
        <a href="http://26.54.248.146/test/mini.php#post_22" class="nav-item" style="color: white;"> <!-- เปลี่ยน href เป็น URL ที่ต้องการ -->
            <span class="material-icons">home</span> <!-- ไอคอนโฮม -->
        </a>
    </div>
    <main>
        <div style="background-color: #f0f0f0; padding: 20px; border-radius: 5px; color: black;">
            <h2 style="font-family: 'Lobster', cursive; font-size: 2rem;">About</h2> <!-- เปลี่ยนฟอนต์เป็น Lobster -->
            <p style="font-family: 'Lobster', cursive; font-size: 1.5rem;">Website Purpose</p> <!-- เพิ่มหัวข้อใหม่ -->
            <p style="font-family: 'Arial', sans-serif; color: #555;">This website is created for the study of students of Rajamangala University of Technology Isan, Khon Kaen Campus. Created for the subject Database in the Mini Project topic of this subject.</p> <!-- ข้อความใหม่ -->
            <p style="font-family: 'Lobster', cursive; font-size: 1.5rem;">Website Creators:</p>
            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1; min-width: 150px; text-align: center; border: 1px solid #ccc; border-radius: 10px; padding: 10px; background-color: #fff;">
                    <h3 style="font-family: 'Roboto', sans-serif; font-weight: 700;">Nattawut</h3> <!-- ชื่อผู้พัฒนา -->
                    <p style="font-family: 'Arial', sans-serif; color: #555;">Is the person who develops the main website and improves the database. Backyard data storage.</p> <!-- รายละเอียดเพิ่มเติม -->
                </div>
                <div style="flex: 1; min-width: 150px; text-align: center; border: 1px solid #ccc; border-radius: 10px; padding: 10px; background-color: #fff;">
                    <h3 style="font-family: 'Roboto', sans-serif; font-weight: 700;">Sirimas</h3>
                    <p style="font-family: 'Arial', sans-serif; color: #555;">These are the people who work behind the scenes on websites and fix problems. and develop to make it better.</p>
                </div>
                <div style="flex: 1; min-width: 150px; text-align: center; border: 1px solid #ccc; border-radius: 10px; padding: 10px; background-color: #fff;">
                    <h3 style="font-family: 'Roboto', sans-serif; font-weight: 700;">Panyawong</h3>
                    <p style="font-family: 'Arial', sans-serif; color: #555;">Is the designer of the web page structure Web page icons and backgrounds related to design.</p>
                </div>
            </div>
        </div>
    </main>
    <footer style="position: fixed; bottom: 0; width: 100%; display: block;"> <!-- เปลี่ยน display เป็น block เพื่อให้แสดงเสมอ -->
        <p>© 2024 Dee Talk</p> <!-- เปลี่ยนชื่อเว็บไซต์เป็น Dee Talk -->
    </footer>

    <script src="script.js"></script>
    <script>
        window.onscroll = function() {
            const footer = document.querySelector('footer');
            footer.style.display = 'block'; // แสดงแถบท้ายเสมอ
        };
    </script>
</body>
</html>
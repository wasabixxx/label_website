<?php
session_start();
include 'connect_db.php';

// Kiểm tra nếu có cookie tồn tại
if (isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username']; // Đặt lại phiên từ cookie
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS để ẩn/hiện iframe và nút tắt */
        #container {
            display: none;
        }
        iframe {
            width: 100%;
            height: 600px;
            border: none;
        }
        .close-btn {
            display: none;
            margin-top: 10px;
        }
        .action-buttons button {
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <div id="header">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <a href="logout.php">Đăng xuất</a> <br>
        <a href="register.php">Tạo thêm tài khoản quản lí</a>
    </div>

    <div id="action-selection">
        <!-- Hai nút lựa chọn -->
        <div class="action-buttons">
            <button onclick="showIframe('songs')">Thêm trang nhạc mới</button>
            <button onclick="showIframe('homepage')">Chỉnh sửa trang chủ</button>
        </div>
    </div>

    <div id="container">
        <!-- Nút tắt iframe -->
        <button class="close-btn" onclick="closeIframe()">Đóng</button>

        <!-- Cột bên trái: songs.php -->
        <div id="left-pane" style="display: none;">
            <iframe id="songs-iframe" src="songs.php"></iframe>
        </div>

        <!-- Cột bên phải: homepage.php -->
        <div id="right-pane" style="display: none;">
            <iframe id="homepage-iframe" src="homepage.php"></iframe>
        </div>
    </div>

    <script>
        function showIframe(page) {
            // Hiển thị container và iframe tương ứng
            document.getElementById('container').style.display = 'block';
            document.querySelector('.close-btn').style.display = 'inline-block';

            if (page === 'songs') {
                document.getElementById('left-pane').style.display = 'block';
                document.getElementById('right-pane').style.display = 'none';
            } else if (page === 'homepage') {
                document.getElementById('right-pane').style.display = 'block';
                document.getElementById('left-pane').style.display = 'none';
            }

            // Ẩn nút lựa chọn
            document.getElementById('action-selection').style.display = 'none';
        }

        function closeIframe() {
            // Ẩn iframe và quay lại màn hình lựa chọn
            document.getElementById('container').style.display = 'none';
            document.querySelector('.close-btn').style.display = 'none';
            document.getElementById('action-selection').style.display = 'block';
        }
    </script>

</body>
</html>

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
    <link rel="stylesheet" href="style.css">
    <style>
        /* Tạo một container với hai cột */
        #container {
            display: flex;
            height: 100vh; /* Chiều cao 100% */
        }

        /* Cột trái - songs.php */
        #left-pane {
            flex: 1;
            border-right: 2px solid #ccc;
        }

        /* Cột phải - homepage.php */
        #right-pane {
            flex: 1;
        }

        /* Đặt chiều cao cho iframe */
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>

    <div id="header">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <a href="logout.php">Đăng xuất</a>
    </div>

    <div id="container">
        <!-- Cột bên trái: songs.php -->
        <div id="left-pane">
            <iframe src="songs.php"></iframe>
        </div>

        <!-- Cột bên phải: homepage.php -->
        <div id="right-pane">
            <iframe src="homepage.php"></iframe>
        </div>
    </div>

</body>
</html>

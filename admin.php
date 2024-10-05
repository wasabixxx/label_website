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
    <title>Admin - Quản lý trang nhạc</title>
    <link rel="stylesheet" href="style.css"> <!-- Đảm bảo file CSS được liên kết -->
</head>
<body>
    <div id="header">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <a href="logout.php">Đăng xuất</a>
    </div>

    <div id="container">
        <h2>Quản lý trang nhạc</h2>
        <p>
            <a href="homepage.php">Cập nhật trang chủ</a>
        </p>
        <p>
            <a href="songs.php">Thêm trang nhạc mới</a>
        </p>
    </div>
</body>
</html>

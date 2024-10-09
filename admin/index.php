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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between">
            <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
            <div>
                <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
                <a href="register.php" class="btn btn-success">Tạo thêm tài khoản quản lí</a>
            </div>
        </div>

        <!-- Tab navigation -->
        <ul class="nav nav-tabs mt-4" id="adminTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="songs-tab" data-toggle="tab" href="#songs" role="tab" aria-controls="songs" aria-selected="true">Thêm trang nhạc mới</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="homepage-tab" data-toggle="tab" href="#homepage" role="tab" aria-controls="homepage" aria-selected="false">Chỉnh sửa trang chủ</a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content" id="adminTabContent">
            <!-- Tab: Thêm trang nhạc mới -->
            <div class="tab-pane fade show active" id="songs" role="tabpanel" aria-labelledby="songs-tab">
                <?php include 'songs.php'; // Hiển thị nội dung từ file songs.php ?>
            </div>

            <!-- Tab: Chỉnh sửa trang chủ -->
            <div class="tab-pane fade" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                <?php include 'homepage.php'; // Hiển thị nội dung từ file homepage.php ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

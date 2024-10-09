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

// Kiểm tra quyền của người dùng
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
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
        .content {
            margin-top: 20px;
        }
        .nav-link {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Quản lý Admin</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-danger text-white" href="logout.php">Đăng xuất</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <div class="mt-2">
            <strong>Vai trò:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?>
        </div>

        <!-- Các lựa chọn -->
        <div class="content mt-4">
            <h4>Chọn một hành động:</h4>
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="songs.php" class="btn btn-primary btn-block">Thêm trang nhạc mới</a>
                </li>
                <li class="list-group-item">
                    <a href="homepage.php" class="btn btn-primary btn-block">Chỉnh sửa trang chủ</a>
                </li>
                <?php if ($isAdmin): ?>
                    <li class="list-group-item">
                        <a href="user.php" class="btn btn-primary btn-block">Quản lí người dùng</a>
                    </li>
                <?php else: ?>
                    <li class="list-group-item">
                        <span class="btn btn-secondary btn-block disabled" title="Bạn không có quyền truy cập">Quản lí người dùng</span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

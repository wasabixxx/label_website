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

// Đặt trang mặc định cho iframe
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Thêm kiểm tra cho tên người dùng
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Khách';
$role = isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Khách';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="../img/123.ico" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-color w-64 h-full p-4 shadow-lg transition-transform duration-300 transform -translate-x-full sm:translate-x-0">
            <div class="text-white text-2xl font-bold mb-6">Quản lý Admin</div>
            <div class="bg-white p-4 rounded-lg mb-4">
                <div class="text-teal-700 font-bold mb-1">Xin chào, <?php echo $username; ?></div>
                <div class="text-gray-600"><strong>Vai trò:</strong> <?php echo $role; ?></div>
            </div>
            <ul>
                <li class="mb-4">
                    <a href="?page=dashboard" class="sidebar-link flex items-center text-white p-2 rounded">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                </li>
                <li class="mb-4">
                    <a href="?page=songs" class="sidebar-link flex items-center text-white p-2 rounded">
                        <i class="fas fa-music mr-2"></i> Thêm trang nhạc mới
                    </a>
                </li>
                <li class="mb-4">
                    <a href="?page=homepage" class="sidebar-link flex items-center text-white p-2 rounded">
                        <i class="fas fa-home mr-2"></i> Chỉnh sửa trang chủ
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                    <li class="mb-4">
                        <a href="?page=user" class="sidebar-link flex items-center text-white p-2 rounded">
                            <i class="fas fa-users mr-2"></i> Quản lí người dùng
                        </a>
                    </li>
                <?php else: ?>
                    <li class="mb-4">
                        <span class="btn btn-secondary btn-block disabled" title="Bạn không có quyền truy cập">Quản lí người dùng</span>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="absolute bottom-0 left-0 w-full p-4">
                <div class="flex justify-between text-white">
                    <a href="logout.php" class="flex items-center p-2 rounded hover:bg-red-600">
                        <i class="fas fa-power-off mr-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-0">
            <div class="flex justify-between p-4 bg-white shadow">
                <button id="toggleSidebar" class="text-gray-700 md:hidden">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-2xl font-bold">Trang Quản Trị</h1>
            </div>
            <div class="iframe-container">
                <iframe src="<?php 
                    if ($page === 'songs') {
                        echo 'songs.php';
                    } elseif ($page === 'homepage') {
                        echo 'homepage.php';
                    } elseif ($page === 'user' && $isAdmin) {
                        echo 'user.php';
                    } else {
                        echo 'dashboard.php'; // Trang mặc định
                    }
                ?>" 
                class="w-full h-[calc(100vh-64px)] border-0" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>

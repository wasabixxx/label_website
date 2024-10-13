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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar-wrapper">
        <div class="sidebar" id="sidebar">
            <ul>
                <div class="profile">
                    <img src="img/12135.jpg" alt="profile pic">
                    <span><?php echo $username; ?> (<?php echo $role; ?>)</span>
                </div>

                <div class="indicator" id="indicator"></div>
                <li class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>" onclick="window.location.href='?page=dashboard';"><i class="fa-solid fa-house"></i><span>HELLO</span></li>
                <li class="<?php echo ($page == 'songs') ? 'active' : ''; ?>" onclick="window.location.href='?page=songs';"><i class="fa-solid fa-music"></i><span>SONGS</span></li>
                <li class="<?php echo ($page == 'homepage') ? 'active' : ''; ?>" onclick="window.location.href='?page=homepage';"><i class="fa-solid fa-home"></i><span>HOMEPAGE</span></li>
                <?php if ($isAdmin): ?>
                    <li class="<?php echo ($page == 'user') ? 'active' : ''; ?>" onclick="window.location.href='?page=user';"><i class="fa-solid fa-users"></i><span>USER</span></li>
                <?php endif; ?>
            </ul>
            <div class="logout-section">
                <a href="logout.php"><i class="fa-solid fa-power-off"></i> Đăng xuất</a>
            </div>
        </div>
        <button class="toggle-btn" id="toggleBtn">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
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
                echo 'dashboard.php';
            }
        ?>" 
        class="w-full h-[calc(100vh-64px)] border-0" frameborder="0"></iframe>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const indicator = document.getElementById('indicator');
        const menuItems = document.querySelectorAll('.sidebar ul li');

        // Toggle sidebar open/close
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (sidebar.classList.contains('open')) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            }
        });

        // Move the indicator line on hover
        menuItems.forEach((item, index) => {
            item.addEventListener('mouseover', () => {
                const itemHeight = item.offsetHeight;
                const offsetTop = item.offsetTop;
                indicator.style.top = `${offsetTop}px`;
                indicator.style.height = `${itemHeight}px`;
            });
        });
    </script>
</body>
</html>

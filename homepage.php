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

// Cập nhật trang chủ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    $home_title = $_POST['home_title'];
    $home_spotify = $_POST['home_spotify'];
    $home_apple = $_POST['home_apple'];
    $home_soundcloud = $_POST['home_soundcloud'];
    $home_youtube = $_POST['home_youtube'];
    $home_instagram = $_POST['home_instagram'];
    
    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['home_image']) && $_FILES['home_image']['error'] == 0) {
        $home_image = $_FILES['home_image']['name']; // Lấy tên file hình ảnh
        // Thêm logic để lưu tệp hình ảnh vào thư mục `uploads` (nếu cần)
    } else {
        // Nếu không có tệp nào được tải lên, có thể giữ nguyên giá trị cũ hoặc xử lý theo cách bạn muốn
        $home_image = ""; // hoặc giá trị cũ
    }

    // Cập nhật dữ liệu trang chủ
    $sql = "UPDATE homepage SET 
                title=?, 
                spotify_link=?, 
                apple_link=?, 
                soundcloud_link=?, 
                youtube_link=?, 
                instagram_link=?, 
                image=? 
            WHERE id=1";

    // Sử dụng prepared statements để bảo mật
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $home_title, $home_spotify, $home_apple, $home_soundcloud, $home_youtube, $home_instagram, $home_image);

    if ($stmt->execute()) {
        echo "Trang chủ đã được cập nhật!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trang chủ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="header">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <a href="logout.php">Đăng xuất</a>
    </div>

    <div id="container">
        <h2>Cập nhật trang chủ</h2>
        <form action="homepage.php" method="post" enctype="multipart/form-data">
            <label for="home_title">Tiêu đề:</label><br>
            <input type="text" name="home_title" required><br>
            
            <label for="home_spotify">Spotify:</label><br>
            <input type="text" name="home_spotify"><br>
            
            <label for="home_apple">Apple Music:</label><br>
            <input type="text" name="home_apple"><br>
            
            <label for="home_soundcloud">SoundCloud:</label><br>
            <input type="text" name="home_soundcloud"><br>
            
            <label for="home_youtube">YouTube Music:</label><br>
            <input type="text" name="home_youtube"><br>
            
            <label for="home_instagram">Instagram:</label><br>
            <input type="text" name="home_instagram"><br>
            
            <label for="home_image">Chọn ảnh để tải lên:</label>
            <input type="file" name="home_image"><br>
            
            <input type="submit" name="update_homepage" value="Cập nhật trang chủ">
        </form>
    </div>
</body>
</html>

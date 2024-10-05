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

// Thêm trang nhạc mới
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_page'])) {
    $title = $_POST['title'];
    $spotify_link = $_POST['spotify_link'];
    $apple_link = $_POST['apple_link'];
    $soundcloud_link = $_POST['soundcloud_link'];
    $youtube_link = $_POST['youtube_link'];
    $instagram_link = $_POST['instagram_link'];
    $slug = $_POST['slug'];

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name']; // Lấy tên file hình ảnh
        // Thêm logic để lưu tệp hình ảnh vào thư mục `uploads` (nếu cần)
    } else {
        // Nếu không có tệp nào được tải lên, có thể giữ nguyên giá trị cũ hoặc xử lý theo cách bạn muốn
        $image = ""; // hoặc giá trị cũ
    }

    // Thêm bản ghi vào database
    $sql = "INSERT INTO songs (title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image, slug)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Sử dụng prepared statements để bảo mật
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $image, $slug);

    if ($stmt->execute()) {
        echo "Trang nhạc mới đã được tạo!";
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
    <title>Thêm trang nhạc</title>
    <link rel="stylesheet" href="style.css"> <!-- Đảm bảo file CSS được liên kết -->
</head>
<body>
    <h2>Thêm trang nhạc mới</h2>
    <form action="songs.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="new_page" value="1">
        <label for="title">Tiêu đề:</label><br>
        <input type="text" name="title" required><br>
        
        <label for="spotify_link">Spotify:</label><br>
        <input type="text" name="spotify_link"><br>
        
        <label for="apple_link">Apple Music:</label><br>
        <input type="text" name="apple_link"><br>
        
        <label for="soundcloud_link">SoundCloud:</label><br>
        <input type="text" name="soundcloud_link"><br>
        
        <label for="youtube_link">YouTube Music:</label><br>
        <input type="text" name="youtube_link"><br>
        
        <label for="instagram_link">Instagram:</label><br>
        <input type="text" name="instagram_link"><br>
        
        <label for="slug">Slug (URL hậu tố):</label><br>
        <input type="text" name="slug" required><br>
        
        <label for="image">Chọn ảnh để tải lên:</label><br>
        <input type="file" name="image" required><br>
        
        <input type="submit" value="Thêm trang nhạc">
    </form>
</body>
</html>

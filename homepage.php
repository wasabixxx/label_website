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

// Lấy thông tin hiện tại của homepage
$sql = "SELECT * FROM homepage WHERE id=1";
$result = $conn->query($sql);
$current_homepage = $result->fetch_assoc();

// Cập nhật trang chủ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    $home_title = $_POST['home_title'];
    $home_spotify = $_POST['home_spotify'];
    $home_apple = $_POST['home_apple'];
    $home_soundcloud = $_POST['home_soundcloud'];
    $home_youtube = $_POST['home_youtube'];
    $home_instagram = $_POST['home_instagram'];

    $home_image = ""; // Khởi tạo biến hình ảnh

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['home_image']) && $_FILES['home_image']['error'] == 0) {
        $home_image = $_FILES['home_image']['name']; // Lấy tên file hình ảnh
        $target_dir = "uploads1/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($home_image);

        // Kiểm tra loại tệp và kích thước nếu cần
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            // Di chuyển tệp đã tải lên vào thư mục chỉ định
            if (move_uploaded_file($_FILES['home_image']['tmp_name'], $target_file)) {
                // Nếu tải lên thành công, tiếp tục cập nhật dữ liệu trong cơ sở dữ liệu
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
            } else {
                echo "Lỗi: Không thể tải lên tệp hình ảnh.";
            }
        } else {
            echo "Lỗi: Chỉ hỗ trợ các định dạng jpg, jpeg, png, gif.";
        }
    } else {
        // Nếu không có tệp nào được tải lên, có thể giữ nguyên giá trị cũ hoặc xử lý theo cách bạn muốn
        // Cập nhật dữ liệu mà không thay đổi hình ảnh
        $sql = "UPDATE homepage SET 
                    title=?, 
                    spotify_link=?, 
                    apple_link=?, 
                    soundcloud_link=?, 
                    youtube_link=?, 
                    instagram_link=? 
                WHERE id=1";

        // Sử dụng prepared statements để bảo mật
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $home_title, $home_spotify, $home_apple, $home_soundcloud, $home_youtube, $home_instagram);

        if ($stmt->execute()) {
            echo "Trang chủ đã được cập nhật mà không thay đổi hình ảnh!";
        } else {
            echo "Lỗi: " . $stmt->error;
        }
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
            <input type="text" name="home_title" value="<?php echo htmlspecialchars($current_homepage['title']); ?>" required><br>
            
            <label for="home_spotify">Spotify:</label><br>
            <input type="text" name="home_spotify" value="<?php echo htmlspecialchars($current_homepage['spotify_link']); ?>"><br>
            
            <label for="home_apple">Apple Music:</label><br>
            <input type="text" name="home_apple" value="<?php echo htmlspecialchars($current_homepage['apple_link']); ?>"><br>
            
            <label for="home_soundcloud">SoundCloud:</label><br>
            <input type="text" name="home_soundcloud" value="<?php echo htmlspecialchars($current_homepage['soundcloud_link']); ?>"><br>
            
            <label for="home_youtube">YouTube Music:</label><br>
            <input type="text" name="home_youtube" value="<?php echo htmlspecialchars($current_homepage['youtube_link']); ?>"><br>
            
            <label for="home_instagram">Instagram:</label><br>
            <input type="text" name="home_instagram" value="<?php echo htmlspecialchars($current_homepage['instagram_link']); ?>"><br>
            
            <label for="home_image">Chọn ảnh để tải lên:</label>
            <input type="file" name="home_image"><br>
            
            <input type="submit" name="update_homepage" value="Cập nhật trang chủ">
        </form>
    </div>
</body>
</html>

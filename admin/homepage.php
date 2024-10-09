<?php
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

// Lưu phiên bản hiện tại của homepage vào bảng homepage_versions
function saveCurrentHomepageVersion($conn) {
    // Lấy thông tin hiện tại của homepage
    $sql = "SELECT * FROM homepage WHERE id = 1";
    $result = $conn->query($sql);
    $current_homepage = $result->fetch_assoc();

    // Chèn phiên bản hiện tại vào bảng homepage_versions
    $sql = "INSERT INTO homepage_versions 
        (homepage_id, title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", 
        $current_homepage['id'], 
        $current_homepage['title'], 
        $current_homepage['spotify_link'], 
        $current_homepage['apple_link'], 
        $current_homepage['soundcloud_link'], 
        $current_homepage['youtube_link'], 
        $current_homepage['instagram_link'], 
        $current_homepage['image']
    );
    $stmt->execute();
}

// Lấy thông tin hiện tại của homepage
$sql = "SELECT * FROM homepage WHERE id=1";
$result = $conn->query($sql);
$current_homepage = $result->fetch_assoc();

// Cập nhật trang chủ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    // Lưu phiên bản hiện tại trước khi cập nhật
    saveCurrentHomepageVersion($conn);
    
    $home_title = $_POST['home_title'];
    $home_spotify = $_POST['home_spotify'];
    $home_apple = $_POST['home_apple'];
    $home_soundcloud = $_POST['home_soundcloud'];
    $home_youtube = $_POST['home_youtube'];
    $home_instagram = $_POST['home_instagram'];
    $home_color = $_POST['home_color']; // Nhận giá trị màu

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
                            image=?, 
                            color=? 
                        WHERE id=1";

                // Sử dụng prepared statements để bảo mật
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssss", $home_title, $home_spotify, $home_apple, $home_soundcloud, $home_youtube, $home_instagram, $home_image, $home_color);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Trang chủ đã được cập nhật!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Lỗi: Không thể tải lên tệp hình ảnh.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Lỗi: Chỉ hỗ trợ các định dạng jpg, jpeg, png, gif.</div>";
        }
    } else {
        // Nếu không có tệp nào được tải lên, cập nhật dữ liệu mà không thay đổi hình ảnh
        $sql = "UPDATE homepage SET 
                    title=?, 
                    spotify_link=?, 
                    apple_link=?, 
                    soundcloud_link=?, 
                    youtube_link=?, 
                    instagram_link=?, 
                    color=? 
                WHERE id=1";

        // Sử dụng prepared statements để bảo mật
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $home_title, $home_spotify, $home_apple, $home_soundcloud, $home_youtube, $home_instagram, $home_color);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Trang chủ đã được cập nhật mà không thay đổi hình ảnh!</div>";
        } else {
            echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
        }
    }
}

// Xử lý việc phục hồi một phiên bản cũ
if (isset($_GET['version_id'])) {
    $version_id = $_GET['version_id'];
    
    // Lấy thông tin phiên bản đã chọn
    $sql = "SELECT * FROM homepage_versions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $version_id);
    $stmt->execute();
    $version = $stmt->get_result()->fetch_assoc();
    
    // Khôi phục phiên bản về bảng homepage
    $sql = "UPDATE homepage SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, image=?, color=? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", 
        $version['title'], 
        $version['spotify_link'], 
        $version['apple_link'], 
        $version['soundcloud_link'], 
        $version['youtube_link'], 
        $version['instagram_link'], 
        $version['image'],
        $version['color'] // Khôi phục giá trị màu
    );
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Đã khôi phục phiên bản từ " . $version['version_date'] . "</div>";
    }
}

// Xử lý xóa các phiên bản đã chọn
if (isset($_POST['delete_versions'])) {
    if (!empty($_POST['version_ids'])) {
        foreach ($_POST['version_ids'] as $id) {
            $sql = "DELETE FROM homepage_versions WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        echo "<div class='alert alert-success'>Đã xóa các phiên bản đã chọn.</div>";
    }
}

// Lấy danh sách phiên bản cũ của homepage
$sql = "SELECT * FROM homepage_versions WHERE homepage_id = 1 ORDER BY version_date DESC";
$result_versions = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trang chủ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Cập nhật trang chủ</h2>
        <form action="homepage.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="home_title">Tiêu đề:</label>
                <input type="text" class="form-control" name="home_title" value="<?php echo htmlspecialchars($current_homepage['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="home_spotify">Spotify:</label>
                <input type="text" class="form-control" name="home_spotify" value="<?php echo htmlspecialchars($current_homepage['spotify_link']); ?>">
            </div>
            <div class="form-group">
                <label for="home_apple">Apple Music:</label>
                <input type="text" class="form-control" name="home_apple" value="<?php echo htmlspecialchars($current_homepage['apple_link']); ?>">
            </div>
            <div class="form-group">
                <label for="home_soundcloud">SoundCloud:</label>
                <input type="text" class="form-control" name="home_soundcloud" value="<?php echo htmlspecialchars($current_homepage['soundcloud_link']); ?>">
            </div>
            <div class="form-group">
                <label for="home_youtube">YouTube Music:</label>
                <input type="text" class="form-control" name="home_youtube" value="<?php echo htmlspecialchars($current_homepage['youtube_link']); ?>">
            </div>
            <div class="form-group">
                <label for="home_instagram">Instagram:</label>
                <input type="text" class="form-control" name="home_instagram" value="<?php echo htmlspecialchars($current_homepage['instagram_link']); ?>">
            </div>
            <div class="form-group">
                <label for="home_image">Hình ảnh:</label>
                <input type="file" class="form-control-file" name="home_image">
            </div>
            <div class="form-group">
                <label for="home_color">Chọn màu:</label>
                <input type="color" class="form-control" name="home_color" value="<?php echo htmlspecialchars($current_homepage['color']); ?>" required>
            </div>
            <button type="submit" name="update_homepage" class="btn btn-primary">Cập nhật</button>
        </form>

        <h3 class="mt-5">Các phiên bản cũ</h3>
        <form action="homepage.php" method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Tiêu đề</th>
                        <th>Ngày phiên bản</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($version = $result_versions->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="version_ids[]" value="<?php echo $version['id']; ?>"></td>
                            <td><?php echo htmlspecialchars($version['title']); ?></td>
                            <td><?php echo htmlspecialchars($version['version_date']); ?></td>
                            <td>
                                <a href="homepage.php?version_id=<?php echo $version['id']; ?>" class="btn btn-success">Khôi phục</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="delete_versions" class="btn btn-danger">Xóa phiên bản đã chọn</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>

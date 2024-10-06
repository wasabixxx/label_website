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
        // Nếu không có tệp nào được tải lên, cập nhật dữ liệu mà không thay đổi hình ảnh
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
    $sql = "UPDATE homepage SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, image=? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", 
        $version['title'], 
        $version['spotify_link'], 
        $version['apple_link'], 
        $version['soundcloud_link'], 
        $version['youtube_link'], 
        $version['instagram_link'], 
        $version['image']
    );
    if ($stmt->execute()) {
        echo "Đã khôi phục phiên bản từ " . $version['version_date'];
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
        echo "Đã xóa các phiên bản đã chọn.";
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

        <h2>Phiên bản hiện tại</h2>
        <div id="current-version">
            <h3>Phiên bản hiện tại</h3>
            <p><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($current_homepage['title']); ?></p>
            <p><strong>Spotify:</strong> <?php echo htmlspecialchars($current_homepage['spotify_link']); ?></p>
            <p><strong>Apple Music:</strong> <?php echo htmlspecialchars($current_homepage['apple_link']); ?></p>
            <p><strong>SoundCloud:</strong> <?php echo htmlspecialchars($current_homepage['soundcloud_link']); ?></p>
            <p><strong>YouTube Music:</strong> <?php echo htmlspecialchars($current_homepage['youtube_link']); ?></p>
            <p><strong>Instagram:</strong> <?php echo htmlspecialchars($current_homepage['instagram_link']); ?></p>
            <img src="uploads1/<?php echo htmlspecialchars($current_homepage['image']); ?>" alt="Hình ảnh hiện tại" style="max-width: 300px;"><br>
        </div>

        <h2>Phiên bản trước đó</h2>
        <form action="homepage.php" method="post">
            <table border="1">
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Ngày phiên bản</th>
                        <th>Tiêu đề</th>
                        <th>Spotify</th>
                        <th>Apple Music</th>
                        <th>SoundCloud</th>
                        <th>YouTube Music</th>
                        <th>Instagram</th>
                        <th>Hình ảnh</th>
                        <th>Khôi phục</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_versions->fetch_assoc()) { ?>
                        <tr>
                            <td><input type="checkbox" name="version_ids[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['version_date']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['spotify_link']); ?></td>
                            <td><?php echo htmlspecialchars($row['apple_link']); ?></td>
                            <td><?php echo htmlspecialchars($row['soundcloud_link']); ?></td>
                            <td><?php echo htmlspecialchars($row['youtube_link']); ?></td>
                            <td><?php echo htmlspecialchars($row['instagram_link']); ?></td>
                            <td><img src="uploads1/<?php echo htmlspecialchars($row['image']); ?>" alt="Hình ảnh phiên bản cũ" style="max-width: 100px;"></td>
                            <td><a href="homepage.php?version_id=<?php echo $row['id']; ?>">Khôi phục phiên bản này</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <input type="submit" name="delete_versions" value="Xóa các phiên bản đã chọn">
        </form>
    </div>

    <!-- <script>
    setTimeout(function() {
        window.location.href = "homepage.php?rand=" + new Date().getTime();
    }, 5000); // Thay đổi số 5000 để điều chỉnh khoảng thời gian tự động làm mới (5000ms = 5 giây)
</script> -->
</body>
</html>
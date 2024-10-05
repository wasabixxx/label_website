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

// Xóa bài hát
if (isset($_POST['delete_songs'])) {
    if (isset($_POST['selected_songs'])) {
        foreach ($_POST['selected_songs'] as $id) {
            $sql = "DELETE FROM songs WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        echo "Các bài hát đã được xóa!";
    }
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
        $target_dir = "uploads/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($image);

        // Kiểm tra loại tệp và kích thước nếu cần
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            // Di chuyển tệp đã tải lên vào thư mục chỉ định
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
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
            } else {
                echo "Lỗi: Không thể tải lên tệp hình ảnh.";
            }
        } else {
            echo "Lỗi: Chỉ hỗ trợ các định dạng jpg, jpeg, png, gif.";
        }
    } else {
        echo "Lỗi: Không có tệp nào được tải lên.";
    }
}

// Lấy danh sách bài hát từ cơ sở dữ liệu
$sql = "SELECT * FROM songs";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách bài hát</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="header">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
        <a href="logout.php">Đăng xuất</a>
    </div>

    <div id="container">
        <h2>Danh sách bài hát</h2>

        <form action="songs.php" method="post">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all"></th>
                        <th>Tiêu đề</th>
                        <th>Spotify</th>
                        <th>Apple Music</th>
                        <th>SoundCloud</th>
                        <th>YouTube Music</th>
                        <th>Instagram</th>
                        <th>Hình ảnh</th>
                        <th>Slug</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($song = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><input type="checkbox" name="selected_songs[]" value="<?php echo $song['id']; ?>"></td>
                            <td><?php echo htmlspecialchars($song['title']); ?></td>
                            <td><?php echo htmlspecialchars($song['spotify_link']); ?></td>
                            <td><?php echo htmlspecialchars($song['apple_link']); ?></td>
                            <td><?php echo htmlspecialchars($song['soundcloud_link']); ?></td>
                            <td><?php echo htmlspecialchars($song['youtube_link']); ?></td>
                            <td><?php echo htmlspecialchars($song['instagram_link']); ?></td>
                            <td><img src="uploads/<?php echo htmlspecialchars($song['image']); ?>" alt="Hình ảnh bài hát" style="width: 50px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($song['slug']); ?></td>
                            <td>
                                <a href="fix.php?id=<?php echo $song['id']; ?>">Sửa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <input type="submit" name="delete_songs" value="Xóa các bài hát đã chọn">
        </form>

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
    </div>

    <script>
        // Chọn/ bỏ chọn tất cả các checkbox
        document.getElementById('select_all').onclick = function() {
            const checkboxes = document.querySelectorAll('input[name="selected_songs[]"]');
            for (const checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        };
    </script>
</body>
</html>

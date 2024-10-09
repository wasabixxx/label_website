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

// Xóa bài hát và xóa ảnh tương ứng
if (isset($_POST['delete_songs'])) {
    if (isset($_POST['selected_songs'])) {
        foreach ($_POST['selected_songs'] as $id) {
            // Lấy tên file ảnh từ database
            $sql_image = "SELECT image FROM songs WHERE id = ?";
            $stmt_image = $conn->prepare($sql_image);
            $stmt_image->bind_param("i", $id);
            $stmt_image->execute();
            $result_image = $stmt_image->get_result();
            
            if ($result_image->num_rows > 0) {
                $row = $result_image->fetch_assoc();
                $image_path = 'uploads/' . $row['image'];
                
                // Xóa ảnh khỏi thư mục nếu tồn tại
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            // Xóa bài hát khỏi cơ sở dữ liệu
            $sql = "DELETE FROM songs WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        echo "Các bài hát và ảnh tương ứng đã được xóa!";
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
    $color = $_POST['color']; // Lấy giá trị màu từ ô nhập

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
                $sql = "INSERT INTO songs (title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image, slug, color)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                // Sử dụng prepared statements để bảo mật
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssss", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $image, $slug, $color);

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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Thêm trang nhạc mới</h2>
        <form action="songs.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="new_page" value="1">
            <div class="form-group">
                <label for="title">Tiêu đề:</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
                <label for="spotify_link">Spotify:</label>
                <input type="text" class="form-control" name="spotify_link">
            </div>
            <div class="form-group">
                <label for="apple_link">Apple Music:</label>
                <input type="text" class="form-control" name="apple_link">
            </div>
            <div class="form-group">
                <label for="soundcloud_link">SoundCloud:</label>
                <input type="text" class="form-control" name="soundcloud_link">
            </div>
            <div class="form-group">
                <label for="youtube_link">YouTube Music:</label>
                <input type="text" class="form-control" name="youtube_link">
            </div>
            <div class="form-group">
                <label for="instagram_link">Instagram:</label>
                <input type="text" class="form-control" name="instagram_link">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL hậu tố):</label>
                <input type="text" class="form-control" name="slug" required>
            </div>
            <div class="form-group">
                <label for="color">Màu sắc:</label>
                <input type="color" class="form-control" name="color" value="#ff0000"> <!-- Trình chọn màu -->
            </div>
            <div class="form-group">
                <label for="image">Chọn ảnh để tải lên:</label>
                <input type="file" class="form-control-file" name="image" required>
            </div>
            <button type="submit" class="btn btn-primary">Thêm trang nhạc</button>
        </form>

        <h2 class="mt-5">Danh sách bài hát</h2>
        <form action="songs.php" method="post">
            <table class="table table-bordered">
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
                        <th>Màu sắc</th>
                        <th>Link đã gen</th>
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
                            <td><img src="uploads/<?php echo htmlspecialchars($song['image']); ?>" alt="Hình ảnh bài hát" class="img-thumbnail" style="width: 50px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($song['slug']); ?></td>
                            <td style="background-color: <?php echo htmlspecialchars($song['color']); ?>; width: 50px;"></td>
                            <td>
                                <a href="../<?php echo htmlspecialchars($song['slug']); ?>" target="_blank">Xem trang</a>
                            </td>
                            <td>
                                <a href="fix.php?id=<?php echo $song['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button type="submit" name="delete_songs" class="btn btn-danger">Xóa đã chọn</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chọn tất cả checkbox
        document.getElementById('select_all').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_songs[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    </script>
</body>
</html>

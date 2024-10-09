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
        (homepage_id, title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image, color) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", 
        $current_homepage['id'], 
        $current_homepage['title'], 
        $current_homepage['spotify_link'], 
        $current_homepage['apple_link'], 
        $current_homepage['soundcloud_link'], 
        $current_homepage['youtube_link'], 
        $current_homepage['instagram_link'], 
        $current_homepage['image'],
        $current_homepage['color'] // Thêm màu vào phiên bản
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
    
    $home_title = $_POST['home_title'] ?? $current_homepage['title'];
    $home_spotify = $_POST['home_spotify'] ?? $current_homepage['spotify_link'];
    $home_apple = $_POST['home_apple'] ?? $current_homepage['apple_link'];
    $home_soundcloud = $_POST['home_soundcloud'] ?? $current_homepage['soundcloud_link'];
    $home_youtube = $_POST['home_youtube'] ?? $current_homepage['youtube_link'];
    $home_instagram = $_POST['home_instagram'] ?? $current_homepage['instagram_link'];
    $home_color = $_POST['home_color'] ?? $current_homepage['color']; // Nhận giá trị màu

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

// Xử lý xóa các ảnh đã chọn
if (isset($_POST['delete_images'])) {
    if (!empty($_POST['image_names'])) {
        foreach ($_POST['image_names'] as $image_name) {
            $file_path = "uploads1/" . $image_name;
            if (file_exists($file_path)) {
                unlink($file_path); // Xóa tệp
            }
        }
        echo "<div class='alert alert-success'>Đã xóa các ảnh đã chọn.</div>";
    }
}

// Lấy danh sách phiên bản cũ của homepage
$sql = "SELECT * FROM homepage_versions WHERE homepage_id = 1 ORDER BY version_date DESC";
$result_versions = $conn->query($sql);

// Lấy danh sách ảnh trong thư mục uploads1
$images = array_diff(scandir('uploads1'), array('..', '.'));
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
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="../admin" class="btn btn-danger">Quay về trang ADMIN</a>
                    </div>
                </div>
        <div class="row">
            <!-- Phần bên trái: Form cập nhật -->
            <div class="col-md-6">
                <h2>Cập nhật trang chủ</h2>
                <form action="homepage.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="home_title">Tiêu đề:</label>
                        <input type="text" class="form-control" name="home_title" value="<?= $current_homepage['title'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_spotify">Link Spotify:</label>
                        <input type="text" class="form-control" name="home_spotify" value="<?= $current_homepage['spotify_link'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_apple">Link Apple Music:</label>
                        <input type="text" class="form-control" name="home_apple" value="<?= $current_homepage['apple_link'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_soundcloud">Link SoundCloud:</label>
                        <input type="text" class="form-control" name="home_soundcloud" value="<?= $current_homepage['soundcloud_link'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_youtube">Link YouTube:</label>
                        <input type="text" class="form-control" name="home_youtube" value="<?= $current_homepage['youtube_link'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_instagram">Link Instagram:</label>
                        <input type="text" class="form-control" name="home_instagram" value="<?= $current_homepage['instagram_link'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="home_image">Hình ảnh:</label>
                        <input type="file" class="form-control-file" name="home_image">
                    </div>
                    <div class="form-group">
                        <label for="home_color">Màu sắc:</label>
                        <input type="color" class="form-control" name="home_color" value="<?= $current_homepage['color'] ?>">
                    </div>
                    <button type="submit" name="update_homepage" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>

            <!-- Phần bên phải: Hình ảnh hiện tại -->
            <div class="col-md-6">
                <h3 class="mt-5">Hình ảnh hiện tại:</h3>
                <img src="uploads1/<?= $current_homepage['image'] ?>" alt="Current Homepage Image" class="img-fluid" />

                <h3 class="mt-5">Danh sách hình ảnh trong uploads1:</h3>
                <form action="homepage.php" method="post">
                    <div class="form-group">
                        <?php foreach ($images as $image): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="image_names[]" value="<?= $image ?>" id="<?= $image ?>">
                                <label class="form-check-label" for="<?= $image ?>"><?= $image ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="delete_images" class="btn btn-danger">Xóa các ảnh đã chọn</button>
                </form>

                <h3 class="mt-5">Danh sách phiên bản cũ:</h3>
                <form action="homepage.php" method="post">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Chọn</th>
                                <th>Tiêu đề</th>
                                <th>Ngày phiên bản</th>
                                <th>Hình ảnh</th>
                                <th>Khôi phục</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($version = $result_versions->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="version_ids[]" value="<?= $version['id'] ?>">
                                    </td>
                                    <td><?= $version['title'] ?></td>
                                    <td><?= $version['version_date'] ?></td>
                                    <td><img src="uploads1/<?= $version['image'] ?>" alt="Version Image" class="img-thumbnail" width="100"></td>
                                    <td>
                                        <a href="homepage.php?version_id=<?= $version['id'] ?>" class="btn btn-warning">Khôi phục</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="delete_versions" class="btn btn-danger">Xóa các phiên bản đã chọn</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

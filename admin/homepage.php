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
        (homepage_id, title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, facebook_link, tiktok_link, zalo_link, image, color) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssss", 
        $current_homepage['id'], 
        $current_homepage['title'], 
        $current_homepage['spotify_link'], 
        $current_homepage['apple_link'], 
        $current_homepage['soundcloud_link'], 
        $current_homepage['youtube_link'], 
        $current_homepage['instagram_link'], 
        $current_homepage['facebook_link'], // Thêm facebook_link
        $current_homepage['tiktok_link'], // Thêm tiktok_link
        $current_homepage['zalo_link'], // Thêm zalo_link
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
    $home_facebook = $_POST['home_facebook'] ?? $current_homepage['facebook_link']; // Nhận giá trị Facebook link
    $home_tiktok = $_POST['home_tiktok'] ?? $current_homepage['tiktok_link'];       // Nhận giá trị TikTok link
    $home_zalo = $_POST['home_zalo'] ?? $current_homepage['zalo_link'];             // Nhận giá trị Zalo link
    $home_color = $_POST['home_color'] ?? $current_homepage['color']; // Nhận giá trị màu

    $home_image = ""; // Khởi tạo biến hình ảnh

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['home_image']) && $_FILES['home_image']['error'] == 0) {
        $home_image = $_FILES['home_image']['name']; // Lấy tên file hình ảnh
        $target_dir = "../uploads1/"; // Đường dẫn đến thư mục lưu ảnh
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
                            facebook_link=?, 
                            tiktok_link=?, 
                            zalo_link=?, 
                            image=?, 
                            color=? 
                        WHERE id=1";

                // Sử dụng prepared statements để bảo mật
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssss", 
                    $home_title, 
                    $home_spotify, 
                    $home_apple, 
                    $home_soundcloud, 
                    $home_youtube, 
                    $home_instagram, 
                    $home_facebook, 
                    $home_tiktok, 
                    $home_zalo, 
                    $home_image, 
                    $home_color
                );

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
                    facebook_link=?, 
                    tiktok_link=?, 
                    zalo_link=?, 
                    color=? 
                WHERE id=1";

        // Sử dụng prepared statements để bảo mật
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", 
            $home_title, 
            $home_spotify, 
            $home_apple, 
            $home_soundcloud, 
            $home_youtube, 
            $home_instagram, 
            $home_facebook, 
            $home_tiktok, 
            $home_zalo, 
            $home_color
        );

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
    $sql = "UPDATE homepage SET 
                title=?, 
                spotify_link=?, 
                apple_link=?, 
                soundcloud_link=?, 
                youtube_link=?, 
                instagram_link=?, 
                facebook_link=?, 
                tiktok_link=?, 
                zalo_link=?, 
                image=?, 
                color=? 
            WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", 
        $version['title'], 
        $version['spotify_link'], 
        $version['apple_link'], 
        $version['soundcloud_link'], 
        $version['youtube_link'], 
        $version['instagram_link'], 
        $version['facebook_link'], // Khôi phục giá trị Facebook link
        $version['tiktok_link'],   // Khôi phục giá trị TikTok link
        $version['zalo_link'],     // Khôi phục giá trị Zalo link
        $version['image'], 
        $version['color']
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
            $file_path = "../uploads1/" . $image_name;
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

// Lấy danh sách ảnh trong thư mục ../uploads1
$images = array_diff(scandir('../uploads1'), array('..', '.'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trang chủ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Thêm một số CSS tùy chỉnh nếu cần */
        .form-section {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .current-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        .version-image {
            max-width: 100px;
            height: auto;
        }
        .image-list img {
            max-width: 100px;
            height: auto;
        }
        .image-list {
        width: 100px; /* Đặt kích thước cố định cho ảnh */
        height: auto; /* Đảm bảo tỷ lệ ảnh không bị bóp méo */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <!-- Bên trái: Form cập nhật -->
            <div class="col-md-6">
                <div class="form-section">
                    <h2>Cập nhật trang chủ</h2>
                    <form action="homepage.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="home_title">Tiêu đề:</label>
                            <input type="text" class="form-control" name="home_title" value="<?= htmlspecialchars($current_homepage['title']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_spotify">Link Spotify:</label>
                            <input type="text" class="form-control" name="home_spotify" value="<?= htmlspecialchars($current_homepage['spotify_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_apple">Link Apple Music:</label>
                            <input type="text" class="form-control" name="home_apple" value="<?= htmlspecialchars($current_homepage['apple_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_soundcloud">Link SoundCloud:</label>
                            <input type="text" class="form-control" name="home_soundcloud" value="<?= htmlspecialchars($current_homepage['soundcloud_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_youtube">Link YouTube:</label>
                            <input type="text" class="form-control" name="home_youtube" value="<?= htmlspecialchars($current_homepage['youtube_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_instagram">Link Instagram:</label>
                            <input type="text" class="form-control" name="home_instagram" value="<?= htmlspecialchars($current_homepage['instagram_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_facebook">Link Facebook:</label>
                            <input type="text" class="form-control" name="home_facebook" value="<?= htmlspecialchars($current_homepage['facebook_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_tiktok">Link TikTok:</label>
                            <input type="text" class="form-control" name="home_tiktok" value="<?= htmlspecialchars($current_homepage['tiktok_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_zalo">Link Zalo:</label>
                            <input type="text" class="form-control" name="home_zalo" value="<?= htmlspecialchars($current_homepage['zalo_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_color">Màu sắc:</label>
                            <input type="color" class="form-control" name="home_color" value="<?= htmlspecialchars($current_homepage['color']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="home_image">Hình ảnh:</label>
                            <input type="file" class="form-control-file" name="home_image">
                        </div>
                        <button type="submit" name="update_homepage" class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>
            </div>

            <!-- Bên phải: Hình ảnh hiện tại và các phiên bản -->
            <div class="col-md-6">
                <div class="form-section">
                    <h2>Hình ảnh hiện tại:</h2>
                    <?php if (!empty($current_homepage['image'])) : ?>
                        <img src="../uploads1/<?= htmlspecialchars($current_homepage['image']) ?>" alt="Current Homepage Image" class="img-fluid current-image">
                    <?php else: ?>
                        <p>Không có hình ảnh hiện tại.</p>
                    <?php endif; ?>

                    <h3 class="mt-4">Danh sách phiên bản cũ:</h3>
                    <form action="homepage.php" method="post">
                        <table class="table table-bordered">
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
                                <?php while ($version = $result_versions->fetch_assoc()) : ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="version_ids[]" value="<?= htmlspecialchars($version['id']) ?>">
                                        </td>
                                        <td><?= htmlspecialchars($version['title']) ?></td>
                                        <td><?= htmlspecialchars($version['version_date']) ?></td>
                                        <td>
                                            <?php if (!empty($version['image'])): ?>
                                                <img src="../uploads1/<?= htmlspecialchars($version['image']) ?>" alt="Version Image" class="version-image">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="homepage.php?version_id=<?= htmlspecialchars($version['id']) ?>" class="btn btn-warning btn-sm">Khôi phục</a>
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

       <!-- Danh sách ảnh trong ../uploads1 -->
<div class="row mt-5">
    <div class="col-12">
        <div class="form-section">
            <h2>Danh sách ảnh trong ../uploads1:</h2>
            <form method="POST">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Chọn</th>
                            <th>Tên ảnh</th>
                            <th>Xem trước</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($images as $image) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="image_names[]" value="<?= htmlspecialchars($image) ?>">
                                </td>
                                <td><?= htmlspecialchars($image) ?></td>
                                <td>
                                    <img src="../uploads1/<?= htmlspecialchars($image) ?>" alt="Image" class="img-thumbnail image-list">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="delete_images" class="btn btn-danger">Xóa các ảnh đã chọn</button>
            </form>
        </div>
    </div>
</div>

    </div>
</body>
</html>

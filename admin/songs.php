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
    $color = $_POST['color'];

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name']; // Lấy tên file hình ảnh
        $target_dir = "uploads/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($image);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $sql = "INSERT INTO songs (title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image, slug, color)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

// Cập nhật bài hát
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_song_id'])) {
    $edit_song_id = $_POST['edit_song_id'];
    $edit_title = $_POST['edit_title'];
    $edit_spotify_link = $_POST['edit_spotify_link'];
    $edit_apple_link = $_POST['edit_apple_link'];
    $edit_soundcloud_link = $_POST['edit_soundcloud_link'];
    $edit_youtube_link = $_POST['edit_youtube_link'];
    $edit_instagram_link = $_POST['edit_instagram_link'];
    $edit_slug = $_POST['edit_slug'];
    $edit_color = $_POST['edit_color'];

    // Kiểm tra nếu có ảnh mới được tải lên
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
        $new_image = $_FILES['edit_image']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($new_image);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            // Di chuyển ảnh mới vào thư mục uploads
            if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target_file)) {
                // Lấy ảnh cũ từ database
                $sql_image = "SELECT image FROM songs WHERE id = ?";
                $stmt_image = $conn->prepare($sql_image);
                $stmt_image->bind_param("i", $edit_song_id);
                $stmt_image->execute();
                $result_image = $stmt_image->get_result();

                if ($result_image->num_rows > 0) {
                    $row = $result_image->fetch_assoc();
                    $old_image_path = 'uploads/' . $row['image'];

                    // Xóa ảnh cũ nếu tồn tại
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }

                // Cập nhật bài hát với ảnh mới
                $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, slug=?, color=?, image=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssi", $edit_title, $edit_spotify_link, $edit_apple_link, $edit_soundcloud_link, $edit_youtube_link, $edit_instagram_link, $edit_slug, $edit_color, $new_image, $edit_song_id);

                if ($stmt->execute()) {
                    echo "Bài hát và hình ảnh đã được cập nhật!";
                } else {
                    echo "Lỗi: " . $stmt->error;
                }
            } else {
                echo "Lỗi: Không thể tải lên tệp hình ảnh mới.";
            }
        } else {
            echo "Lỗi: Chỉ hỗ trợ các định dạng jpg, jpeg, png, gif.";
        }
    } else {
        // Cập nhật bài hát mà không thay đổi ảnh
        $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, slug=?, color=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $edit_title, $edit_spotify_link, $edit_apple_link, $edit_soundcloud_link, $edit_youtube_link, $edit_instagram_link, $edit_slug, $edit_color, $edit_song_id);

        if ($stmt->execute()) {
            echo "Bài hát đã được cập nhật!";
        } else {
            echo "Lỗi: " . $stmt->error;
        }
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <a href="../admin" class="btn btn-danger">Quay về trang ADMIN</a>
        </div>
    </div>
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
            <label for="youtube_link">YouTube:</label>
            <input type="text" class="form-control" name="youtube_link">
        </div>
        <div class="form-group">
            <label for="instagram_link">Instagram:</label>
            <input type="text" class="form-control" name="instagram_link">
        </div>
        <div class="form-group">
            <label for="image">Chọn hình ảnh:</label>
            <input type="file" class="form-control-file" name="image" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug:</label>
            <input type="text" class="form-control" name="slug">
        </div>
        <div class="form-group">
            <label for="color">Màu nền:</label>
            <input type="text" class="form-control" name="color">
        </div>
        <button type="submit" class="btn btn-primary">Thêm mới</button>
    </form>

    <h2 class="mt-5">Danh sách bài hát</h2>
    <form action="songs.php" method="post">
        <button type="submit" name="delete_songs" class="btn btn-danger mb-3">Xóa các mục đã chọn</button>
        <table class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th>Tiêu đề</th>
                <th>Spotify</th>
                <th>Apple Music</th>
                <th>SoundCloud</th>
                <th>YouTube</th>
                <th>Instagram</th>
                <th>Hình ảnh</th>
                <th>Slug</th>
                <th>Màu nền</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="selected_songs[]" value="<?= $row['id'] ?>"></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['spotify_link']) ?></td>
                    <td><?= htmlspecialchars($row['apple_link']) ?></td>
                    <td><?= htmlspecialchars($row['soundcloud_link']) ?></td>
                    <td><?= htmlspecialchars($row['youtube_link']) ?></td>
                    <td><?= htmlspecialchars($row['instagram_link']) ?></td>
                    <td><img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Image" width="50"></td>
                    <td><?= htmlspecialchars($row['slug']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editModal<?= $row['id'] ?>">Chỉnh sửa
                        </button>
                    </td>
                </tr>

                <!-- Modal chỉnh sửa bài hát -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="songs.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="edit_song_id" value="<?= $row['id'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Chỉnh sửa bài hát</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="edit_title">Tiêu đề:</label>
                                        <input type="text" class="form-control" name="edit_title"
                                               value="<?= htmlspecialchars($row['title']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_spotify_link">Spotify:</label>
                                        <input type="text" class="form-control" name="edit_spotify_link"
                                               value="<?= htmlspecialchars($row['spotify_link']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_apple_link">Apple Music:</label>
                                        <input type="text" class="form-control" name="edit_apple_link"
                                               value="<?= htmlspecialchars($row['apple_link']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_soundcloud_link">SoundCloud:</label>
                                        <input type="text" class="form-control" name="edit_soundcloud_link"
                                               value="<?= htmlspecialchars($row['soundcloud_link']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_youtube_link">YouTube:</label>
                                        <input type="text" class="form-control" name="edit_youtube_link"
                                               value="<?= htmlspecialchars($row['youtube_link']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_instagram_link">Instagram:</label>
                                        <input type="text" class="form-control" name="edit_instagram_link"
                                               value="<?= htmlspecialchars($row['instagram_link']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_slug">Slug:</label>
                                        <input type="text" class="form-control" name="edit_slug"
                                               value="<?= htmlspecialchars($row['slug']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_color">Màu nền:</label>
                                        <input type="text" class="form-control" name="edit_color"
                                               value="<?= htmlspecialchars($row['color']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_image">Chọn ảnh mới (nếu muốn thay đổi):</label>
                                        <input type="file" class="form-control-file" name="edit_image" id="edit_image">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            </tbody>
        </table>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

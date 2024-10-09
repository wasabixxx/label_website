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

    // Cập nhật dữ liệu bài hát
    $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, slug=?, color=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $edit_title, $edit_spotify_link, $edit_apple_link, $edit_soundcloud_link, $edit_youtube_link, $edit_instagram_link, $edit_slug, $edit_color, $edit_song_id);

    if ($stmt->execute()) {
        echo "Bài hát đã được cập nhật!";
    } else {
        echo "Lỗi: " . $stmt->error;
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
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editSongModal"
                                data-id="<?php echo $song['id']; ?>"
                                data-title="<?php echo htmlspecialchars($song['title']); ?>"
                                data-spotify_link="<?php echo htmlspecialchars($song['spotify_link']); ?>"
                                data-apple_link="<?php echo htmlspecialchars($song['apple_link']); ?>"
                                data-soundcloud_link="<?php echo htmlspecialchars($song['soundcloud_link']); ?>"
                                data-youtube_link="<?php echo htmlspecialchars($song['youtube_link']); ?>"
                                data-instagram_link="<?php echo htmlspecialchars($song['instagram_link']); ?>"
                                data-slug="<?php echo htmlspecialchars($song['slug']); ?>"
                                data-color="<?php echo htmlspecialchars($song['color']); ?>"
                        >Sửa</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <button type="submit" name="delete_songs" class="btn btn-danger">Xóa đã chọn</button>
    </form>
</div>

<!-- Modal để chỉnh sửa bài hát -->
<div class="modal fade" id="editSongModal" tabindex="-1" role="dialog" aria-labelledby="editSongModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSongModalLabel">Chỉnh sửa bài hát</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSongForm" action="songs.php" method="post">
                    <input type="hidden" name="edit_song_id" id="edit_song_id">
                    <div class="form-group">
                        <label for="edit_title">Tiêu đề:</label>
                        <input type="text" class="form-control" name="edit_title" id="edit_title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_spotify_link">Spotify:</label>
                        <input type="text" class="form-control" name="edit_spotify_link" id="edit_spotify_link">
                    </div>
                    <div class="form-group">
                        <label for="edit_apple_link">Apple Music:</label>
                        <input type="text" class="form-control" name="edit_apple_link" id="edit_apple_link">
                    </div>
                    <div class="form-group">
                        <label for="edit_soundcloud_link">SoundCloud:</label>
                        <input type="text" class="form-control" name="edit_soundcloud_link" id="edit_soundcloud_link">
                    </div>
                    <div class="form-group">
                        <label for="edit_youtube_link">YouTube Music:</label>
                        <input type="text" class="form-control" name="edit_youtube_link" id="edit_youtube_link">
                    </div>
                    <div class="form-group">
                        <label for="edit_instagram_link">Instagram:</label>
                        <input type="text" class="form-control" name="edit_instagram_link" id="edit_instagram_link">
                    </div>
                    <div class="form-group">
                        <label for="edit_slug">Slug:</label>
                        <input type="text" class="form-control" name="edit_slug" id="edit_slug">
                    </div>
                    <div class="form-group">
                        <label for="edit_color">Màu sắc:</label>
                        <input type="color" class="form-control" name="edit_color" id="edit_color">
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#editSongModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Nút kích hoạt modal
        var id = button.data('id');
        var title = button.data('title');
        var spotify_link = button.data('spotify_link');
        var apple_link = button.data('apple_link');
        var soundcloud_link = button.data('soundcloud_link');
        var youtube_link = button.data('youtube_link');
        var instagram_link = button.data('instagram_link');
        var slug = button.data('slug');
        var color = button.data('color');

        var modal = $(this);
        modal.find('#edit_song_id').val(id);
        modal.find('#edit_title').val(title);
        modal.find('#edit_spotify_link').val(spotify_link);
        modal.find('#edit_apple_link').val(apple_link);
        modal.find('#edit_soundcloud_link').val(soundcloud_link);
        modal.find('#edit_youtube_link').val(youtube_link);
        modal.find('#edit_instagram_link').val(instagram_link);
        modal.find('#edit_slug').val(slug);
        modal.find('#edit_color').val(color);
    });
</script>
</body>
</html>

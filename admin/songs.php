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
                $image_path = '../uploads/' . $row['image'];

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
    $facebook_link = $_POST['facebook_link']; // Thêm facebook_link
    $tiktok_link = $_POST['tiktok_link']; // Thêm tiktok_link
    $zalo_link = $_POST['zalo_link']; // Thêm zalo_link
    $slug = $_POST['slug'];
    $color = $_POST['color'];

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name']; // Lấy tên file hình ảnh
        $target_dir = "../uploads/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($image);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $sql = "INSERT INTO songs (title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, facebook_link, tiktok_link, zalo_link, image, slug, color)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssss", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $facebook_link, $tiktok_link, $zalo_link, $image, $slug, $color);

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
    $edit_facebook_link = $_POST['edit_facebook_link']; // Thêm facebook_link
    $edit_tiktok_link = $_POST['edit_tiktok_link']; // Thêm tiktok_link
    $edit_zalo_link = $_POST['edit_zalo_link']; // Thêm zalo_link
    $edit_slug = $_POST['edit_slug'];
    $edit_color = $_POST['edit_color'];

    // Kiểm tra nếu có ảnh mới được tải lên
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
        $new_image = $_FILES['edit_image']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($new_image);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_extensions)) {
            // Di chuyển ảnh mới vào thư mục ../uploads
            if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target_file)) {
                // Lấy ảnh cũ từ database
                $sql_image = "SELECT image FROM songs WHERE id = ?";
                $stmt_image = $conn->prepare($sql_image);
                $stmt_image->bind_param("i", $edit_song_id);
                $stmt_image->execute();
                $result_image = $stmt_image->get_result();

                if ($result_image->num_rows > 0) {
                    $row = $result_image->fetch_assoc();
                    $old_image_path = '../uploads/' . $row['image'];

                    // Xóa ảnh cũ nếu tồn tại
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }

                // Cập nhật bài hát với ảnh mới
                $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, facebook_link=?, tiktok_link=?, zalo_link=?, slug=?, color=?, image=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssssi", $edit_title, $edit_spotify_link, $edit_apple_link, $edit_soundcloud_link, $edit_youtube_link, $edit_instagram_link, $edit_facebook_link, $edit_tiktok_link, $edit_zalo_link, $edit_slug, $edit_color, $new_image, $edit_song_id);

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
        $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, facebook_link=?, tiktok_link=?, zalo_link=?, slug=?, color=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssi", $edit_title, $edit_spotify_link, $edit_apple_link, $edit_soundcloud_link, $edit_youtube_link, $edit_instagram_link, $edit_facebook_link, $edit_tiktok_link, $edit_zalo_link, $edit_slug, $edit_color, $edit_song_id);

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mt-5 bg-white p-5 rounded shadow">
    <div class="d-flex justify-content-between mb-4">
        <form method="post" enctype="multipart/form-data" class="flex flex-wrap">
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" name="title" class="form-control" placeholder="Tiêu đề">
                </div>
                <div class="col">
                    <input type="text" name="slug" class="form-control" placeholder="Slug">
                </div>
            </div>
            <div class="form-row mb-2">
                <div class="col">
                    <input type="color" name="color" class="form-control" title="Chọn màu">
                </div>
                <div class="col">
                    <input type="text" name="spotify_link" class="form-control" placeholder="Link Spotify">
                </div>
            </div>
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" name="apple_link" class="form-control" placeholder="Link Apple">
                </div>
                <div class="col">
                    <input type="text" name="soundcloud_link" class="form-control" placeholder="Link SoundCloud">
                </div>
            </div>
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" name="youtube_link" class="form-control" placeholder="Link YouTube">
                </div>
                <div class="col">
                    <input type="text" name="instagram_link" class="form-control" placeholder="Link Instagram">
                </div>
            </div>
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" name="facebook_link" class="form-control" placeholder="Link Facebook">
                </div>
                <div class="col">
                    <input type="text" name="tiktok_link" class="form-control" placeholder="Link TikTok">
                </div>
            </div>
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" name="zalo_link" class="form-control" placeholder="Link Zalo">
                </div>
                <div class="col">
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            <button type="submit" name="new_page" class="btn btn-primary mb-2">Thêm bài hát</button>
        </form>
    </div>
    <h1 class="text-2xl font-bold">Danh sách bài hát</h1>
    <form method="post" class="mt-4">
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th><input type="checkbox" id="select_all"></th>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Slug</th>
                    <th>Màu</th>
                    <th>Link Spotify</th>
                    <th>Link Apple</th>
                    <th>Link SoundCloud</th>
                    <th>Link YouTube</th>
                    <th>Link Instagram</th>
                    <th>Link Facebook</th>
                    <th>Link TikTok</th>
                    <th>Link Zalo</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_songs[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo $row['id']; ?></td>
                        <td><a href="../<?php echo $row['slug']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $row['title']; ?></a></td>
                        <td><?php echo $row['slug']; ?></td>
                        <td style="background-color: <?php echo $row['color']; ?>;"><?php echo $row['color']; ?></td>
                        <td><?php echo $row['spotify_link']; ?></td>
                        <td><?php echo $row['apple_link']; ?></td>
                        <td><?php echo $row['soundcloud_link']; ?></td>
                        <td><?php echo $row['youtube_link']; ?></td>
                        <td><?php echo $row['instagram_link']; ?></td>
                        <td><?php echo $row['facebook_link']; ?></td>
                        <td><?php echo $row['tiktok_link']; ?></td>
                        <td><?php echo $row['zalo_link']; ?></td>
                        <td><img src="../uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>" width="100" class="rounded"></td>
                        <td>
                            <button type="button" class="btn btn-info edit-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo $row['title']; ?>" data-spotify="<?php echo $row['spotify_link']; ?>" data-apple="<?php echo $row['apple_link']; ?>" data-soundcloud="<?php echo $row['soundcloud_link']; ?>" data-youtube="<?php echo $row['youtube_link']; ?>" data-instagram="<?php echo $row['instagram_link']; ?>" data-facebook="<?php echo $row['facebook_link']; ?>" data-tiktok="<?php echo $row['tiktok_link']; ?>" data-zalo="<?php echo $row['zalo_link']; ?>" data-slug="<?php echo $row['slug']; ?>" data-color="<?php echo $row['color']; ?>">Sửa</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit" name="delete_songs" class="btn btn-danger">Xóa bài hát đã chọn</button>
    </form>
</div>

<!-- Modal chỉnh sửa bài hát -->
<div class="modal" id="editSongModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Chỉnh sửa bài hát</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_song_id" id="edit_song_id">
                    <div class="form-group">
                        <label for="edit_title">Tiêu đề:</label>
                        <input type="text" name="edit_title" id="edit_title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_slug">Slug:</label>
                        <input type="text" name="edit_slug" id="edit_slug" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_color">Màu:</label>
                        <input type="color" name="edit_color" id="edit_color" class="form-control" title="Chọn màu">
                    </div>
                    <div class="form-group">
                        <label for="edit_spotify_link">Link Spotify:</label>
                        <input type="text" name="edit_spotify_link" id="edit_spotify_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_apple_link">Link Apple:</label>
                        <input type="text" name="edit_apple_link" id="edit_apple_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_soundcloud_link">Link SoundCloud:</label>
                        <input type="text" name="edit_soundcloud_link" id="edit_soundcloud_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_youtube_link">Link YouTube:</label>
                        <input type="text" name="edit_youtube_link" id="edit_youtube_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_instagram_link">Link Instagram:</label>
                        <input type="text" name="edit_instagram_link" id="edit_instagram_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_facebook_link">Link Facebook:</label>
                        <input type="text" name="edit_facebook_link" id="edit_facebook_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_tiktok_link">Link TikTok:</label>
                        <input type="text" name="edit_tiktok_link" id="edit_tiktok_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_zalo_link">Link Zalo:</label>
                        <input type="text" name="edit_zalo_link" id="edit_zalo_link" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_image">Hình ảnh:</label>
                        <input type="file" name="edit_image" id="edit_image" class="form-control">
                    </div>
                    <button type="submit" name="edit_song" class="btn btn-success">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        $('.edit-btn').on('click', function () {
            const songId = $(this).data('id');
            const title = $(this).data('title');
            const slug = $(this).data('slug');
            const color = $(this).data('color');
            const spotifyLink = $(this).data('spotify');
            const appleLink = $(this).data('apple');
            const soundcloudLink = $(this).data('soundcloud');
            const youtubeLink = $(this).data('youtube');
            const instagramLink = $(this).data('instagram');
            const facebookLink = $(this).data('facebook');
            const tiktokLink = $(this).data('tiktok');
            const zaloLink = $(this).data('zalo');

            $('#edit_song_id').val(songId);
            $('#edit_title').val(title);
            $('#edit_slug').val(slug);
            $('#edit_color').val(color);
            $('#edit_spotify_link').val(spotifyLink);
            $('#edit_apple_link').val(appleLink);
            $('#edit_soundcloud_link').val(soundcloudLink);
            $('#edit_youtube_link').val(youtubeLink);
            $('#edit_instagram_link').val(instagramLink);
            $('#edit_facebook_link').val(facebookLink);
            $('#edit_tiktok_link').val(tiktokLink);
            $('#edit_zalo_link').val(zaloLink);

            $('#editSongModal').modal('show');
        });
    });
</script>
</body>
</html>

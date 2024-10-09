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

// Kiểm tra nếu có ID bài hát
if (!isset($_GET['id'])) {
    echo "Không tìm thấy bài hát!";
    exit();
}

$id = $_GET['id'];

// Lấy thông tin bài hát từ database
$sql = "SELECT * FROM songs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Không tìm thấy bài hát!";
    exit();
}

$song = $result->fetch_assoc();

// Xử lý sửa bài hát
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $spotify_link = $_POST['spotify_link'];
    $apple_link = $_POST['apple_link'];
    $soundcloud_link = $_POST['soundcloud_link'];
    $youtube_link = $_POST['youtube_link'];
    $instagram_link = $_POST['instagram_link'];
    $slug = $_POST['slug'];
    $color = $_POST['color']; // Lấy giá trị màu sắc

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name']; // Lấy tên file hình ảnh
        $target_dir = "uploads/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($image);

        // Di chuyển tệp đã tải lên vào thư mục chỉ định
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Cập nhật thông tin bài hát
            $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, image=?, slug=?, color=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $image, $slug, $color, $id);
        }
    } else {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        $image = $song['image'];
        $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, slug=?, color=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $slug, $color, $id);
    }

    if ($stmt->execute()) {
        echo "Bài hát đã được cập nhật!";
        header("Location: songs.php");
        exit();
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa bài hát</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Sửa thông tin bài hát</h2>
        <form action="fix.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Tiêu đề:</label>
                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="spotify_link">Spotify:</label>
                <input type="text" class="form-control" name="spotify_link" value="<?php echo htmlspecialchars($song['spotify_link']); ?>">
            </div>
            <div class="form-group">
                <label for="apple_link">Apple Music:</label>
                <input type="text" class="form-control" name="apple_link" value="<?php echo htmlspecialchars($song['apple_link']); ?>">
            </div>
            <div class="form-group">
                <label for="soundcloud_link">SoundCloud:</label>
                <input type="text" class="form-control" name="soundcloud_link" value="<?php echo htmlspecialchars($song['soundcloud_link']); ?>">
            </div>
            <div class="form-group">
                <label for="youtube_link">YouTube Music:</label>
                <input type="text" class="form-control" name="youtube_link" value="<?php echo htmlspecialchars($song['youtube_link']); ?>">
            </div>
            <div class="form-group">
                <label for="instagram_link">Instagram:</label>
                <input type="text" class="form-control" name="instagram_link" value="<?php echo htmlspecialchars($song['instagram_link']); ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL hậu tố):</label>
                <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($song['slug']); ?>" required>
            </div>
            <div class="form-group">
                <label for="color">Màu sắc:</label>
                <input type="color" class="form-control" name="color" value="<?php echo htmlspecialchars($song['color']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image">Chọn ảnh mới để tải lên (nếu có):</label>
                <input type="file" class="form-control-file" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật bài hát</button>
            <a href="songs.php" class="btn btn-secondary">Quay về trang trước</a>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

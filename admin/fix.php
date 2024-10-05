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

    // Kiểm tra xem tệp hình ảnh có được tải lên hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name']; // Lấy tên file hình ảnh
        $target_dir = "uploads/"; // Đường dẫn đến thư mục lưu ảnh
        $target_file = $target_dir . basename($image);

        // Di chuyển tệp đã tải lên vào thư mục chỉ định
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Cập nhật thông tin bài hát
            $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, image=?, slug=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $image, $slug, $id);
        }
    } else {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        $image = $song['image'];
        $sql = "UPDATE songs SET title=?, spotify_link=?, apple_link=?, soundcloud_link=?, youtube_link=?, instagram_link=?, slug=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $title, $spotify_link, $apple_link, $soundcloud_link, $youtube_link, $instagram_link, $slug, $id);
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Sửa thông tin bài hát</h2>
    <form action="fix.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
        <label for="title">Tiêu đề:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($song['title']); ?>" required><br>
        
        <label for="spotify_link">Spotify:</label><br>
        <input type="text" name="spotify_link" value="<?php echo htmlspecialchars($song['spotify_link']); ?>"><br>
        
        <label for="apple_link">Apple Music:</label><br>
        <input type="text" name="apple_link" value="<?php echo htmlspecialchars($song['apple_link']); ?>"><br>
        
        <label for="soundcloud_link">SoundCloud:</label><br>
        <input type="text" name="soundcloud_link" value="<?php echo htmlspecialchars($song['soundcloud_link']); ?>"><br>
        
        <label for="youtube_link">YouTube Music:</label><br>
        <input type="text" name="youtube_link" value="<?php echo htmlspecialchars($song['youtube_link']); ?>"><br>
        
        <label for="instagram_link">Instagram:</label><br>
        <input type="text" name="instagram_link" value="<?php echo htmlspecialchars($song['instagram_link']); ?>"><br>
        
        <label for="slug">Slug (URL hậu tố):</label><br>
        <input type="text" name="slug" value="<?php echo htmlspecialchars($song['slug']); ?>" required><br>
        
        <label for="image">Chọn ảnh mới để tải lên (nếu có):</label><br>
        <input type="file" name="image"><br>
        
        <input type="submit" value="Cập nhật bài hát">
    </form>
</body>
</html>

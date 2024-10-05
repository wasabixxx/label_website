<?php
session_start();
include 'admin/connect_db.php';

// Kiểm tra slug trong URL
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];

    // Truy vấn bài hát dựa trên slug
    $sql = "SELECT * FROM songs WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Lấy dữ liệu từ bản ghi
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $spotify_link = $row['spotify_link'];
        $apple_link = $row['apple_link'];
        $soundcloud_link = $row['soundcloud_link'];
        $youtube_link = $row['youtube_link'];
        $instagram_link = $row['instagram_link'];
        $image = $row['image'];
        
        // Đặt đường dẫn cho uploads
        $uploads_path = 'admin/uploads/'; // Sử dụng uploads cho slug
    } else {
        echo "Không tìm thấy bài hát!";
        exit();
    }
} else {
    // Nếu không có slug, lấy dữ liệu mặc định
    $sql = "SELECT * FROM homepage LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $spotify_link = $row['spotify_link'];
        $apple_link = $row['apple_link'];
        $soundcloud_link = $row['soundcloud_link'];
        $youtube_link = $row['youtube_link'];
        $instagram_link = $row['instagram_link'];
        $image = $row['image'];

        // Đặt đường dẫn cho uploads1
        $uploads_path = 'admin/uploads1/'; // Sử dụng uploads1 cho không có slug
    } else {
        echo "Chưa có dữ liệu trang chủ!";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Brothers Still Alive</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/btn.css">
    <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon">
</head>
<body style="background-image: url('<?php echo $uploads_path . htmlspecialchars($image); ?>'); background-size: cover; background-position: center; backdrop-filter: blur(5px);">
    <div id="header">
        <h3>BROTHERS STILL ALIVE</h3>
    </div>

    <div id="container">
        <img id="artistImage" src="<?php echo $uploads_path . htmlspecialchars($image); ?>" alt="Artist Image">
        <div id="songTitle"><?php echo htmlspecialchars($title); ?></div>
        <div id="platformLinks">
            <?php if (!empty($spotify_link)): ?>
                <a href="<?php echo htmlspecialchars($spotify_link); ?>" target="_blank" class="bttn-jelly bttn-md bttn-default">Spotify</a>
            <?php endif; ?>
            <?php if (!empty($apple_link)): ?>
                <a href="<?php echo htmlspecialchars($apple_link); ?>" target="_blank" class="bttn-jelly bttn-md bttn-default">Apple Music</a>
            <?php endif; ?>
            <?php if (!empty($soundcloud_link)): ?>
                <a href="<?php echo htmlspecialchars($soundcloud_link); ?>" target="_blank" class="bttn-jelly bttn-md bttn-default">SoundCloud</a>
            <?php endif; ?>
            <?php if (!empty($youtube_link)): ?>
                <a href="<?php echo htmlspecialchars($youtube_link); ?>" target="_blank" class="bttn-jelly bttn-md bttn-default">YouTube Music</a>
            <?php endif; ?>
            <?php if (!empty($instagram_link)): ?>
                <a href="<?php echo htmlspecialchars($instagram_link); ?>" target="_blank" class="bttn-jelly bttn-md bttn-default">Instagram</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="container">
        <div>
            Copyright &copy; <script>document.write(new Date().getFullYear())</script> 
            <a href="https://www.facebook.com/BSAdagang" target="_blank">Brothers Still Alive</a>
        </div>
    </footer>
</body>
</html>

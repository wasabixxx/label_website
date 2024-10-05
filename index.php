<?php
// Include file kết nối cơ sở dữ liệu
include 'connect_db.php';

// Truy vấn dữ liệu trang chủ từ cơ sở dữ liệu
$sql = "SELECT * FROM homepage LIMIT 1";
$result = $conn->query($sql);

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
} else {
    echo "Chưa có dữ liệu trang chủ!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Brothers Still Alive</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/btn.css">
    <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon">
</head>
<body>
    <div id="header">
        <h3>BROTHERS STILL SLIVE</h3>
    </div>
    <div id="container">
        <img id="artistImage" src="<?php echo $image; ?>" alt="Artist Image">
        <div id="songTitle"><?php echo $title; ?></div>
        <div id="platformLinks">
            <?php if($spotify_link): ?>
                <a href="<?php echo $spotify_link; ?>" target="_blank">Spotify</a>
            <?php endif; ?>
            <?php if($apple_link): ?>
                <a href="<?php echo $apple_link; ?>" target="_blank">Apple Music</a>
            <?php endif; ?>
            <?php if($soundcloud_link): ?>
                <a href="<?php echo $soundcloud_link; ?>" target="_blank">SoundCloud</a>
            <?php endif; ?>
            <?php if($youtube_link): ?>
                <a href="<?php echo $youtube_link; ?>" target="_blank">YouTube Music</a>
            <?php endif; ?>
            <?php if($instagram_link): ?>
                <a href="<?php echo $instagram_link; ?>" target="_blank">Instagram</a>
            <?php endif; ?>
        </div>
    </div>
    <footer class="container">
        <div>
            Copyright &copy;
            <script>document.write(new Date().getFullYear())</script>
            Brothers Still Alive
        </div>
    </footer>
</body>
</html>

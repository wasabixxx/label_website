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
        $color = $row['color']; // Lấy màu từ bảng songs
        
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
        $color = $row['color']; // Lấy màu từ bảng homepage

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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/btn.css">
    <link rel="shortcut icon" href="img/123.ico" type="image/x-icon">
    <style>
        .background-blur {
            background-image: url("<?php echo $uploads_path . htmlspecialchars($image); ?>");
            background-size: cover;
            filter: blur(10px);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
        }
        body{
            padding: 30px;
        }
    </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen relative">
    <div class="background-blur"></div>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden w-80 relative z-10 main">
        <div class="relative">
            <img src="<?php echo $uploads_path . htmlspecialchars($image); ?>" class="w-full h-60 object-cover" alt="Artist Image">
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                <h1 class="text-white text-xl font-bold"><?php echo htmlspecialchars($title); ?></h1>
            </div>
        </div>
        <div class="p-4">
            <?php if (!empty($spotify_link)): ?>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="https://storage.googleapis.com/a1aa/image/z71NkDHMVMYRKBjeOLnEl9fbSew8dgw8gG3AIij0ldeZwJUOB.jpg" class="w-6 h-6 mr-2 .bttn-pill.bttn-md" alt="Spotify logo">
                    <span class="text-lg font-medium">Spotify</span>
                </div>
                <a href="<?php echo htmlspecialchars($spotify_link); ?>" class="bg-gray-200 text-gray-700 rounded-full px-4 py-1" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($apple_link)): ?>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="https://storage.googleapis.com/a1aa/image/4VNxWoUQZsbVEZKAbiKlcP9QFscFMpzNgukuLf0op3FEOhyJA.jpg" class="w-6 h-6 mr-2 .bttn-pill.bttn-md" alt="Apple Music logo">
                    <span class="text-lg font-medium">Apple Music</span>
                </div>
                <a href="<?php echo htmlspecialchars($apple_link); ?>" class="bg-gray-200 text-gray-700 rounded-full px-4 py-1" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($soundcloud_link)): ?>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="https://st-aug.edu/wp-content/uploads/2021/09/soundcloud-logo-soundcloud-icon-transparent-png-1.png" class="w-6 h-6 mr-2 .bttn-pill.bttn-md" alt="SoundCloud logo">
                    <span class="text-lg font-medium">SoundCloud</span>
                </div>
                <a href="<?php echo htmlspecialchars($soundcloud_link); ?>" class="bg-gray-200 text-gray-700 rounded-full px-4 py-1" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($youtube_link)): ?>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="https://storage.googleapis.com/a1aa/image/geWMoZrl5KT3SK8gvAlblMGYQ1hbHw10o3YPTnIYiIXBOhyJA.jpg" class="w-6 h-6 mr-2.bttn-pill.bttn-md" alt="YouTube Music logo">
                    <span class="text-lg font-medium">YouTube Music</span>
                </div>
                <a href="<?php echo htmlspecialchars($youtube_link); ?>" class="bg-gray-200 text-gray-700 rounded-full px-4 py-1" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($instagram_link)): ?>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" class="w-6 h-6 mr-2 .bttn-pill.bttn-md" alt="Instagram logo">
                    <span class="text-lg font-medium">Instagram</span>
                </div>
                <a href="<?php echo htmlspecialchars($instagram_link); ?>" class="bg-gray-200 text-gray-700 rounded-full px-4 py-1" target="_blank">View</a>
            </div>
            <?php endif; ?>
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <footer>
        <div class="text-center py-4">
            <p class="text-gray-500">POWERED BY B$A</p>
            <p>Copyright &copy; <script>document.write(new Date().getFullYear())</script> <a href="https://www.facebook.com/BSAdagang" target="_blank">Brothers Still Alive</a></p>
        </div>
    </footer>
    </div>
</body>
</html>

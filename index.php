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
        $facebook_link = $row['facebook_link'];
        $tiktok_link = $row['tiktok_link'];
        $zalo_link = $row['zalo_link'];
        
        // Đặt đường dẫn cho uploads
        $uploads_path = 'uploads/'; // Sử dụng uploads cho slug
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
        $facebook_link = $row['facebook_link'];
        $tiktok_link = $row['tiktok_link'];
        $zalo_link = $row['zalo_link'];

        // Đặt đường dẫn cho uploads1
        $uploads_path = 'uploads1/'; // Sử dụng uploads1 cho không có slug
    } else {
        echo "Chưa có dữ liệu trang chủ!";
        exit();
    }
}
?>  
<!-- 
    ,---,.   .--.--.      ,---,        
  ,'  .'  \ /  /    '.   '  .' \       
,---.' .' ||  :  /`. /  /  ;    '.     
|   |  |: |;  |  |--`  :  :       \    
:   :  :  /|  :  ;_    :  |   /\   \   
:   |    ;  \  \    `. |  :  ' ;.   :  
|   :     \  `----.   \|  |  ;/  \   \ 
|   |   . |  __ \  \  |'  :  | \  \ ,' 
'   :  '; | /  /`--'  /|  |  '  '--'   
|   |  | ; '--'.     / |  :  :         
|   :   /    `--'---'  |  | ,'         
|   | ,'               `--''           
`----'       
                                    -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="facebook-domain-verification" content="42wjac8c0k25qu1kysxglupna7bos6" />
    <!-- Thẻ meta description để mô tả nội dung của trang web -->
    <meta name="description" content="BSA - Brothers Still Alive là một tổ chức âm nhạc với chuyên môn chính là làm nhạc và phát hành sản phẩm chuyên về hiphop">
    <!-- Thẻ meta keywords cho từ khóa liên quan đến nội dung (ít còn sử dụng nhưng có thể thêm) -->
    <meta name="keywords" content="music, bsa, rap, hiphop">

    <!-- Thẻ meta robots điều khiển cách mà công cụ tìm kiếm lập chỉ mục trang web -->
    <meta name="robots" content="index, follow">

    <!-- Thẻ canonical để chỉ định URL chính thống của trang web, tránh nội dung trùng lặp -->
    <link rel="canonical" href="https://www.brothersstillalive.asia/" />

    <!-- Thẻ meta cho Open Graph (OG) - chia sẻ trên mạng xã hội -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta property="og:description" content="BSA - <?php echo htmlspecialchars($title); ?>">
    <meta property="og:image" content="https://www.brothersstillalive.asia/<?php echo $uploads_path . htmlspecialchars($image); ?>">
    <meta property="og:url" content="https://www.brothersstillalive.asia">
    <meta property="og:type" content="website">

    <!-- Thẻ meta để chỉ định tác giả của trang -->
    <meta name="author" content="Nguyễn Ngọc Khánh">

    <!-- Thẻ meta để xác định chủ đề nội dung của trang web (optional) -->
    <meta name="subject" content="Music fan link website">

    <!-- Thẻ meta cho xác định khu vực địa lý hoặc ngôn ngữ (optional) -->
    <meta name="geo.region" content="VN">
    <meta name="language" content="Vietnamese">

    <title><?php echo htmlspecialchars($title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <link rel="shortcut icon" href="img/123.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
    * {
    user-select: none;
    -webkit-user-select: none;/*IE,etc*/
    -moz-user-select: none; /*Mozzila Firefox*/
    -ms-user-select: none;
    }
    body {
        padding: 30px;
        background-color: #121212;
    }
    .background-blur {
        background-image: url("<?php echo $uploads_path . htmlspecialchars($image); ?>");
        background-color: black;
        background-size: cover;
        filter: blur(15px) brightness(0.8);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
    }
    .main-header h1,p {
        color: <?php echo htmlspecialchars($color); ?>;
        max-width: 24rem;
    }
    
    footer { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 20px;
        color: rgb(0, 0, 0);
        font-size: medium;
    }

    footer div a {
        text-decoration: none;
        font-weight: 400;
    }

    footer div {
        text-align: center;
    }

    footer div a:hover {
        font-weight: 600;
        color: #880000;
    }


    /* Media query cho màn hình nhỏ hơn 768px (thiết bị di động) */
    /* @media (max-width: 768px) { */
        /* .background-blur { */
            /* background-image: none; Loại bỏ background-image */ */
            /* filter: blur(15px); */
            /* background-color: black; */
        /* } */
        /* footer a,p{
            color: black;
        }
        footer div a {
            text-decoration: none;
            color: black;
            font-weight: 500;
        }
        .main-header h1 {
            color: #121212;
        }
        .main-header h1,p {
            color: #121212 !important;
        } */

    /* } */
</style>
</head>
<body class="relative flex flex-col items-center justify-center min-h-screen" id="body-disable-rc">
    <div class="background-blur"></div>
    <div class="flex flex-col items-center relative z-10 mb-4 mt-14 text-center main-header">
        <img 
            alt="Album cover" 
            class="w-56 h-full rounded-lg mb-1 drop-shadow-xl" 
            src="<?php echo $uploads_path . htmlspecialchars($image); ?>" 
        />
        <h1 class="text-2xl font-bold break-words">
            <?php echo nl2br(htmlspecialchars($title)); ?>
        </h1>

        <p class="text-500 mt-2 mb-0 cms" style="color: <?php echo htmlspecialchars($color); ?>; ">
            <?php 
            if (!empty($slug)) {
             echo "Choose music service";
            }else {
             echo "Contact us:";
            }
            ?>
        </p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 max-w-md w-full relative z-10 my-5">
        <div class="space-y-4">
            <?php if (!empty($spotify_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="Spotify logo" class="w-6 h-6" height="24" src="https://storage.googleapis.com/pr-newsroom-wp/1/2023/05/Spotify_Primary_Logo_RGB_Green.png" width="24"/>
                <span class="text-lg font-medium">Spotify</span>
                <a href="<?php echo htmlspecialchars($spotify_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($apple_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="Apple Music logo" class="w-6 h-6" height="24" src="https://upload.wikimedia.org/wikipedia/commons/5/5f/Apple_Music_icon.svg" width="24"/>
                <span class="text-lg font-medium">Apple Music</span>
                <a href="<?php echo htmlspecialchars($apple_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($soundcloud_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="SoundCloud logo" class="w-6 h-6" height="24" src="https://e7.pngegg.com/pngimages/41/942/png-clipart-soundcloud-logo-square-soundcloud-icon-icons-logos-emojis-social-media-icons.png" width="24"/>
                <span class="text-lg font-medium">SoundCloud</span>
                <a href="<?php echo htmlspecialchars($soundcloud_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($youtube_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="YouTube Music logo" class="w-6 h-6" height="24" src="https://upload.wikimedia.org/wikipedia/commons/f/fc/YouTube_play_button_square_%282013-2017%29.svg" width="24"/>
                <span class="text-lg font-medium">YouTube</span>
                <a href="<?php echo htmlspecialchars($youtube_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">Play</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($instagram_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="Instagram logo" class="w-6 h-6" height="24" src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" width="24"/>
                <span class="text-lg font-medium">Instagram</span>
                <a href="<?php echo htmlspecialchars($instagram_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">View</a>
            </div>
            <?php endif; ?>

            <!-- ADDDDDD -->
            <?php if (!empty($facebook_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="Facebook logo" class="w-6 h-6" height="24" src="https://upload.wikimedia.org/wikipedia/commons/b/b9/2023_Facebook_icon.svg" width="24"/>
                <span class="text-lg font-medium">Facebook</span>
                <a href="<?php echo htmlspecialchars($facebook_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">View</a>
            </div>
            <?php endif; ?>
            <?php if (!empty($tiktok_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="Tiktok logo" class="w-6 h-6" height="24" src="https://cdn.pixabay.com/photo/2021/06/15/12/28/tiktok-6338429_1280.png" width="24"/>
                <span class="text-lg font-medium">Tiktok</span>
                <a href="<?php echo htmlspecialchars($tiktok_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">View</a>
            </div>
            <?php if (!empty($zalo_link)): ?>
            <div class="flex items-center justify-between bg-gray-100 p-4 rounded-lg">
                <img alt="zalo logo" class="w-6 h-6" height="24" src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-Zalo-App-Rec.png" width="24"/>
                <span class="text-lg font-medium">Zalo</span>
                <a href="<?php echo htmlspecialchars($Zalo_link); ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg" target="_blank">View</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- <p class="text-gray-500 text-center mt-6 text-sm relative z-10">
        You have accepted the use of cookies for this service.
        <a class="text-blue-500" href="#">Click here</a>
        to manage your permissions.
        <br/>
        This page may contain affiliate links.
    </p> -->
    <footer>
        <div class="text-center py-4">
            <p class="text-gray-500">POWERED BY B$A</p>
            <p class="text-white-500">Copyright &copy; <script>document.write(new Date().getFullYear())</script> <a href="https://www.facebook.com/BSAdagang" target="_blank">Brothers Still Alive</a></p>
        </div>
    </footer>

    <script>
        const element = document.getElementById("body-disable-rc"); 
        element.addEventListener("contextmenu", (event) => { event.preventDefault(); });
    </script>
</body>
</html>

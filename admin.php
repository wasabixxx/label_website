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

// Biến để lưu tên tệp hình ảnh
$image_name = '';
$home_image_name = '';

// Xử lý tải ảnh lên cho trang nhạc
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra xem tệp có phải là ảnh hay không
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "Tệp không phải là ảnh.";
        $uploadOk = 0;
    }

    // Kiểm tra kích thước tệp
    if ($_FILES["image"]["size"] > 500000) {
        echo "Xin lỗi, tệp của bạn quá lớn.";
        $uploadOk = 0;
    }

    // Cho phép các định dạng tệp cụ thể
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Xin lỗi, chỉ cho phép tải lên các định dạng JPG, JPEG, PNG và GIF.";
        $uploadOk = 0;
    }

    // Kiểm tra xem $uploadOk có được đặt thành 0 hay không
    if ($uploadOk == 0) {
        echo "Xin lỗi, tệp của bạn không được tải lên.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_name = htmlspecialchars(basename($_FILES["image"]["name"]));
            echo "Tệp ". $image_name . " đã được tải lên.";
        } else {
            echo "Xin lỗi, đã xảy ra lỗi khi tải lên tệp.";
        }
    }
}

// Xử lý tải ảnh lên cho trang chủ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_home_image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["home_image"]["name"]);
    $uploadOk = 1;
    $home_imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra xem tệp có phải là ảnh hay không
    $check = getimagesize($_FILES["home_image"]["tmp_name"]);
    if ($check === false) {
        echo "Tệp không phải là ảnh.";
        $uploadOk = 0;
    }

    // Kiểm tra kích thước tệp
    if ($_FILES["home_image"]["size"] > 500000) {
        echo "Xin lỗi, tệp của bạn quá lớn.";
        $uploadOk = 0;
    }

    // Cho phép các định dạng tệp cụ thể
    if ($home_imageFileType != "jpg" && $home_imageFileType != "png" && $home_imageFileType != "jpeg" && $home_imageFileType != "gif") {
        echo "Xin lỗi, chỉ cho phép tải lên các định dạng JPG, JPEG, PNG và GIF.";
        $uploadOk = 0;
    }

    // Kiểm tra xem $uploadOk có được đặt thành 0 hay không
    if ($uploadOk == 0) {
        echo "Xin lỗi, tệp của bạn không được tải lên.";
    } else {
        if (move_uploaded_file($_FILES["home_image"]["tmp_name"], $target_file)) {
            $home_image_name = htmlspecialchars(basename($_FILES["home_image"]["name"]));
            echo "Tệp ". $home_image_name . " đã được tải lên cho trang chủ.";
        } else {
            echo "Xin lỗi, đã xảy ra lỗi khi tải lên tệp cho trang chủ.";
        }
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
    
    // Sử dụng tên tệp đã tải lên
    $image = $image_name;
    $slug = $_POST['slug'];

    // Thêm bản ghi vào database
    $sql = "INSERT INTO songs (title, spotify_link, apple_link, soundcloud_link, youtube_link, instagram_link, image, slug)
            VALUES ('$title', '$spotify_link', '$apple_link', '$soundcloud_link', '$youtube_link', '$instagram_link', '$image', '$slug')";

    if ($conn->query($sql) === TRUE) {
        echo "Trang nhạc mới đã được tạo!";
    } else {
        echo "Lỗi: " . $sql . "<br>" . $conn->error;
    }
}

// Cập nhật trang chủ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    $home_title = $_POST['home_title'];
    $home_spotify = $_POST['home_spotify'];
    $home_apple = $_POST['home_apple'];
    $home_soundcloud = $_POST['home_soundcloud'];
    $home_youtube = $_POST['home_youtube'];
    $home_instagram = $_POST['home_instagram'];
    
    // Sử dụng tên tệp đã tải lên cho trang chủ
    $home_image = $home_image_name;

    // Cập nhật dữ liệu trang chủ
    $sql = "UPDATE homepage SET 
                title='$home_title', 
                spotify_link='$home_spotify', 
                apple_link='$home_apple', 
                soundcloud_link='$home_soundcloud', 
                youtube_link='$home_youtube', 
                instagram_link='$home_instagram', 
                image='$home_image' 
            WHERE id=1";

    if ($conn->query($sql) === TRUE) {
        echo "Trang chủ đã được cập nhật!";
    } else {
        echo "Lỗi: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý trang nhạc</title>
</head>
<body>
    <h2>Xin chào, <?php echo $_SESSION['username']; ?></h2>
    <a href="logout.php">Đăng xuất</a>

    <h2>Tải ảnh lên cho trang nhạc</h2>
    <form action="admin.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_image" value="1">
        <label for="image">Chọn ảnh để tải lên:</label>
        <input type="file" name="image" required><br>
        <input type="submit" value="Tải lên">
    </form>

    <h2>Thêm trang nhạc mới</h2>
    <form action="admin.php" method="post">
        <input type="hidden" name="new_page" value="1">
        <label for="title">Tiêu đề:</label><br>
        <input type="text" name="title" required><br>
        
        <label for="spotify_link">Spotify:</label><br>
        <input type="text" name="spotify_link"><br>
        
        <label for="apple_link">Apple Music:</label><br>
        <input type="text" name="apple_link"><br>
        
        <label for="soundcloud_link">SoundCloud:</label><br>
        <input type="text" name="soundcloud_link"><br>
        
        <label for="youtube_link">YouTube Music:</label><br>
        <input type="text" name="youtube_link"><br>
        
        <label for="instagram_link">Instagram:</label><br>
        <input type="text" name="instagram_link"><br>
        
        <label for="slug">Slug (URL hậu tố):</label><br>
        <input type="text" name="slug" required><br>
        
        <input type="submit" value="Tạo trang">
    </form>

    <h2>Tải ảnh lên cho trang chủ</h2>
    <form action="admin.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_home_image" value="1">
        <label for="home_image">Chọn ảnh cho trang chủ:</label>
        <input type="file" name="home_image" required><br>
        <input type="submit" value="Tải lên">
    </form>

    <h2>Cập nhật giao diện trang chủ</h2>
    <form action="admin.php" method="post">
        <input type="hidden" name="update_homepage" value="1">
        <label for="home_title">Tiêu đề trang chủ:</label><br>
        <input type="text" name="home_title" required><br>
        
        <label for="home_spotify">Spotify:</label><br>
        <input type="text" name="home_spotify"><br>
        
        <label for="home_apple">Apple Music:</label><br>
        <input type="text" name="home_apple"><br>
        
        <label for="home_soundcloud">SoundCloud:</label><br>
        <input type="text" name="home_soundcloud"><br>
        
        <label for="home_youtube">YouTube Music:</label><br>
        <input type="text" name="home_youtube"><br>
        
        <label for="home_instagram">Instagram:</label><br>
        <input type="text" name="home_instagram"><br>
        
        <input type="submit" value="Cập nhật trang chủ">
    </form>
</body>
</html>

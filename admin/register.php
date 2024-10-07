<?php
include 'connect_db.php'; // Kết nối đến cơ sở dữ liệu

// Kiểm tra nếu có cookie tồn tại
if (isset($_COOKIE['username'])) {
    $_SESSION['username'] = $_COOKIE['username']; // Đặt lại phiên từ cookie
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Băm mật khẩu trước khi lưu vào cơ sở dữ liệu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Thêm người dùng vào bảng
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        echo "Tài khoản đã được tạo thành công!";
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
    <title>Đăng Ký Tài Khoản</title>
</head>
<body>
    <h2>Đăng Ký Tài Khoản</h2>
    <form action="register.php" method="post">
        <label for="username">Tên Đăng Nhập:</label><br>
        <input type="text" name="username" required><br>
        
        <label for="password">Mật Khẩu:</label><br>
        <input type="password" name="password" required><br>
        
        <input type="submit" value="Đăng Ký">
    </form>

    <a href="../admin">Quay lại trang admin</a>
</body>
</html>

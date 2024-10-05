<?php
// Cấu hình thông tin kết nối
$servername = "localhost";  // Hoặc địa chỉ IP của máy chủ
$username = "root";         // Tên người dùng MySQL
$password = "";             // Mật khẩu MySQL (mặc định thường để trống nếu dùng XAMPP)
$dbname = "music_website";  // Tên cơ sở dữ liệu

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>

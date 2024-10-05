<?php
session_start();
include 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn người dùng từ cơ sở dữ liệu
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // So sánh mật khẩu đã băm
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;

            // Thiết lập cookie để lưu phiên đăng nhập
            if (isset($_POST['remember'])) {
                setcookie('username', $username, time() + (86400 * 30), "/"); // Cookie sẽ tồn tại trong 30 ngày
            }

            header("Location: admin.php");
            exit();
        } else {
            echo "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        echo "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
</head>
<body>
    <h2>Đăng Nhập</h2>
    <form action="login.php" method="post">
        <label for="username">Tên Đăng Nhập:</label><br>
        <input type="text" name="username" required><br>

        <label for="password">Mật Khẩu:</label><br>
        <input type="password" name="password" required><br>

        <input type="checkbox" name="remember"> Ghi nhớ đăng nhập<br>
        
        <input type="submit" value="Đăng Nhập">
    </form>
</body>
</html>

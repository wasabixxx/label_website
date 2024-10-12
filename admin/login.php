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
            $_SESSION['role'] = $row['role']; // Lưu vai trò vào session

            // Thiết lập cookie để lưu phiên đăng nhập
            if (isset($_POST['remember'])) {
                setcookie('username', $username, time() + (86400 * 30), "/"); // Cookie sẽ tồn tại trong 30 ngày
            }

            header("Location: index.php");
            exit();
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="shortcut icon" href="../img/123.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../img/123.ico" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-form {
            width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-form h2 {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <form action="login.php" method="post">
            <h2 class="text-center">Đăng Nhập</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Tên Đăng Nhập:</label>
                <input type="text" name="username" class="form-control" required="required" placeholder="Nhập tên đăng nhập">
            </div>
            <div class="form-group">
                <label for="password">Mật Khẩu:</label>
                <input type="password" name="password" class="form-control" required="required" placeholder="Nhập mật khẩu">
            </div>
            <div class="form-group form-check">
                <input type="checkbox" name="remember" class="form-check-input">
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Đăng Nhập</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Nếu bạn muốn xóa cookie cũng, hãy thiết lập cookie với thời gian hết hạn trong quá khứ
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cuối cùng, hủy session
session_destroy();

// Chuyển hướng đến trang đăng nhập
header("Location: login.php");
exit();
?>

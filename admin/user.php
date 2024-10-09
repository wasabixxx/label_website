<?php
session_start();
include 'connect_db.php'; // Kết nối đến cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập và có vai trò admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin"); // Chuyển hướng về trang admin nếu không phải là admin
    exit();
}

// Thêm người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
    $role = $_POST['role'];

    // Kiểm tra trùng tên đăng nhập
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result_check = $check_stmt->get_result();

    if ($result_check->num_rows > 0) {
        echo "<div class='alert alert-danger'>Tên đăng nhập đã tồn tại.</div>";
    } else {
        // Thực hiện truy vấn thêm người dùng vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Người dùng đã được thêm thành công.</div>";
        } else {
            echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Sửa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Thực hiện truy vấn sửa thông tin người dùng
    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $role, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Người dùng đã được cập nhật thành công.</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    // Thực hiện truy vấn xóa người dùng
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Người dùng đã được xóa thành công.</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Lấy danh sách người dùng để hiển thị
$result = $conn->query("SELECT * FROM users");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <div>
            <a href="../admin" class="btn btn-danger">Quay về trang ADMIN</a>
        </div>
    </div>
    <h2 class="mb-4">Quản lý Người dùng</h2>

    <!-- Form thêm người dùng -->
    <div class="card mb-4">
        <div class="card-header">Thêm Người dùng</div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Vai trò:</label>
                    <select name="role" class="form-control">
                        <option value="user">Người dùng</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary">Thêm Người dùng</button>
            </form>
        </div>
    </div>

    <!-- Danh sách người dùng -->
    <h3>Danh sách Người dùng</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Vai trò</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $user['id']; ?>">Sửa</button>
                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>

                        <!-- Modal Sửa người dùng -->
                        <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Sửa Người dùng</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="" method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <div class="form-group">
                                                <label for="username">Tên đăng nhập:</label>
                                                <input type="text" class="form-control" name="username" value="<?php echo $user['username']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="role">Vai trò:</label>
                                                <select name="role" class="form-control">
                                                    <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>Người dùng</option>
                                                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="edit_user" class="btn btn-primary">Cập nhật</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

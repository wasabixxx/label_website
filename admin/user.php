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
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password']; // Lấy mật khẩu nhập lại
    $role = $_POST['role'];

    // Kiểm tra trùng tên đăng nhập
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result_check = $check_stmt->get_result();

    if ($result_check->num_rows > 0) {
        echo "<div class='alert alert-danger'>Tên đăng nhập đã tồn tại.</div>";
    } else if ($password !== $retype_password) {
        echo "<div class='alert alert-danger'>Mật khẩu không khớp.</div>"; // Kiểm tra mật khẩu nhập lại
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu

        // Thực hiện truy vấn thêm người dùng vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password_hashed, $role);

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
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];

    // Kiểm tra nếu mật khẩu được nhập thì mã hóa và cập nhật
    if (!empty($password) && $password === $retype_password) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        // Cập nhật thông tin người dùng bao gồm cả mật khẩu
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $password_hashed, $role, $user_id);
    } else {
        // Chỉ cập nhật tên đăng nhập và vai trò nếu mật khẩu không được nhập
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $user_id);
    }

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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@1.9.6/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .input-group {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Quản lý Người dùng</h2>

        <!-- Form thêm người dùng -->
        <div class="card mb-4">
            <div class="card-header">Thêm Người dùng</div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <div class="form-group flex-1">
                            <label for="username">Tên đăng nhập:</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="password">Mật khẩu:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-group flex-1">
                            <label for="retype_password">Nhập lại mật khẩu:</label>
                            <input type="password" class="form-control" name="retype_password" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="role">Vai trò:</label>
                            <select name="role" class="form-control">
                                <option value="user">Người dùng</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary">Thêm Người dùng</button>
                </form>
            </div>
        </div>

        <!-- Danh sách người dùng -->
        <h3 class="mb-3">Danh sách Người dùng</h3>
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
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal<?php echo $user['id']; ?>">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>

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
                                            <div class="input-group mb-3">
                                                <div class="form-group flex-1">
                                                    <label for="username">Tên đăng nhập:</label>
                                                    <input type="text" class="form-control" name="username" value="<?php echo $user['username']; ?>" required>
                                                </div>
                                                <div class="form-group flex-1">
                                                    <label for="password">Mật khẩu mới (để trống nếu không thay đổi):</label>
                                                    <input type="password" class="form-control" name="password">
                                                </div>
                                            </div>
                                            <div class="input-group mb-3">
                                                <div class="form-group flex-1">
                                                    <label for="retype_password">Nhập lại mật khẩu mới:</label>
                                                    <input type="password" class="form-control" name="retype_password">
                                                </div>
                                                <div class="form-group flex-1">
                                                    <label for="role">Vai trò:</label>
                                                    <select name="role" class="form-control">
                                                        <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>Người dùng</option>
                                                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" name="edit_user" class="btn btn-primary">Cập nhật Người dùng</button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
include 'connect_db.php';

// Lấy thông tin hiện tại của bảng homepage (id = 1)
$sql = "SELECT * FROM homepage WHERE id = 1";
$result = $conn->query($sql);

// Kiểm tra nếu có dữ liệu trả về
if ($result->num_rows > 0) {
    $current_homepage = $result->fetch_assoc();
    
    // Trả về dữ liệu dưới dạng JSON
    echo json_encode($current_homepage);
} else {
    // Nếu không tìm thấy dữ liệu, trả về một thông báo lỗi
    echo json_encode(array("error" => "Không tìm thấy dữ liệu trang chủ"));
}
?>
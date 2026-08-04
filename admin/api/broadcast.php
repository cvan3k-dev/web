<?php
require_once '../../api/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    jsonResponse('error', 'Không có quyền truy cập');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Phương thức không được hỗ trợ');
}

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$targetType = $_POST['target_type'] ?? 'all'; // all, specific
$specificUsername = trim($_POST['specific_username'] ?? '');

if (empty($subject) || empty($message)) {
    jsonResponse('error', 'Vui lòng nhập đầy đủ tiêu đề và nội dung');
}

require_once '../../includes/mailer.php';

$sentCount = 0;
$failedCount = 0;

if ($targetType === 'specific') {
    if (empty($specificUsername)) {
        jsonResponse('error', 'Vui lòng nhập tên người dùng cụ thể');
    }
    
    $stmt = $conn->prepare("SELECT email, is_email_verified FROM users WHERE username = ?");
    $stmt->bind_param("s", $specificUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse('error', 'Không tìm thấy người dùng này');
    }
    
    $uData = $result->fetch_assoc();
    if (empty($uData['email']) || $uData['is_email_verified'] != 1) {
        jsonResponse('error', 'Người dùng này chưa xác thực email');
    }
    
    if (sendAppEmail($uData['email'], $subject, $message)) {
        $sentCount++;
    } else {
        $failedCount++;
    }
} else {
    // Send to all verified users
    $stmt = $conn->prepare("SELECT email FROM users WHERE is_email_verified = 1 AND email IS NOT NULL AND email != ''");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (sendAppEmail($row['email'], $subject, $message)) {
            $sentCount++;
        } else {
            $failedCount++;
        }
    }
}

jsonResponse('success', "Gửi hoàn tất. Thành công: $sentCount, Thất bại: $failedCount");
?>

<?php
require_once '../../admin/includes/auth.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Yêu cầu không hợp lệ');
}

$username = trim($_POST['username'] ?? '');
$duration = $_POST['duration'] ?? '';

if (empty($username)) {
    jsonResponse('error', 'Vui lòng nhập tên người dùng');
}

$valid_durations = ['1hour', '1day', '3day', '7day', '30day', 'forever'];
if (!in_array($duration, $valid_durations)) {
    jsonResponse('error', 'Thời hạn không hợp lệ');
}

// 1. Kiểm tra user có tồn tại
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    jsonResponse('error', 'Không tìm thấy người dùng này');
}

$user_id = $user['id'];

// 2. Tính toán ngày hết hạn
switch ($duration) {
    case '1hour':
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        break;
    case '1day':
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
        break;
    case '3day':
        $expires_at = date('Y-m-d H:i:s', strtotime('+3 days'));
        break;
    case '7day':
        $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
        break;
    case '30day':
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        break;
    case 'forever':
    default:
        $expires_at = '9999-12-31 23:59:59';
        break;
}

// 3. Hàm tạo key code duy nhất
function generateUniqueKey($conn) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($chars) - 1;
    do {
        $random = '';
        for ($i = 0; $i < 10; $i++) {
            $random .= $chars[random_int(0, $max)];
        }
        $key_code = 'NTK' . $random;
        $stmt = $conn->prepare("SELECT id FROM user_keys WHERE key_code = ?");
        $stmt->bind_param("s", $key_code);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
    } while ($exists);
    return $key_code;
}

$key_code = generateUniqueKey($conn);

// 4. Lưu key
$ins = $conn->prepare("INSERT INTO user_keys (user_id, key_code, expires_at) VALUES (?, ?, ?)");
$ins->bind_param("iss", $user_id, $key_code, $expires_at);

if ($ins->execute()) {
    logAdminAction("Tạo VIP Key $key_code ($duration) cho user $username (ID: $user_id)");
    jsonResponse('success', 'Tạo Key thành công', [
        'key' => $key_code,
        'expires_at' => $expires_at
    ]);
} else {
    jsonResponse('error', 'Lỗi hệ thống không thể tạo key');
}
?>

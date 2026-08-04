<?php

require_once 'config.php';

// Bảo vệ: không cho truy cập API trực tiếp từ bên ngoài
requireApiAccess();

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Bắt buộc phải đăng nhập
if (!isset($_SESSION['user_id'])) {
    jsonResponse('error', 'Chưa đăng nhập');
}

$action  = $_POST['action'] ?? '';
$user_id = (int)$_SESSION['user_id'];

// Rate limit tổng thể cho tool API
rateLimitApi('tool_' . $action, 60, 60);

/*
|--------------------------------------------------------------------------
| JSON RESPONSE SAFE
|--------------------------------------------------------------------------
*/
if (!function_exists('safeJson')) {
    function safeJson($status, $message, $data = []) {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| XÁC MINH SESSION USER TOÀN VẸN (chống giả mạo session)
|--------------------------------------------------------------------------
*/
function verifyUserSession() {
    global $conn;
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        // Session không hợp lệ - huỷ ngay
        session_unset();
        session_destroy();
        jsonResponse('error', 'Phiên đăng nhập không hợp lệ');
    }
}
verifyUserSession();

/*
|--------------------------------------------------------------------------
| GENERATE UNIQUE KEY
|--------------------------------------------------------------------------
*/
function generateUniqueKey($conn) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max   = strlen($chars) - 1;
    do {
        $random = '';
        for ($i = 0; $i < 10; $i++) {
            $random .= $chars[random_int(0, $max)];
        }
        $key_code = 'NTK' . $random;
        $stmt     = $conn->prepare("SELECT id FROM user_keys WHERE key_code = ?");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("s", $key_code);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
    } while ($exists);
    return $key_code;
}

try {

    /*
    |--------------------------------------------------------------------------
    | CHECK KEY — chống bypass bằng cách xác minh key thuộc đúng user
    |--------------------------------------------------------------------------
    */
    if ($action === 'check_key') {

        checkCSRF();
        rateLimitApi('check_key', 10, 30); // Tối đa 10 lần/30 giây

        $key_code    = trim($_POST['key_code'] ?? '');
        $fingerprint = trim($_POST['fingerprint'] ?? '');

        if (!$key_code)    safeJson('error', 'Vui lòng nhập Key');
        if (!$fingerprint) safeJson('error', 'Không xác định được thiết bị');

        // Làm sạch đầu vào
        $key_code    = preg_replace('/[^A-Z0-9]/', '', strtoupper($key_code));
        $fingerprint = substr($fingerprint, 0, 128);

        // Bắt buộc key phải thuộc đúng user đang đăng nhập (chống bypass key của người khác)
        $stmt = $conn->prepare("
            SELECT id, key_code, expires_at, fingerprint, user_id
            FROM user_keys
            WHERE user_id = ?
              AND key_code = ?
              AND expires_at > NOW()
            LIMIT 1
        ");
        if (!$stmt) throw new Exception($conn->error);

        $stmt->bind_param("is", $user_id, $key_code);
        $stmt->execute();
        $key = $stmt->get_result()->fetch_assoc();

        if (!$key) {
            safeJson('error', 'Key không tồn tại, đã hết hạn hoặc không thuộc tài khoản này');
        }

        // Ràng buộc fingerprint thiết bị
        if (empty($key['fingerprint'])) {
            $upd = $conn->prepare("UPDATE user_keys SET fingerprint = ? WHERE id = ? AND user_id = ?");
            if (!$upd) throw new Exception($conn->error);
            $upd->bind_param("sii", $fingerprint, $key['id'], $user_id);
            $upd->execute();
        } else {
            if ($key['fingerprint'] !== $fingerprint) {
                safeJson('error', 'Key đã được kích hoạt trên thiết bị khác. Liên hệ hỗ trợ nếu cần đổi thiết bị.');
            }
        }

        safeJson('success', 'Kích hoạt thành công', [
            'key_code'   => $key['key_code'],
            'expires_at' => $key['expires_at']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BUY KEY — chống buff tiền bằng SELECT FOR UPDATE + transaction
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'buy_key') {

        checkCSRF();
        rateLimitApi('buy_key', 5, 60); // Tối đa 5 lần mua/phút

        $plan          = $_POST['plan'] ?? '';
        
        $settingsFile = __DIR__ . '/../admin/settings.json';
        $siteSettings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
        $key_plans = $siteSettings['key_plans'] ?? [
            '1day'    => ['price'=>40000, 'old_price'=>0],
            '3day'    => ['price'=>60000, 'old_price'=>0],
            '7day'    => ['price'=>99000, 'old_price'=>0],
            '30day'   => ['price'=>175000, 'old_price'=>0],
            'forever' => ['price'=>400000, 'old_price'=>0]
        ];

        $daysMap = [
            '1day' => 1,
            '3day' => 3,
            '7day' => 7,
            '30day' => 30,
            'forever' => 9999
        ];

        if (!isset($key_plans[$plan])) {
            safeJson('error', 'Gói không hợp lệ');
        }

        $price = $key_plans[$plan]['price'];
        $days = $daysMap[$plan];



        // Bắt đầu transaction
        if (!$conn->begin_transaction()) throw new Exception('Không thể bắt đầu giao dịch');

        try {
            // SELECT FOR UPDATE — khoá dòng, ngăn buff tiền đồng thời (race condition)
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) throw new Exception('Không tìm thấy tài khoản');

            // Xác minh số dư thực tế từ DB (không tin client)
            if ((int)$user['balance'] < $price) {
                throw new Exception('Số dư không đủ. Vui lòng nạp thêm tiền.');
            }

            $newBalance = (int)$user['balance'] - $price;

            // Chỉ cập nhật nếu newBalance hợp lệ (chống underflow)
            if ($newBalance < 0) throw new Exception('Lỗi tính toán số dư');

            $upd = $conn->prepare("UPDATE users SET balance = ? WHERE id = ? AND balance >= ?");
            if (!$upd) throw new Exception($conn->error);
            $upd->bind_param("iii", $newBalance, $user_id, $price);
            if (!$upd->execute() || $upd->affected_rows === 0) {
                throw new Exception('Không thể cập nhật số dư. Vui lòng thử lại.');
            }



            // Tạo key mới
            $key_code  = generateUniqueKey($conn);
            $expires_at = ($days >= 9999)
                ? '9999-12-31 23:59:59'
                : date('Y-m-d H:i:s', strtotime("+{$days} days"));

            $ins = $conn->prepare("INSERT INTO user_keys (user_id, key_code, expires_at) VALUES (?, ?, ?)");
            if (!$ins) throw new Exception($conn->error);
            $ins->bind_param("iss", $user_id, $key_code, $expires_at);
            if (!$ins->execute()) throw new Exception($ins->error);

            // Ghi log giao dịch
            $order_id = '#ORD' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $desc     = "Mua key {$plan} - {$key_code}";
            $log      = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description, order_id, status) VALUES (?, 'buy_key', ?, ?, ?, 'completed')");
            if (!$log) throw new Exception($conn->error);
            $log->bind_param("iiss", $user_id, $price, $desc, $order_id);
            $log->execute();

            $conn->commit();

            // Gửi email thông báo nếu đã xác thực email
            $stmt_email = $conn->prepare("SELECT email, is_email_verified, username FROM users WHERE id = ?");
            $stmt_email->bind_param("i", $user_id);
            $stmt_email->execute();
            $uData = $stmt_email->get_result()->fetch_assoc();
            
            if ($uData && $uData['is_email_verified'] == 1 && !empty($uData['email'])) {
                require_once __DIR__ . '/../includes/mailer.php';
                $subject = "Thông báo: Mua mã (Key) thành công";
                $body = '
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="display: inline-block; background: #dcfce7; color: #10b981; padding: 12px; border-radius: 50%; margin-bottom: 15px;">
                            <img src="https://cdn-icons-png.flaticon.com/512/3064/3064195.png" width="40" height="40" alt="Key" style="display: block;">
                        </div>
                        <h2 style="color: #1e293b; margin: 0 0 10px 0; font-size: 22px;">Mua Key Thành Công!</h2>
                    </div>
                    
                    <p style="color: #334155; font-size: 16px;">Xin chào <strong>' . htmlspecialchars($uData['username']) . '</strong>,</p>
                    <p style="color: #334155; font-size: 16px;">Cảm ơn bạn đã tin tưởng. Giao dịch mua mã sử dụng (Key) của bạn đã được xử lý thành công. Dưới đây là thông tin chi tiết:</p>
                    
                    <div style="background: #ffffff; border: 2px dashed #10b981; border-radius: 12px; padding: 25px; text-align: center; margin: 25px 0;">
                        <p style="color: #64748b; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">MÃ KEY CỦA BẠN</p>
                        <div style="font-size: 28px; font-weight: 800; letter-spacing: 2px; color: #10b981; margin: 15px 0; background: #f0fdf4; padding: 10px; border-radius: 8px;">
                            ' . $key_code . '
                        </div>
                    </div>
                    
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <tr>
                            <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 40%;">Gói đăng ký:</td>
                            <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #1e293b; font-weight: 600; text-align: right;">' . strtoupper($plan) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;">Thời hạn đến:</td>
                            <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #ef4444; font-weight: 600; text-align: right;">' . $expires_at . '</td>
                        </tr>
                    </table>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="https://toolgameai.site/my-keys" style="display: inline-block; padding: 12px 25px; background: #10b981; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">Xem Lịch Sử Key</a>
                    </div>
                ';
                sendAppEmail($uData['email'], $subject, $body);
            }

            safeJson('success', 'Mua key thành công', [
                'key'        => $key_code,
                'expires_at' => $expires_at,
                'balance'    => $newBalance
            ]);

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KEY HISTORY
    |--------------------------------------------------------------------------
    */
    elseif ($action === 'key_history') {

        $stmt = $conn->prepare("
            SELECT key_code, expires_at, created_at,
                   (SELECT amount FROM transactions
                    WHERE description LIKE CONCAT('%', key_code, '%')
                      AND user_id = user_keys.user_id
                    ORDER BY id DESC LIMIT 1) AS amount
            FROM user_keys
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 20
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        safeJson('success', 'Lấy lịch sử thành công', $rows);
    }



    /*
    |--------------------------------------------------------------------------
    | ACTION KHÔNG HỢP LỆ
    |--------------------------------------------------------------------------
    */
    else {
        safeJson('error', 'Action không hợp lệ');
    }

} catch (Throwable $e) {
    // Ghi log lỗi vào file, không expose ra ngoài
    @file_put_contents(
        __DIR__ . '/error_log.txt',
        '[' . date('Y-m-d H:i:s') . '] [tool.php] ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
    safeJson('error', 'Lỗi hệ thống. Vui lòng thử lại sau.');
}
?>

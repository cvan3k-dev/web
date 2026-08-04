<?php
require_once __DIR__.'/../includes/auth.php';
requireAdmin();
header('Content-Type: application/json');

$token = trim($_POST['token'] ?? '');
$chat_id = trim($_POST['chat_id'] ?? '');

if (!$token || !$chat_id) {
    echo json_encode(['status'=>'error','message'=>'Thiếu token hoặc chat_id']);
    exit;
}

$text = "🔔 *Test thông báo từ Admin*\n✅ Bot Telegram đã kết nối thành công!\n🌐 Website: toolgameai.site";

$url = "https://api.telegram.org/bot{$token}/sendMessage";
$payload = json_encode(['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'Markdown']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['status'=>'error','message'=>'cURL error: '.$error]);
    exit;
}

$data = json_decode($response, true);
if ($data && $data['ok']) {
    echo json_encode(['status'=>'success','message'=>'✅ Đã gửi tin nhắn test thành công!']);
} else {
    $desc = $data['description'] ?? 'Unknown error';
    echo json_encode(['status'=>'error','message'=>'❌ Lỗi Telegram: '.$desc]);
}

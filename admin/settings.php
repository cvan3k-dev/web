<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$isSuper = ($currentAdmin['role'] === 'superadmin');
if (!$isSuper) { die('<script>location.href="index.php";</script>'); }

$settingsFile = __DIR__.'/settings.json';
$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $old = json_decode(file_get_contents($settingsFile), true) ?: [];

    // Deposit promo: convert days to timestamp expiry
    $deposit_promo_days = max(0, intval($_POST['deposit_promo_days'] ?? 0));
    $deposit_promo_percent = max(0, min(200, intval($_POST['deposit_promo_percent'] ?? 0)));
    if ($deposit_promo_percent > 0 && $deposit_promo_days > 0) {
        $deposit_promo_expiry = time() + $deposit_promo_days * 86400;
    } else {
        $deposit_promo_expiry = $old['deposit_promo_expiry'] ?? 0;
        if ($deposit_promo_percent == 0) $deposit_promo_expiry = 0;
    }

    // Key Pricing
    $key_plans = $old['key_plans'] ?? [];
    if (isset($_POST['key_price']) && is_array($_POST['key_price'])) {
        foreach ($_POST['key_price'] as $plan => $price) {
            $key_plans[$plan]['price'] = max(0, intval($price));
            $key_plans[$plan]['old_price'] = max(0, intval($_POST['key_old_price'][$plan] ?? 0));
        }
    }

    $data = [
        'site_name'              => trim($_POST['site_name'] ?? 'ToolGameAI'),
        'min_deposit'            => intval($_POST['min_deposit'] ?? 10000),
        'max_deposit'            => intval($_POST['max_deposit'] ?? 30000000),
        'chat_enabled'           => false,
        'maintenance_mode'       => isset($_POST['maintenance_mode']),
        'maintenance_message'    => trim($_POST['maintenance_message'] ?? 'Hệ thống đang bảo trì!'),
        'telegram_bot_token'     => trim($_POST['telegram_bot_token'] ?? ''),
        'telegram_chat_id'       => trim($_POST['telegram_chat_id'] ?? ''),
        'deposit_promo_percent'  => $deposit_promo_percent,
        'deposit_promo_days'     => $deposit_promo_days,
        'deposit_promo_expiry'   => $deposit_promo_expiry,
        'key_plans'              => $key_plans,
    ];
    file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    logAdminAction("Cập nhật cài đặt hệ thống");
    $msg = 'Đã lưu cài đặt thành công!';
}

$settings = json_decode(file_get_contents($settingsFile), true) ?: [];

// Compute remaining days for promo/discount
$promoExpiry = $settings['deposit_promo_expiry'] ?? 0;
$promoRemaining = $promoExpiry > time() ? ceil(($promoExpiry - time()) / 86400) : 0;
$key_plans = $settings['key_plans'] ?? [
    '1day'    => ['price'=>40000, 'old_price'=>0],
    '3day'    => ['price'=>60000, 'old_price'=>0],
    '7day'    => ['price'=>99000, 'old_price'=>0],
    '30day'   => ['price'=>175000, 'old_price'=>0],
    'forever' => ['price'=>400000, 'old_price'=>0]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>
<div class="sidebar" id="adminSidebar"><?php include 'includes/sidebar.php'; ?></div>
<div class="admin-header">
    <div class="header-left"><?php include 'includes/header.php'; ?></div>
    <div class="header-right">
        <div class="header-admin-info">
            <div class="header-avatar"><?= strtoupper(substr($currentAdmin['username'],0,1)) ?></div>
            <div>
                <div class="header-admin-name"><?= htmlspecialchars($currentAdmin['username']) ?></div>
                <div class="header-admin-role"><?= $currentAdmin['role'] ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Thoát</a>
    </div>
</div>
<div class="main-content">
    <?php if ($msg): ?>
    <div id="settingsAlert" class="alert alert-<?= $msgType ?>">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <form method="post" id="settingsForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- === CỘT TRÁI === -->
        <div>
            <!-- General Settings -->
            <div class="data-card" style="margin-bottom:20px;">
                <div class="data-card-header">
                    <div class="data-card-title"><i class="fas fa-sliders-h"></i> Cài đặt chung</div>
                </div>
                <div class="data-card-body">
                    <div class="form-group">
                        <label class="form-label">Tên trang web</label>
                        <input name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name']??'ToolGameAI') ?>">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Nạp tối thiểu (đ)</label>
                            <input type="number" name="min_deposit" class="form-control" value="<?= $settings['min_deposit']??10000 ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nạp tối đa (đ)</label>
                            <input type="number" name="max_deposit" class="form-control" value="<?= $settings['max_deposit']??30000000 ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telegram Bot -->
            <div class="data-card" style="margin-bottom:20px;">
                <div class="data-card-header">
                    <div class="data-card-title"><i class="fab fa-telegram"></i> Telegram Bot Thông báo</div>
                </div>
                <div class="data-card-body">
                    <div class="alert alert-info" style="margin-bottom:14px;font-size:12px;">
                        <i class="fas fa-info-circle"></i>
                        Khi nạp tiền thành công, bot sẽ gửi thông báo vào group/channel. Thêm bot vào group & lấy Chat ID.
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bot Token</label>
                        <input type="text" name="telegram_bot_token" class="form-control" value="<?= htmlspecialchars($settings['telegram_bot_token']??'') ?>" placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                        <div class="form-hint">Lấy từ @BotFather trên Telegram</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Chat ID (Group/Channel)</label>
                        <input type="text" name="telegram_chat_id" class="form-control" value="<?= htmlspecialchars($settings['telegram_chat_id']??'') ?>" placeholder="-100123456789">
                        <div class="form-hint">ID âm = group/supergroup, dương = user</div>
                    </div>
                    <?php if (!empty($settings['telegram_bot_token']) && !empty($settings['telegram_chat_id'])): ?>
                    <button type="button" class="btn btn-sm btn-outline" onclick="testTelegram()">
                        <i class="fab fa-telegram"></i> Test gửi thông báo
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- === CỘT PHẢI === -->
        <div>
            <!-- Deposit Promotion -->
            <div class="data-card" style="margin-bottom:20px;">
                <div class="data-card-header">
                    <div class="data-card-title"><i class="fas fa-gift"></i> Khuyến mãi Nạp Tiền</div>
                    <?php if ($promoRemaining > 0): ?>
                    <span class="badge badge-success"><i class="fas fa-clock"></i> Còn <?= $promoRemaining ?> ngày</span>
                    <?php elseif (($settings['deposit_promo_percent']??0) > 0): ?>
                    <span class="badge badge-danger">Đã hết hạn</span>
                    <?php else: ?>
                    <span class="badge badge-warning">Chưa bật</span>
                    <?php endif; ?>
                </div>
                <div class="data-card-body">
                    <div class="alert alert-info" style="margin-bottom:14px;font-size:12px;">
                        <i class="fas fa-info-circle"></i>
                        Khi bật, user nạp tiền sẽ được cộng thêm % vào số dư. Ví dụ: nạp 100k + 20% = nhận 120k.
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label class="form-label">% Bonus thêm</label>
                            <input type="number" name="deposit_promo_percent" class="form-control" value="<?= $settings['deposit_promo_percent']??0 ?>" min="0" max="200" placeholder="0 = tắt">
                            <div class="form-hint">0 = Không khuyến mãi</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kéo dài (số ngày)</label>
                            <input type="number" name="deposit_promo_days" class="form-control" value="<?= $settings['deposit_promo_days']??0 ?>" min="0" placeholder="0 = không kéo dài">
                            <div class="form-hint">Nhập &gt;0 để reset thời hạn</div>
                        </div>
                    </div>
                    <?php if ($promoRemaining > 0 && ($settings['deposit_promo_percent']??0) > 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Đang khuyến mãi <b>+<?= $settings['deposit_promo_percent'] ?>%</b> nạp tiền — hết hạn <?= date('d/m/Y H:i', $settings['deposit_promo_expiry']) ?>
                    </div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-danger" onclick="cancelPromo()">
                        <i class="fas fa-times"></i> Dừng khuyến mãi ngay
                    </button>
                </div>
            </div>

            <!-- Key Pricing -->
            <div class="data-card" style="margin-bottom:20px;">
                <div class="data-card-header">
                    <div class="data-card-title"><i class="fas fa-tags"></i> Định Giá Các Gói Key</div>
                </div>
                <div class="data-card-body">
                    <div class="alert alert-info" style="margin-bottom:14px;font-size:12px;">
                        <i class="fas fa-info-circle"></i>
                        Nhập Giá Gốc (sẽ hiển thị gạch ngang) và Giá Bán Thực Tế. Nếu không muốn gạch ngang, để Giá Gốc = 0.
                    </div>
                    <?php 
                    $planLabels = [
                        '1day' => '1 Ngày',
                        '3day' => '3 Ngày',
                        '7day' => '7 Ngày',
                        '30day' => '30 Ngày',
                        'forever' => 'Vĩnh Viễn'
                    ];
                    foreach ($planLabels as $pKey => $pLabel): 
                        $pPrice = $key_plans[$pKey]['price'] ?? 0;
                        $pOld = $key_plans[$pKey]['old_price'] ?? 0;
                    ?>
                    <div style="margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #334155;">
                        <label class="form-label" style="color:#38bdf8; font-size:14px; margin-bottom:8px;">Gói <?= $pLabel ?></label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" style="font-size:11px;">Giá Gốc (đ)</label>
                                <input type="number" name="key_old_price[<?= $pKey ?>]" class="form-control" value="<?= $pOld ?>" min="0">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" style="font-size:11px; color:#10b981;">Giá Bán Thực Tế (đ)</label>
                                <input type="number" name="key_price[<?= $pKey ?>]" class="form-control" value="<?= $pPrice ?>" min="0" required>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title"><i class="fas fa-tools"></i> Chế độ bảo trì</div>
                    <?php if (!empty($settings['maintenance_mode'])): ?>
                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> ĐANG BẢO TRÌ</span>
                    <?php else: ?>
                    <span class="badge badge-success">Online</span>
                    <?php endif; ?>
                </div>
                <div class="data-card-body">
                    <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <label class="toggle-switch"><input type="checkbox" name="maintenance_mode" id="maintenanceToggle" <?= !empty($settings['maintenance_mode'])?'checked':'' ?>><span class="toggle-slider"></span></label>
                        <span style="font-size:14px;font-weight:600;">Bật chế độ bảo trì hệ thống</span>
                    </div>
                    <?php if (!empty($settings['maintenance_mode'])): ?>
                    <div class="alert alert-danger" style="margin-bottom:12px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <b>CẢNH BÁO:</b> Người dùng đang bị chuyển đến trang bảo trì!
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label">Thông báo bảo trì</label>
                        <textarea name="maintenance_message" class="form-control" rows="3"><?= htmlspecialchars($settings['maintenance_message']??'Hệ thống đang bảo trì!') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- end grid -->

        <div style="margin-top:20px;text-align:right;">
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:15px;">
                <i class="fas fa-save"></i> Lưu tất cả cài đặt
            </button>
        </div>
    </form>
</div>

<div id="toast-container"></div>

<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function showToast(msg, type='success') {
    const tc = document.getElementById('toast-container');
    const t = document.createElement('div');
    const icons = {success:'check-circle',error:'times-circle',info:'info-circle',warning:'exclamation-triangle'};
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;
    tc.appendChild(t);
    setTimeout(()=>{ t.style.animation='toastOut 0.3s forwards'; setTimeout(()=>t.remove(),300); }, 3500);
}

function cancelPromo() {
    if (!confirm('Dừng khuyến mãi nạp tiền ngay bây giờ?')) return;
    document.querySelector('[name="deposit_promo_percent"]').value = 0;
    document.querySelector('[name="deposit_promo_days"]').value = 0;
    document.getElementById('settingsForm').submit();
}

function testTelegram() {
    const token = document.querySelector('[name="telegram_bot_token"]').value.trim();
    const chatId = document.querySelector('[name="telegram_chat_id"]').value.trim();
    if (!token || !chatId) { showToast('Vui lòng nhập Token và Chat ID trước!', 'warning'); return; }
    fetch('ajax/test_telegram.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `token=${encodeURIComponent(token)}&chat_id=${encodeURIComponent(chatId)}`
    }).then(r=>r.json()).then(d=>{
        showToast(d.message || 'Đã gửi!', d.status==='success'?'success':'error');
    }).catch(()=>showToast('Lỗi kết nối!','error'));
}
<?php if ($msg): ?>
setTimeout(()=>{
    const a = document.getElementById('settingsAlert');
    if(a) { a.style.opacity='0'; a.style.transition='opacity 0.5s'; setTimeout(()=>a.remove(),500); }
}, 4000);
<?php endif; ?>
</script>
</body>
</html>

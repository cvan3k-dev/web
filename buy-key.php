<?php
require_once __DIR__ . '/api/config.php';
$isLoggedIn = isLoggedIn();
$user = null;
if ($isLoggedIn) {
    $user = getUserById($_SESSION['user_id']);
}
if (!$isLoggedIn) {
    header('Location: index.php');
    exit;
}

if (!isset($settings) || !is_array($settings)) {
    $settingsFile = __DIR__ . '/admin/settings.json';
    $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
}

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
    <?php
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    if (strpos($scriptPath, 'buy-key.php') !== false) {
        $baseDir = preg_replace('/buy-key\.php$/', '', $scriptPath);
    } else {
        $baseDir = '/';
    }
    $baseDir = str_replace('\\', '/', $baseDir);
    if (substr($baseDir, -1) !== '/') {
        $baseDir .= '/';
    }
    ?>
    <base href="<?= htmlspecialchars($baseDir) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mua Key VIP – TOOLGAMEAI.SITE</title>
    <meta name="description" content="Mua Key VIP để sử dụng Tool Game AI tại TOOLGAMEAI.SITE.">
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/new_ui.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .page-wrap { max-width: 560px; margin: 0 auto; width: 100%; padding: 16px 16px 80px; }

        .back-bar { display: flex; align-items: center; gap: 12px; padding: 12px 0 4px; margin-bottom: 8px; }
        .back-btn {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 8px 16px;
            color: #94a3b8; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: 0.2s; text-decoration: none;
        }
        .back-btn:hover { background: rgba(34,197,94,0.1); border-color: rgba(34,197,94,0.3); color: #22c55e; }

        .bk-page-title {
            font-family: 'Oswald', sans-serif; font-size: 26px; font-weight: 700;
            color: #22c55e; text-align: center; margin-bottom: 6px;
            text-shadow: 0 0 20px rgba(34,197,94,0.4);
        }
        .bk-page-sub { text-align: center; font-size: 12px; color: #94a3b8; margin-bottom: 20px; }

        /* Balance card */
        .balance-card {
            background: rgba(15,23,42,0.8); border: 1px solid rgba(34,197,94,0.2);
            border-radius: 20px; padding: 16px 20px; margin-bottom: 16px;
            display: flex; align-items: center; justify-content: space-between;
            backdrop-filter: blur(12px);
        }
        .balance-label { font-size: 12px; color: rgba(255,255,255,0.5); }
        .balance-amount { font-size: 20px; font-weight: 800; color: #fcd34d; display: flex; align-items: center; gap: 6px; }

        /* Tab buttons */
        .bk-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
        .bk-tab {
            flex: 1; padding: 10px; border-radius: 14px;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
            color: #94a3b8; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: 0.2s; text-transform: uppercase;
        }
        .bk-tab.active { background: rgba(34,197,94,0.12); border-color: rgba(34,197,94,0.4); color: #22c55e; }

        /* Plan cards */
        .plan-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
        .plan-card {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 16px; border-radius: 20px;
            background: rgba(255,255,255,0.02); border: 1.5px solid rgba(255,255,255,0.08);
            cursor: pointer; transition: all 0.2s;
        }
        .plan-card:hover { background: rgba(34,197,94,0.06); border-color: rgba(34,197,94,0.2); }
        .plan-card.selected { background: rgba(34,197,94,0.1); border-color: #22c55e; box-shadow: 0 0 20px rgba(34,197,94,0.15); }
        .plan-card img { width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0; }
        .plan-info { flex: 1; }
        .plan-name { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
        .plan-desc { font-size: 11px; color: rgba(255,255,255,0.5); }
        .plan-price { font-size: 20px; font-weight: 800; white-space: nowrap; }
        .plan-check { width: 20px; height: 20px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .plan-card.selected .plan-check { background: #22c55e; border-color: #22c55e; }
        .plan-check i { font-size: 10px; color: #fff; display: none; }
        .plan-card.selected .plan-check i { display: block; }

        /* Confirm button */
        .confirm-btn {
            width: 100%; padding: 16px; border-radius: 18px;
            background: linear-gradient(135deg, #065f46, #10b981, #065f46);
            border: none; color: #fff;
            font-family: 'Oswald', sans-serif; font-weight: 900;
            letter-spacing: 2px; font-size: 17px;
            cursor: pointer; transition: 0.2s;
            box-shadow: 0 8px 24px rgba(16,185,129,0.4);
        }
        .confirm-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(16,185,129,0.5); }
        .confirm-btn:active { transform: translateY(0); }
        .confirm-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* History pane */
        #buy-history-pane { display: none; }
        .hist-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 12px;
        }
        .hist-item:last-child { border-bottom: none; }
        .hist-plan { font-weight: 700; }
        .hist-key { font-family: monospace; font-size: 11px; color: #22c55e; word-break: break-all; }
        .hist-expire { color: rgba(255,255,255,0.5); font-size: 10px; margin-top: 2px; }
        .hist-status-ok { color: #22c55e; font-weight: 700; }
        .hist-status-exp { color: #ef4444; font-weight: 700; }

        /* Container card */
        .bk-card {
            background: rgba(15,23,42,0.8); border: 1px solid rgba(34,197,94,0.2);
            border-radius: 24px; overflow: hidden; margin-bottom: 16px;
            backdrop-filter: blur(12px);
        }
        .bk-card-body { padding: 20px; }
    </style>
</head>
<body data-csrf="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <!-- PRELOADER -->
    <div id="preloader">
        <div class="preloader-inner">
            <div class="tk-loader-wrapper">
                <svg class="tk-loader" viewBox="0 0 500 350" width="160" height="112" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="circuit-grad" x1="0%" y1="100%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#84cc16" />
                            <stop offset="30%" stop-color="#22c55e" />
                            <stop offset="70%" stop-color="#06b6d4" />
                            <stop offset="100%" stop-color="#0284c7" />
                        </linearGradient>
                        <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="6" result="blur" />
                            <feMerge>
                                <feMergeNode in="blur" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>
                        <filter id="glow-intense" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="10" result="blur1" />
                            <feGaussianBlur stdDeviation="4" result="blur2" />
                            <feMerge>
                                <feMergeNode in="blur1" />
                                <feMergeNode in="blur2" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>
                    </defs>
                    <g class="circuit-grid" opacity="0.12">
                        <path d="M 0,50 L 500,50 M 0,100 L 500,100 M 0,150 L 500,150 M 0,200 L 500,200 M 0,250 L 500,250 M 0,300 L 500,300" stroke="#0ea5e9" stroke-width="1" stroke-dasharray="5 5" fill="none" />
                        <path d="M 50,0 L 50,350 M 100,0 L 100,350 M 150,0 L 150,350 M 200,0 L 200,350 M 250,0 L 250,350 M 300,0 L 300,350 M 350,0 L 350,350 M 400,0 L 400,350 M 450,0 L 450,350" stroke="#0ea5e9" stroke-width="1" stroke-dasharray="5 5" fill="none" />
                    </g>
                    <g class="circuit-back-tracks" stroke="rgba(255,255,255,0.06)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none">
                        <path d="M 150,44 L 60,134 L 60,160 L 120,160 L 120,286 L 150,316 L 180,286 L 180,160 L 240,160 L 240,134 Z" />
                        <path d="M 150,61 L 72,139 L 72,150 L 130,150 L 130,276 L 150,296 L 170,276 L 170,150 L 228,150 L 228,139 Z" />
                        <path d="M 150,78 L 88,140 L 140,140 L 140,266 L 150,276 L 160,266 L 160,140 L 212,140 Z" />
                        <path d="M 150,95 L 150,230" />
                        <path d="M 300,70 L 300,50 L 336,50 L 336,70 M 336,290 L 336,310 L 300,310 L 300,290" />
                        <path d="M 300,70 L 300,290" />
                        <path d="M 312,70 L 312,60 L 324,60 L 324,70 M 324,290 L 324,300 L 312,300 L 312,290" />
                        <path d="M 312,70 L 312,290" />
                        <path d="M 324,70 L 324,290" />
                        <path d="M 336,70 L 336,290" />
                        <path d="M 336,136 L 430,42" />
                        <path d="M 336,156 L 415,77" />
                        <path d="M 336,176 L 400,112" />
                        <path d="M 336,224 L 430,318" />
                        <path d="M 336,204 L 415,283" />
                        <path d="M 336,184 L 400,248" />
                    </g>
                    <g class="circuit-main-tracks" stroke="url(#circuit-grad)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#glow)">
                        <path d="M 150,44 L 60,134 L 60,160 L 120,160 L 120,286 L 150,316 L 180,286 L 180,160 L 240,160 L 240,134 Z" />
                        <path d="M 150,61 L 72,139 L 72,150 L 130,150 L 130,276 L 150,296 L 170,276 L 170,150 L 228,150 L 228,139 Z" />
                        <path d="M 150,78 L 88,140 L 140,140 L 140,266 L 150,276 L 160,266 L 160,140 L 212,140 Z" />
                        <path d="M 150,95 L 150,230" />
                        <path d="M 300,70 L 300,50 L 336,50 L 336,70 M 336,290 L 336,310 L 300,310 L 300,290" />
                        <path d="M 300,70 L 300,290" />
                        <path d="M 312,70 L 312,60 L 324,60 L 324,70 M 324,290 L 324,300 L 312,300 L 312,290" />
                        <path d="M 312,70 L 312,290" />
                        <path d="M 324,70 L 324,290" />
                        <path d="M 336,70 L 336,290" />
                        <path d="M 336,136 L 430,42" />
                        <path d="M 336,156 L 415,77" />
                        <path d="M 336,176 L 400,112" />
                        <path d="M 336,224 L 430,318" />
                        <path d="M 336,204 L 415,283" />
                        <path d="M 336,184 L 400,248" />
                    </g>
                    <g class="circuit-pulses" stroke="#ffffff" stroke-width="5" stroke-linecap="round" fill="none" filter="url(#glow-intense)">
                        <path class="pulse-fast" pathLength="100" d="M 150,44 L 60,134 L 60,160 L 120,160 L 120,286 L 150,316 L 180,286 L 180,160 L 240,160 L 240,134 Z" />
                        <path class="pulse-slow" pathLength="100" d="M 150,61 L 72,139 L 72,150 L 130,150 L 130,276 L 150,296 L 170,276 L 170,150 L 228,150 L 228,139 Z" />
                        <path class="pulse-medium" pathLength="100" d="M 150,78 L 88,140 L 140,140 L 140,266 L 150,276 L 160,266 L 160,140 L 212,140 Z" />
                        <path class="pulse-fast-reverse" pathLength="100" d="M 150,230 L 150,95" />
                        <path class="pulse-medium" pathLength="100" d="M 300,70 L 300,290" />
                        <path class="pulse-slow" pathLength="100" d="M 312,290 L 312,70" />
                        <path class="pulse-fast" pathLength="100" d="M 324,70 L 324,290" />
                        <path class="pulse-medium" pathLength="100" d="M 336,290 L 336,70" />
                        <path class="pulse-fast" pathLength="100" d="M 336,136 L 430,42" />
                        <path class="pulse-slow" pathLength="100" d="M 336,156 L 415,77" />
                        <path class="pulse-medium" pathLength="100" d="M 336,176 L 400,112" />
                        <path class="pulse-fast" pathLength="100" d="M 336,224 L 430,318" />
                        <path class="pulse-medium" pathLength="100" d="M 336,204 L 415,283" />
                        <path class="pulse-slow" pathLength="100" d="M 336,184 L 400,248" />
                    </g>
                    <g class="circuit-nodes" fill="url(#circuit-grad)" stroke="#ffffff" stroke-width="2" filter="url(#glow)">
                        <circle cx="150" cy="44" r="5" />
                        <circle cx="150" cy="95" r="4.5" />
                        <circle cx="150" cy="230" r="4.5" />
                        <circle cx="150" cy="316" r="5" />
                        <circle cx="300" cy="50" r="4.5" />
                        <circle cx="336" cy="50" r="4.5" />
                        <circle cx="300" cy="310" r="4.5" />
                        <circle cx="336" cy="310" r="4.5" />
                        <circle cx="430" cy="42" r="5" />
                        <circle cx="415" cy="77" r="4.5" />
                        <circle cx="400" cy="112" r="4" />
                        <circle cx="430" cy="318" r="5" />
                        <circle cx="415" cy="283" r="4.5" />
                        <circle cx="400" cy="248" r="4" />
                    </g>
                </svg>
            </div>
            <div class="preloader-text">Đang kết nối hệ thống AI...</div>
        </div>
    </div>
    <div class="bg-hex"></div>
    <div class="bg-orb-tl"></div>
    <div class="bg-orb-br"></div>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="page-wrap">
            <div class="bk-page-title" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; margin-top:20px;">
                <span style="font-family:'Oswald',sans-serif;font-size:26px;font-weight:700;color:#22c55e;text-shadow:0 0 20px rgba(34,197,94,0.4);">🔑 MUA KEY VIP</span>
                <button type="button" onclick="toggleBKHistoryPanel()" style="background:transparent; border:1px solid rgba(34,197,94,0.5); color:#22c55e; padding:6px 12px; border-radius:12px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.2s;">
                    <i class="fas fa-history"></i> Lịch sử
                </button>
            </div>
            <div class="bk-page-sub">Chọn gói phù hợp để sử dụng Tool Game AI</div>

            <!-- Balance -->
            <div class="balance-card">
                <div>
                    <div class="balance-label">Số dư của bạn</div>
                    <div class="balance-amount"><i class="fas fa-coins" style="color:#f59e0b;font-size:16px;"></i><?= number_format($user['balance'] ?? 0) ?> <span style="font-size:12px;font-weight:400;color:rgba(255,255,255,0.5)">VNĐ</span></div>
                </div>
                <a href="javascript:void(0)" onclick="switchView('deposit')" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.4);color:#f59e0b;padding:8px 14px;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;">
                    <i class="fas fa-plus"></i> Nạp thêm
                </a>
            </div>

            <!-- Main card -->
            <div class="bk-card">
                <div class="bk-card-body">
                    <!-- Buy pane -->
                    <div id="buy-key-pane">
                        <div class="plan-list-grid">
                            <div class="plan-card-grid" id="plan-1day" onclick="selectPlan('1day', <?= $key_plans['1day']['price'] ?>, this)">
                                <img src="assets/img/vip1.gif" alt="VIP 1" onerror="this.src='assets/img/user.gif'">
                                <div class="plan-name-grid">GÓI 1 NGÀY</div>
                                <div class="plan-desc-grid">Trải nghiệm Tool VIP</div>
                                <?php if($key_plans['1day']['old_price'] > $key_plans['1day']['price']): ?>
                                <div style="text-decoration:line-through; font-size:12px; color:#94a3b8;"><?= number_format($key_plans['1day']['old_price']/1000) ?>K</div>
                                <?php endif; ?>
                                <div class="plan-price-grid" style="color:#22c55e;"><?= number_format($key_plans['1day']['price']/1000) ?>K</div>
                                <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                            </div>
                            <div class="plan-card-grid" id="plan-3day" onclick="selectPlan('3day', <?= $key_plans['3day']['price'] ?>, this)">
                                <img src="assets/img/vip2.gif" alt="VIP 2" onerror="this.src='assets/img/user.gif'">
                                <div class="plan-name-grid" style="color:#0ea5e9;">GÓI 3 NGÀY</div>
                                <div class="plan-desc-grid">Lựa chọn phổ biến</div>
                                <?php if($key_plans['3day']['old_price'] > $key_plans['3day']['price']): ?>
                                <div style="text-decoration:line-through; font-size:12px; color:#94a3b8;"><?= number_format($key_plans['3day']['old_price']/1000) ?>K</div>
                                <?php endif; ?>
                                <div class="plan-price-grid" style="color:#0ea5e9;"><?= number_format($key_plans['3day']['price']/1000) ?>K</div>
                                <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                            </div>
                            <div class="plan-card-grid" id="plan-7day" onclick="selectPlan('7day', <?= $key_plans['7day']['price'] ?>, this)">
                                <img src="assets/img/vip3.gif" alt="VIP 3" onerror="this.src='assets/img/user.gif'">
                                <div class="plan-name-grid" style="color:#7c3aed;">GÓI 1 TUẦN</div>
                                <div class="plan-desc-grid">Tiết kiệm hơn</div>
                                <?php if($key_plans['7day']['old_price'] > $key_plans['7day']['price']): ?>
                                <div style="text-decoration:line-through; font-size:12px; color:#94a3b8;"><?= number_format($key_plans['7day']['old_price']/1000) ?>K</div>
                                <?php endif; ?>
                                <div class="plan-price-grid" style="color:#7c3aed;"><?= number_format($key_plans['7day']['price']/1000) ?>K</div>
                                <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                            </div>
                            <div class="plan-card-grid" id="plan-30day" onclick="selectPlan('30day', <?= $key_plans['30day']['price'] ?>, this)">
                                <img src="assets/img/vip4.gif" alt="VIP 4" onerror="this.src='assets/img/user.gif'">
                                <div class="plan-name-grid" style="color:#ec4899;">GÓI 1 THÁNG</div>
                                <div class="plan-desc-grid">Dân chơi thực thụ</div>
                                <?php if($key_plans['30day']['old_price'] > $key_plans['30day']['price']): ?>
                                <div style="text-decoration:line-through; font-size:12px; color:#94a3b8;"><?= number_format($key_plans['30day']['old_price']/1000) ?>K</div>
                                <?php endif; ?>
                                <div class="plan-price-grid" style="color:#ec4899;"><?= number_format($key_plans['30day']['price']/1000) ?>K</div>
                                <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                            </div>
                            <div class="plan-card-grid" id="plan-forever" onclick="selectPlan('forever', <?= $key_plans['forever']['price'] ?>, this)">
                                <img src="assets/img/vip5.gif" alt="VIP 5" onerror="this.src='assets/img/user.gif'">
                                <div class="plan-name-grid" style="color:#f59e0b;">GÓI VĨNH VIỄN</div>
                                <div class="plan-desc-grid">Mua 1 lần dùng mãi mãi</div>
                                <?php if($key_plans['forever']['old_price'] > $key_plans['forever']['price']): ?>
                                <div style="text-decoration:line-through; font-size:12px; color:#94a3b8;"><?= number_format($key_plans['forever']['old_price']/1000) ?>K</div>
                                <?php endif; ?>
                                <div class="plan-price-grid" style="color:#f59e0b;"><?= number_format($key_plans['forever']['price']/1000) ?>K</div>
                                <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                            </div>
                        </div>



                        <button class="confirm-btn" id="btn-confirm-buy" onclick="confirmBuyKey()" disabled>XÁC NHẬN MUA</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="toast-container"></div>

    <script>
        window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
        window.currentUserId = <?= json_encode($_SESSION['user_id'] ?? 0) ?>;
        window.encryptionKeyHex = "<?= bin2hex(ENCRYPTION_KEY) ?>";
        window.encryptionIvLen = <?= ENCRYPTION_IV_LEN ?>;
        window.selectedPlanId = '';
    </script>
    <script src="assets/app.js?v=<?= time() ?>"></script>
    <script>
        // Override selectPlan to work on standalone page
        function selectPlan(planId, price, el) {
            window.selectedPlanId = planId;
            window.selectedPrice = price;
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
            if (el) el.classList.add('selected');
            

            
            const btn = document.getElementById('btn-confirm-buy');
            if (btn) { btn.disabled = false; btn.textContent = `XÁC NHẬN MUA – ${price.toLocaleString('vi-VN')}đ`; }
        }



        // Auto dismiss preloader after exactly 1000ms
        setTimeout(() => {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('fade-out');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 400);
            }
        }, 1000);
    </script>
    <?php include 'includes/bottom_nav.php'; ?>
    <?php include 'includes/modals.php'; ?>
</body>
</html>

<?php
// config.php sẽ tự start session nên không cần gọi lại
require_once __DIR__ . '/api/config.php';

// === MAINTENANCE MODE CHECK ===
$settingsFile = __DIR__ . '/admin/settings.json';
$siteSettings = json_decode(file_get_contents($settingsFile), true) ?: [];
if (!empty($siteSettings['maintenance_mode'])) {
    // Cho phép admin vượt qua bằng query ?admin_bypass=1 khi có session admin
    $isAdminSession = isset($_SESSION['admin_id']) || isset($_SESSION['admin_username']);
    $bypassKey      = $_GET['bypass'] ?? '';
    if (!$isAdminSession && $bypassKey !== 'toolgameai_admin') {
        $maintenanceMsg = $siteSettings['maintenance_message'] ?? 'Hệ thống đang bảo trì!';
        include __DIR__ . '/maintenance.php';
        exit;
    }
}

$isLoggedIn = isLoggedIn();
$user = null;
if ($isLoggedIn) {
    $user = getUserById($_SESSION['user_id']);
}

$key_plans = $siteSettings['key_plans'] ?? [
    '1day'    => ['price'=>40000, 'old_price'=>0],
    '3day'    => ['price'=>60000, 'old_price'=>0],
    '7day'    => ['price'=>99000, 'old_price'=>0],
    '30day'   => ['price'=>175000, 'old_price'=>0],
    'forever' => ['price'=>400000, 'old_price'=>0]
];

// Dữ liệu game Tool Tài Xỉu (sắp xếp theo số người chơi giảm dần)
$games = [
    ['name' => 'SUNWIN', 'logo' => 'assets/img/sunwin.png', 'players' => 1200, 'class' => 'gc-gold', 'hot' => true],
    ['name' => 'GO88', 'logo' => 'assets/img/go88.png', 'players' => 850, 'class' => 'gc-blue', 'hot' => true],
    ['name' => 'HIT CLUB', 'logo' => 'assets/img/hitclub.png', 'players' => 730, 'class' => 'gc-purple', 'hot' => true],
    ['name' => 'B52 CLUB', 'logo' => 'assets/img/b52.png', 'players' => 640, 'class' => 'gc-cyan', 'hot' => true],
    ['name' => '789 CLUB', 'logo' => 'assets/img/789club.png', 'players' => 510, 'class' => 'gc-rose', 'hot' => true],
    ['name' => 'RIKVIP', 'logo' => 'assets/img/rikvip.png', 'players' => 420, 'class' => 'gc-green', 'hot' => true],
    ['name' => 'SUMCLUB', 'logo' => 'assets/img/sumclub.png', 'players' => 380, 'class' => 'gc-purple', 'hot' => true],
    ['name' => 'LC79', 'logo' => 'assets/img/lc79.png', 'players' => 350, 'class' => 'gc-blue', 'hot' => true],
    ['name' => 'LUCK8', 'logo' => 'assets/img/luck8.png', 'players' => 340, 'class' => 'gc-gold', 'hot' => true],
    ['name' => '68GB', 'logo' => 'assets/img/68gb.png', 'players' => 330, 'class' => 'gc-green', 'hot' => true],
    ['name' => 'SON789', 'logo' => 'assets/img/son789.png', 'players' => 310, 'class' => 'gc-rose', 'hot' => true],
    ['name' => 'XÓC ĐĨA 88', 'logo' => 'assets/img/xocdia88.png', 'players' => 320, 'class' => 'gc-purple', 'hot' => true],
    ['name' => 'IWIN', 'logo' => 'assets/img/iwin.png', 'players' => 290, 'class' => 'gc-gold', 'hot' => true],
    ['name' => 'BET VIP', 'logo' => 'assets/img/betvip.png', 'players' => 180, 'class' => 'gc-blue', 'hot' => true],
];
usort($games, fn($a, $b) => $b['players'] - $a['players']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    if (strpos($scriptPath, 'index.php') !== false) {
        $baseDir = preg_replace('/index\.php$/', '', $scriptPath);
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
    <title>TOOLGAMEAI.SITE - Hệ thống AI Tool Game</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <style>
        .swal2-container { z-index: 999999 !important; }
    </style>
    <link rel="stylesheet" href="assets/new_ui.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-notice { background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(34,197,94,0.3); border-radius: 24px; margin: 20px 16px 0; padding: 20px; text-align: center; box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
        .admin-notice i { font-size: 32px; color: #22c55e; margin-bottom: 8px; }
        .admin-notice h3 { font-family: 'Oswald', sans-serif; font-size: 18px; margin-bottom: 8px; }
        .admin-notice p { font-size: 13px; color: #cbd5e1; }
        .welcome-message { background: rgba(56,189,248,0.1); border-radius: 20px; padding: 12px 20px; margin: 16px; text-align: center; border: 1px solid rgba(56,189,248,0.3); }
        .welcome-message h2 { font-size: 18px; margin: 0; }
        .welcome-message span { color: #fcd34d; }
        .gif-menu { display: flex; justify-content: center; gap: 25px; margin: 12px 16px 0; padding: 10px; background: rgba(0,0,0,0.3); border-radius: 60px; backdrop-filter: blur(8px); }
        .gif-menu-item { display: flex; flex-direction: column; align-items: center; gap: 5px; cursor: pointer; transition: 0.2s; }
        .gif-menu-item:hover { transform: translateY(-3px); }
        .gif-menu-item img { width: 32px; height: 32px; object-fit: contain; border-radius: 50%; background: rgba(0,0,0,0.4); padding: 4px; }
        .gif-menu-item span { font-size: 10px; font-weight: 600; color: #fff; }
        .mlcard-name { font-size: 13px; }
        .mlcard-beads { flex-wrap: wrap; justify-content: center; }
        .mbead { width: 28px; height: 28px; font-size: 11px; }
        .mstrip-item { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
        .mstrip-gif { width: 16px; height: 16px; object-fit: contain; margin-right: 4px; }
        .mstrip-text { font-size: 13px; }

        /* ===== WIDGET: ĐƠN HÀNG & NẠP TIỀN GẦN ĐÂY ===== */
        .recent-system-history { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 16px; }
        @media (max-width: 640px) { .recent-system-history { grid-template-columns: 1fr; } }

        .rsh-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(56,189,248,0.12);
            border-radius: 18px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        /* Header */
        .rsh-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: linear-gradient(90deg, rgba(59,130,246,0.18) 0%, rgba(99,102,241,0.10) 100%);
            border-bottom: 1px solid rgba(56,189,248,0.10);
        }
        .rsh-card-header--green {
            background: linear-gradient(90deg, rgba(34,197,94,0.16) 0%, rgba(16,185,129,0.08) 100%);
            border-bottom-color: rgba(34,197,94,0.10);
        }
        .rsh-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #93c5fd;
        }
        .rsh-card-header--green .rsh-card-title { color: #6ee7b7; }
        .rsh-card-title i { font-size: 12px; }

        /* Live dot */
        .rsh-live-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #38bdf8;
            box-shadow: 0 0 6px #38bdf8;
            animation: rsh-pulse 1.8s ease-in-out infinite;
        }
        .rsh-live-dot--green {
            background: #22c55e;
            box-shadow: 0 0 6px #22c55e;
        }
        @keyframes rsh-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        /* Count badge */
        .rsh-badge-count {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 99px;
            background: rgba(56,189,248,0.15);
            color: #38bdf8;
            border: 1px solid rgba(56,189,248,0.25);
        }
        .rsh-badge-count--green {
            background: rgba(34,197,94,0.15);
            color: #22c55e;
            border-color: rgba(34,197,94,0.25);
        }

        /* List */
        .rsh-list { padding: 6px 0; min-height: 60px; }
        .rsh-loading {
            padding: 24px;
            text-align: center;
            color: #475569;
            font-size: 12px;
        }
        .rsh-loading i { margin-right: 6px; }

        /* Row */
        .rsh-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.15s;
        }
        .rsh-row:last-child { border-bottom: none; }
        .rsh-row:hover { background: rgba(56,189,248,0.05); }

        /* Avatar circle */
        .rsh-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .rsh-avatar--green {
            background: linear-gradient(135deg, #10b981, #22c55e);
        }

        /* Info block */
        .rsh-info { flex: 1; min-width: 0; }
        .rsh-username {
            font-size: 12px; font-weight: 700;
            color: #e2e8f0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .rsh-desc {
            font-size: 10px; color: #64748b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-top: 1px;
        }

        /* Right side */
        .rsh-right { text-align: right; flex-shrink: 0; }
        .rsh-amount {
            font-size: 12px; font-weight: 800;
            color: #fbbf24;
        }
        .rsh-time {
            font-size: 10px; color: #475569;
            margin-top: 1px;
        }

        /* Method badge */
        .rsh-method-badge {
            display: inline-block;
            font-size: 9px; font-weight: 700;
            padding: 2px 6px; border-radius: 6px;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .rsh-method-badge.ocb     { background: rgba(0,207,255,0.15); color: #00cfff; border: 1px solid rgba(0,207,255,0.3); }
        .rsh-method-badge.card    { background: rgba(251,191,36,0.15); color: #fbbf24; border: 1px solid rgba(251,191,36,0.3); }
        .rsh-method-badge.default { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); }

        /* Plan badge */
        .rsh-plan-badge {
            display: inline-block;
            font-size: 9px; font-weight: 700;
            padding: 2px 6px; border-radius: 6px;
            letter-spacing: 0.3px;
            margin-top: 2px;
            background: rgba(167,139,250,0.15);
            color: #c084fc;
            border: 1px solid rgba(167,139,250,0.3);
        }
    </style>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
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

    <?php include 'includes/header.php'; ?>
    
    

    <main>
        <!-- MARQUEE -->
        <div class="mstrip">
            <div class="mstrip-lbl">
                <div class="mstrip-badge-icon"><i class="fas fa-bolt" style="color:#f59e0b;font-size:12px;"></i></div>
                <div class="mstrip-text" style="margin-left:8px;">HỆ THỐNG</div>
            </div>
            <div class="mstrip-fade-l"></div>
            <div class="mstrip-track">
                <div class="mstrip-inner">
                    <div class="mstrip-item">
                        <img src="https://i.imgur.com/TB6V1ww.gif" class="mstrip-gif">
                        <span class="mstrip-text">Chào mừng <?= $user ? $user['username'] : 'Khách' ?> đến với Tool Game AI</span>
                        <span class="mstrip-sep">|</span>
                    </div>
                    <div class="mstrip-item">
                        <img src="https://i.imgur.com/Daw8j7T.gif" class="mstrip-gif">
                        <span class="mstrip-text">Phân tích dữ liệu thời gian thực chuẩn xác 99%</span>
                        <span class="mstrip-sep">|</span>
                    </div>
                    <div class="mstrip-item">
                        <img src="https://i.imgur.com/TB6V1ww.gif" class="mstrip-gif">
                        <span class="mstrip-text">Tự động nạp tiền 10s - 30s. Liên hệ Admin nếu gặp sự cố.</span>
                        <span class="mstrip-sep">|</span>
                    </div>
                    <!-- Lặp lại để chạy mượt -->
                    <div class="mstrip-item">
                        <img src="https://i.imgur.com/Daw8j7T.gif" class="mstrip-gif">
                        <span class="mstrip-text">Chào mừng <?= $user ? $user['username'] : 'Khách' ?> đến với Tool Game AI</span>
                        <span class="mstrip-sep">|</span>
                    </div>
                    <div class="mstrip-item">
                        <img src="https://i.imgur.com/TB6V1ww.gif" class="mstrip-gif">
                        <span class="mstrip-text">Phân tích dữ liệu thời gian thực chuẩn xác 99%</span>
                        <span class="mstrip-sep">|</span>
                    </div>
                </div>
            </div>
            <div class="mstrip-fade-r"></div>
        </div>

        <div class="main-layout-container fade-in-element">
            <div class="app-left-content">
                <!-- VIEW HOME (GAME LISTINGS) -->
                <div class="mpage" id="view-home">
                    <!-- TABS -->
                    <div class="mtabs">
                        <div class="mtab on" onclick="mFilter('all',this)">TẤT CẢ</div>
                        <div class="mtab" onclick="mFilter('taixiu',this)">TOOL TÀI XỈU</div>
                        <div class="mtab" onclick="mFilter('baccarat',this)">TOOL BACCARAT</div>
                        <div class="mtab" onclick="mFilter('sicbo',this)">TOOL SICBO</div>
                    </div>
                    
                    <!-- XÁC THỰC EMAIL BANNER (Nếu chưa xác thực) -->
                    <?php if ($isLoggedIn && empty($user['is_email_verified'])): ?>
                    <div style="margin: 15px 16px; background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid rgba(236, 72, 153, 0.3); border-radius: 16px; padding: 15px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.15); position: relative; overflow: hidden; animation: floatBanner 3s ease-in-out infinite;">
                        <style>
                            @keyframes floatBanner { 0% { transform: translateY(0px); } 50% { transform: translateY(-3px); } 100% { transform: translateY(0px); } }
                            .verify-glow { position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(236,72,153,0.1) 0%, transparent 70%); pointer-events: none; animation: spinGlow 10s linear infinite; }
                            @keyframes spinGlow { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                        </style>
                        <div class="verify-glow"></div>
                        <div style="display: flex; align-items: center; gap: 12px; position: relative; z-index: 1;">
                            <div style="background: rgba(236, 72, 153, 0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ec4899; font-size: 18px; box-shadow: 0 0 10px rgba(236, 72, 153, 0.4);">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div>
                                <div style="color: #fbcfe8; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Nhận Ngay 5.000đ</div>
                                <div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">Xác thực email để bảo vệ tài khoản</div>
                            </div>
                        </div>
                        <button onclick="switchView('profile')" style="position: relative; z-index: 1; background: linear-gradient(135deg, #ec4899, #db2777); color: #fff; border: none; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.4); text-transform: uppercase;">
                            Xác thực ngay
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- LIVESTREAM BACCARAT -->
                    <div class="mslbl" id="lbl-live">
                        <div class="msbar" style="background:#ef4444;"></div>
                        <div class="mstit"><i class="fas fa-broadcast-tower" style="color:#ef4444;"></i> LIVESTREAM BACCARAT <span class="badge-live">LIVE</span></div>
                    </div>
                    <div class="mlive-sec" id="sec-live">
                        <div class="mlive-grid">
                            <div class="mlcard" onclick="openTool('Baccarat AeSexy', 'https://aibcr.me/images/sanh/ae.png')">
                                <div class="mlcard-hot">HOT</div>
                                <div class="mlcard-live"><div class="mlcard-ldot"></div><div class="mlcard-ltxt">LIVE</div></div>
                                <div class="mlcard-logo-wrap">
                                    <img class="mlcard-logo-img" src="https://aibcr.me/images/sanh/ae.png" alt="AE Sexy">
                                </div>
                                <div class="mlcard-bot">
                                    <div class="mlcard-name mlcard-name-gold">AE SEXY</div>
                                    <div class="mlcard-sub">BACCARAT LIVE</div>
                                    <div class="mlcard-beads"><div class="mbead mbead-b">B</div><div class="mbead mbead-r">P</div><div class="mbead mbead-b">B</div><div class="mbead mbead-t">T</div><div class="mbead mbead-r">P</div></div>
                                    <div class="mlcard-choose">VÀO SẢNH <i class="fas fa-chevron-right"></i></div>
                                </div>
                            </div>
                            <div class="mlcard" onclick="openTool('Baccarat DreamGaming', 'https://aibcr.me/images/sanh/dg.png')">
                                <div class="mlcard-live"><div class="mlcard-ldot"></div><div class="mlcard-ltxt">LIVE</div></div>
                                <div class="mlcard-logo-wrap">
                                    <img class="mlcard-logo-img" src="https://aibcr.me/images/sanh/dg.png" alt="DG Casino">
                                </div>
                                <div class="mlcard-bot">
                                    <div class="mlcard-name mlcard-name-blue">DG CASINO</div>
                                    <div class="mlcard-sub">BACCARAT LIVE</div>
                                    <div class="mlcard-beads"><div class="mbead mbead-r">P</div><div class="mbead mbead-r">P</div><div class="mbead mbead-b">B</div><div class="mbead mbead-b">B</div><div class="mbead mbead-t">T</div></div>
                                    <div class="mlcard-choose">VÀO SẢNH <i class="fas fa-chevron-right"></i></div>
                                </div>
                            </div>
                            <div class="mlcard" onclick="openTool('Baccarat Evolution', 'https://aibcr.me/images/sanh/evo.png')">
                                <div class="mlcard-live"><div class="mlcard-ldot"></div><div class="mlcard-ltxt">LIVE</div></div>
                                <div class="mlcard-logo-wrap">
                                    <img class="mlcard-logo-img" src="https://aibcr.me/images/sanh/evo.png" alt="Evolution">
                                </div>
                                <div class="mlcard-bot">
                                    <div class="mlcard-name mlcard-name-green">EVOLUTION</div>
                                    <div class="mlcard-sub">BACCARAT LIVE</div>
                                    <div class="mlcard-beads"><div class="mbead mbead-b">B</div><div class="mbead mbead-t">T</div><div class="mbead mbead-r">P</div><div class="mbead mbead-b">B</div><div class="mbead mbead-r">P</div></div>
                                    <div class="mlcard-choose">VÀO SẢNH <i class="fas fa-chevron-right"></i></div>
                                </div>
                            </div>
                            <div class="mlcard" onclick="openTool('Baccarat WM', 'https://aibcr.me/images/sanh/wm.png')">
                                <div class="mlcard-live"><div class="mlcard-ldot"></div><div class="mlcard-ltxt">LIVE</div></div>
                                <div class="mlcard-logo-wrap">
                                    <img class="mlcard-logo-img" src="https://aibcr.me/images/sanh/wm.png" alt="WM Casino">
                                </div>
                                <div class="mlcard-bot">
                                    <div class="mlcard-name mlcard-name-gold">WM CASINO</div>
                                    <div class="mlcard-sub">BACCARAT LIVE</div>
                                    <div class="mlcard-beads"><div class="mbead mbead-r">P</div><div class="mbead mbead-b">B</div><div class="mbead mbead-t">T</div><div class="mbead mbead-r">P</div><div class="mbead mbead-b">B</div></div>
                                    <div class="mlcard-choose">VÀO SẢNH <i class="fas fa-chevron-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOOL TÀI XỈU VIP -->
                    <div class="mslbl" id="lbl-game">
                        <div class="msbar" style="background:#3b82f6;"></div>
                        <div class="mstit">
                            <i class="fas fa-gamepad" style="color:#3b82f6;"></i> 
                            TOOL 
                            <img src="assets/img/tai.png" alt="Tài" style="height:1.1em; vertical-align:middle; margin:0 2px;">
                            <img src="assets/img/xiu.png" alt="Xỉu" style="height:1.1em; vertical-align:middle; margin:0 2px;">
                            <span class="badge-live">ONLINE</span>
                        </div>
                    </div>
                    <div class="tool-section" id="sec-game">
                        <div class="mggrid">
                            <?php foreach($games as $game): ?>
                            <div class="mgcard <?= $game['class'] ?>" onclick="openTool('<?= $game['name'] ?>', '<?= $game['logo'] ?>')">
                                <?php if($game['hot']): ?>
                                <img class="mgcard__badge" src="https://i.imgur.com/7MqSOai.png">
                                <?php endif; ?>
                                <div class="mgcard__vip">VIP</div>
                                <div class="mgcard__inner">
                                    <div class="mgcard__logo-wrap">
                                        <img class="mgcard__logo" src="<?= $game['logo'] ?>">
                                    </div>
                                    <div class="mgcard__name"><?= $game['name'] ?></div>
                                    <div class="mgcard__players">
                                        <i class="fas fa-users"></i> <?= number_format($game['players']) ?>+
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- TOOL SICBO -->
                    <div class="tool-section" id="sec-sicbo">
                        <div class="mslbl"><div class="msbar" style="background:#f59e0b;"></div><div class="mstit"><i class="fas fa-dice"></i> TOOL SICBO <span class="badge-live">LIVE</span></div></div>
                        <div class="sb-grid">
                            <div class="sb-card" onclick="openTool('Sicbo Sunwin', 'assets/img/sicbosun.png')"><img class="sb-card__img" src="assets/img/sicbosun.png"><div class="sb-card__label"><span class="sb-card__name">SICBO SUNWIN</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                            <div class="sb-card" onclick="openTool('Sicbo Hitclub', 'assets/img/sicbohitclub.png')"><img class="sb-card__img" src="assets/img/sicbohitclub.png"><div class="sb-card__label"><span class="sb-card__name">SICBO HITCLUB</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                            <div class="sb-card" onclick="openTool('Sicbo B52', 'assets/img/sicbob52.png')"><img class="sb-card__img" src="assets/img/sicbob52.png"><div class="sb-card__label"><span class="sb-card__name">SICBO B52 CLUB</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                            <div class="sb-card" onclick="openTool('Sicbo 789club', 'assets/img/sicbo789.png')"><img class="sb-card__img" src="assets/img/sicbo789.png"><div class="sb-card__label"><span class="sb-card__name">SICBO 789 CLUB</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                            <div class="sb-card" onclick="openTool('Sicbo Luck8', 'assets/img/sicboluck8.png')"><img class="sb-card__img" src="assets/img/sicboluck8.png"><div class="sb-card__label"><span class="sb-card__name">SICBO LUCK8</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                            <div class="sb-card" onclick="openTool('Sicbo Sumclub', 'assets/img/sicbosum.png')"><img class="sb-card__img" src="assets/img/sicbosum.png"><div class="sb-card__label"><span class="sb-card__name">SICBO SUMCLUB</span><div class="sb-card__live"><div class="sb-card__ldot"></div>LIVE</div></div></div>
                        </div>
                    </div>

                    <!-- SẢN PHẨM MÃ NGUỒN NỔI BẬT -->
                    <div class="mslbl" id="lbl-sourcecode">
                        <div class="msbar" style="background:linear-gradient(135deg, #16a34a, #0ea5e9);"></div>
                        <div class="mstit"><i class="fas fa-file-code" style="color:#16a34a;"></i> MÃ NGUỒN NỔI BẬT <span style="font-size:11px;color:#94a3b8;font-weight:400;margin-left:4px;">Dịch vụ tốt nhất cho công việc của bạn</span></div>
                    </div>
                    
                    <!-- FILTER TABS FOR SOURCE CODE -->
                    <div class="sc-filter-tabs">
                        <button class="sc-tab active" onclick="filterSourceCodes('all', this)">Tất cả</button>
                        <button class="sc-tab" onclick="filterSourceCodes('new', this)">Sản phẩm mới</button>
                        <button class="sc-tab" onclick="filterSourceCodes('best', this)">Bán chạy</button>
                        <button class="sc-tab" onclick="filterSourceCodes('cheap', this)">Giá rẻ</button>
                        <button class="sc-tab" onclick="filterSourceCodes('free', this)">Miễn phí</button>
                    </div>

                    <div class="sc-section" id="sec-sourcecode">
                        <div class="sc-grid" id="sc-product-grid">
                            <!-- Cards dynamically loaded by AJAX -->
                            <div style="grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Đang tải danh sách sản phẩm...</div>
                        </div>
                    </div>

                    <!-- Global Widget: Recent Transactions -->
                    <div class="recent-system-history">
                        <!-- ĐƠN HÀNG GẦN ĐÂY -->
                        <div class="rsh-card">
                            <div class="rsh-card-header">
                                <div class="rsh-card-title">
                                    <div class="rsh-live-dot"></div>
                                    <i class="fas fa-shopping-bag"></i>
                                    ĐƠN HÀNG GẦN ĐÂY
                                </div>
                                <span class="rsh-badge-count" id="buys-count">--</span>
                            </div>
                            <div class="rsh-list" id="widget-buys-list">
                                <div class="rsh-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                            </div>
                        </div>
                        <!-- NẠP TIỀN GẦN ĐÂY -->
                        <div class="rsh-card">
                            <div class="rsh-card-header rsh-card-header--green">
                                <div class="rsh-card-title">
                                    <div class="rsh-live-dot rsh-live-dot--green"></div>
                                    <i class="fas fa-wallet"></i>
                                    NẠP TIỀN GẦN ĐÂY
                                </div>
                                <span class="rsh-badge-count rsh-badge-count--green" id="deposits-count">--</span>
                            </div>
                            <div class="rsh-list" id="widget-deposits-list">
                                <div class="rsh-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- VIEW RANKING (SPA PANEL) -->
                <div class="mpage" id="view-ranking" style="display: none;">
                    <div style="padding: 16px;">
                        <div class="back-bar">
                            <button type="button" onclick="switchView('home')" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại</button>
                        </div>

                        <div class="rank-page-title">🏆 BẢNG XẾP HẠNG VIP</div>

                        <?php if ($isLoggedIn): ?>
                        <!-- Current User VIP Panel -->
                        <div class="vip-panel" id="vip-panel">
                            <div class="vip-panel-row">
                                <img class="vip-panel-icon" id="current-vip-icon" src="assets/img/icon1.png" alt="VIP">
                                <div>
                                    <div class="vip-panel-label">HẠNG VIP CỦA BẠN</div>
                                    <div class="vip-panel-name" id="current-vip-name">VIP1</div>
                                    <div class="vip-panel-deposit" id="current-total-deposit">Tổng nạp: đang tải...</div>
                                </div>
                            </div>
                            <div class="progress-labels">
                                <span id="bar-label-start">0đ</span>
                                <span id="bar-label-end">40.000đ</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" id="vip-progress-bar"></div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="not-logged-notice">
                            <i class="fas fa-lock"></i>
                            <p>Vui lòng <a href="javascript:void(0)" onclick="openModal('login-modal')">đăng nhập</a> để xem hạng VIP của bạn.</p>
                        </div>
                        <?php endif; ?>

                        <!-- Ranking List -->
                        <div class="rank-box">
                            <div class="rank-box-title"><i class="fas fa-medal" style="color:#f59e0b;"></i> TOP NGƯỜI CHƠI NẠP NHIỀU NHẤT</div>
                            <div class="rank-table-header">
                                <div style="width:50px;text-align:center;">Hạng</div>
                                <div style="flex:1;">Người chơi</div>
                                <div style="width:110px;text-align:right;">Tổng nạp</div>
                                <div style="width:40px;text-align:center;">VIP</div>
                            </div>
                            <div id="ranking-list-container">
                                <div class="loading-spin"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                            </div>
                        </div>

                        <!-- VIP Guide -->
                        <div class="guide-box">
                            <div class="guide-title"><i class="fas fa-chart-line"></i> HƯỚNG DẪN THĂNG HẠNG VIP</div>
                            <div class="guide-grid" id="vip-guide-list">
                                <div class="loading-spin" style="grid-column:1/-1;padding:20px;"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW DEPOSIT (SPA PANEL) -->
                <div class="mpage" id="view-deposit" style="display: none;">
                    <div style="padding: 16px;">
                        
                        <div class="nicepay-container">
                            <!-- Header tabs -->
                            <div class="nicepay-tabs">
                                <button class="np-tab active" id="dp-tab-bank" onclick="switchDepositTab('bank')">NGÂN HÀNG</button>
                                <button class="np-tab" id="dp-tab-card" onclick="switchDepositTab('card')">THẺ CÀO</button>
                                <button class="np-close" onclick="switchView('home')"><i class="fas fa-times"></i></button>
                            </div>
                            
                            <div class="nicepay-content">
                                <!-- VIEW BANKING DEPOSIT -->
                                <div id="dp-bank-view">
                                    <!-- Bước 1: Chọn tiền và ngân hàng -->
                                    <div class="np-step-wrap" id="dp-step-selection">
                                        <div class="nicepay-grid">
                                            <!-- Cột bên trái -->
                                            <div class="np-col-left">
                                                <div class="np-form-group">
                                                    <label class="np-label">Ngân Hàng:</label>
                                                    <div class="np-select-wrapper">
                                                        <select id="deposit-bank-select" class="np-select">
                                                            <option value="OCB" selected>OCB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="np-form-group">
                                                    <label class="np-label">Chi Nhánh:</label>
                                                    <div class="np-readonly-val">Tất cả chi nhánh</div>
                                                </div>
                                                <div class="np-form-group">
                                                    <label class="np-label">Phương Thức:</label>
                                                    <div class="np-readonly-val">Chuyển khoản nhanh 24/7</div>
                                                </div>
                                                <div class="np-form-group" style="margin-top: 8px;">
                                                    <div class="np-readonly-val" style="border: none; background: rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                                        <span style="font-size: 11px; color: rgba(255,255,255,0.5);">Số dư hiện tại:</span>
                                                        <span style="font-weight: 800; color: #fcd34d;"><span id="deposit-user-balance"><?= number_format($user['balance'] ?? 0) ?></span>đ</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="np-footer-left">
                                                    <button type="button" class="np-hist-icon-btn" onclick="toggleHistoryPanel()" title="Lịch sử nạp tiền">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Cột bên phải -->
                                            <div class="np-col-right">
                                                <!-- Banner Nicepay -->
                                                <div class="np-banner" style="background: #105020; border-color: #208c40;">
                                                    <div class="np-banner-logos">
                                                        <span class="bank-badge" style="background:#00cfff;color:#000;font-size:9px;padding:2px 6px;">OCB BANK</span>
                                                    </div>
                                                    <div class="np-banner-title">NẠP TỰ ĐỘNG - 1 PHÚT CÓ TIỀN</div>
                                                </div>
                                                
                                                <div class="np-amount-wrapper">
                                                    <label class="np-amount-label">Số Tiền:</label>
                                                    <div class="np-input-wrapper">
                                                        <input type="number" id="deposit-amount" class="np-amount-input" value="100000" placeholder="Nhập số tiền...">
                                                        <span class="np-currency">đ</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Quick select buttons -->
                                                <div class="np-quick-grid">
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(10000)">10K</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(20000)">20K</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(50000)">50K</button>
                                                    <button type="button" class="np-quick-btn active" onclick="setDepositAmount(100000)">100K</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(200000)">200K</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(500000)">500K</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(1000000)">1M</button>
                                                    <button type="button" class="np-quick-btn" onclick="setDepositAmount(5000000)">5M</button>
                                                </div>
                                                
                                                <button type="button" class="np-submit-btn dp-submit-btn" onclick="submitDeposit()">TẠO CODE</button>
                                                
                                                <div class="np-footer-right">
                                                    <button type="button" class="np-help-icon-btn telegram-wiggle" onclick="window.open('https://t.me/hellokietne21','_blank')" title="Hỗ trợ Telegram">
                                                        <i class="fab fa-telegram-plane" style="color:#0ea5e9;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bước 2: Hiển thị QR Code thanh toán -->
                                    <div class="np-step-wrap" id="dp-step-qr" style="display: none;">
                                        <div class="nicepay-grid">
                                            <!-- Cột bên trái -->
                                            <div class="np-col-left">
                                                <div class="np-form-group">
                                                    <label class="np-label">Ngân Hàng:</label>
                                                    <div class="np-readonly-val">OCB Bank</div>
                                                </div>
                                                
                                                <!-- LOGO NGÂN HÀNG OCB -->
                                                <div class="np-bank-logo-wrap">
                                                    <div class="np-ocb-logo">OCB</div>
                                                </div>
                                                
                                                <div class="np-new-banking-details">
                                                    <!-- Chủ tài khoản -->
                                                    <div class="np-info-row-new">
                                                        <span class="np-info-lbl-new"><i class="fas fa-user" style="color: #94a3b8; margin-right: 8px;"></i>Chủ tài khoản:</span>
                                                        <span class="np-info-val-new font-navy-blue">NGUYEN THI DAI</span>
                                                    </div>
                                                    
                                                    <!-- Số tài khoản -->
                                                    <div class="np-info-row-new">
                                                        <span class="np-info-lbl-new"><i class="fas fa-credit-card" style="color: #94a3b8; margin-right: 8px;"></i>Số tài khoản:</span>
                                                        <span class="np-info-val-new font-dark-green">0369823800 <i class="far fa-copy copy-icon-green" onclick="copyText('0369823800')"></i></span>
                                                    </div>

                                                    <!-- Số tiền -->
                                                    <div class="np-info-row-new">
                                                        <span class="np-info-lbl-new"><i class="fas fa-coins" style="color: #94a3b8; margin-right: 8px;"></i>Số tiền:</span>
                                                        <span class="np-info-val-new font-orange" id="qr-display-amount">0đ <i class="far fa-copy copy-icon-orange" onclick="copyText(document.getElementById('qr-display-amount').innerText.replace('đ','').replace(/\./g,''))"></i></span>
                                                    </div>

                                                    <!-- Nội dung chuyển khoản box -->
                                                    <div class="np-ndck-wrapper">
                                                        <div class="np-ndck-label">NỘI DUNG CHUYỂN KHOẢN</div>
                                                        <div class="np-ndck-box" id="qr-display-content">
                                                            ---
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="np-footer-left-msg" style="margin-top: 15px;">
                                                    <button type="button" class="np-hist-icon-btn" onclick="toggleHistoryPanel()"><i class="fas fa-history"></i></button>
                                                    <span class="np-footer-msg-text">Vui lòng quét mã chuyển tiền tới tài khoản OCB ở bên.</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Cột bên phải -->
                                            <div class="np-col-right">
                                                <!-- Banner Nicepay -->
                                                <div class="np-banner" style="background: #105020; border-color: #208c40;">
                                                    <div class="np-banner-logos">
                                                        <span class="bank-badge" style="background:#00cfff;color:#000;font-size:9px;padding:2px 6px;">OCB BANK</span>
                                                    </div>
                                                    <div class="np-banner-title">NẠP TỰ ĐỘNG - 1 PHÚT CÓ TIỀN</div>
                                                </div>
                                                
                                                <div class="np-qr-instruction" style="margin: 10px 0;">
                                                    Mã QR nạp tiền sử dụng 1 lần. Quét và giữ nguyên nội dung chuyển khoản
                                                </div>
                                                
                                                <div class="np-qr-img-wrap">
                                                    <img src="" id="deposit-qr-img" alt="QR Code">
                                                </div>
                                                
                                                <div class="np-expiry-timer" style="margin-top: 10px;">
                                                    Mã Hết Hạn Sau: <span id="qr-timer" style="color:#ffe066; font-weight:bold;">30:00s</span>
                                                </div>
                                                
                                                <div class="np-refresh-link-wrap" style="margin: 10px 0;">
                                                    <span class="np-refresh-link" onclick="refreshDeposit()">Tạo giao dịch mới?</span>
                                                </div>
                                                
                                                <div class="np-footer-right">
                                                    <button type="button" class="np-help-icon-btn telegram-wiggle" onclick="window.open('https://t.me/hellokietne21','_blank')" title="Hỗ trợ Telegram"><i class="fab fa-telegram-plane" style="color:#0ea5e9;"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- VIEW SCRATCH CARD DEPOSIT -->
                                <div id="dp-card-view" style="display: none;">
                                    <div class="nicepay-grid">
                                        <!-- Cột bên trái: Form nạp thẻ -->
                                        <div class="np-col-left">
                                            <div class="np-form-group">
                                                <label class="np-label">Nhà Mạng:</label>
                                                <div class="card-network-grid">
                                                    <input type="hidden" id="card-network-select" value="1">
                                                    
                                                    <div class="network-logo-btn active" data-network="1" onclick="selectNetwork(this)">
                                                        <img src="assets/img/viettel.png" alt="Viettel">
                                                        <span>Viettel</span>
                                                    </div>
                                                    <div class="network-logo-btn" data-network="2" onclick="selectNetwork(this)">
                                                        <img src="assets/img/vinaphone.png" alt="Vinaphone">
                                                        <span>Vinaphone</span>
                                                    </div>
                                                    <div class="network-logo-btn" data-network="3" onclick="selectNetwork(this)">
                                                        <img src="assets/img/mobifone.png" alt="Mobifone">
                                                        <span>Mobifone</span>
                                                    </div>
                                                    <div class="network-logo-btn" data-network="4" onclick="selectNetwork(this)">
                                                        <img src="assets/img/vietnammobile.webp" alt="Vietnamobile">
                                                        <span>Vietnamobile</span>
                                                    </div>
                                                    <div class="network-logo-btn" data-network="5" onclick="selectNetwork(this)">
                                                        <img src="assets/img/zing.png" alt="Zing">
                                                        <span>Zing</span>
                                                    </div>
                                                    <div class="network-logo-btn" data-network="6" onclick="selectNetwork(this)">
                                                        <img src="assets/img/garena.png" alt="Garena">
                                                        <span>Garena</span>
                                                    </div>
                                                </div>
                                                <script>
                                                function selectNetwork(el) {
                                                    document.querySelectorAll('.network-logo-btn').forEach(btn => {
                                                        btn.classList.remove('active');
                                                    });
                                                    el.classList.add('active');
                                                    document.getElementById('card-network-select').value = el.getAttribute('data-network');
                                                    if (typeof onCardRateUpdate === 'function') onCardRateUpdate();
                                                }
                                                </script>
                                            </div>
                                            <div class="np-form-group">
                                                <label class="np-label">Mệnh Giá:</label>
                                                <div class="np-select-wrapper">
                                                    <select id="card-amount-select" class="np-select" onchange="onCardRateUpdate()">
                                                        <option value="10000">10,000đ</option>
                                                        <option value="20000">20,000đ</option>
                                                        <option value="50000">50,000đ</option>
                                                        <option value="100000" selected>100,000đ</option>
                                                        <option value="200000">200,000đ</option>
                                                        <option value="500000">500,000đ</option>
                                                        <option value="1000000">1,000,000đ</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="np-amount-wrapper">
                                                <label class="np-amount-label">Số Seri:</label>
                                                <div class="np-input-wrapper">
                                                    <input type="text" id="card-seri" class="np-amount-input" style="font-size: 14px; text-transform: uppercase;" placeholder="Nhập số seri thẻ...">
                                                </div>
                                            </div>
                                            
                                            <div class="np-amount-wrapper">
                                                <label class="np-amount-label">Mã Thẻ (PIN):</label>
                                                <div class="np-input-wrapper">
                                                    <input type="text" id="card-code" class="np-amount-input" style="font-size: 14px; text-transform: uppercase;" placeholder="Nhập mã pin của thẻ...">
                                                </div>
                                            </div>
                                            
                                            <!-- Dynamic payout calculations -->
                                            <div class="card-payout-calc-box">
                                                <div class="calc-row">
                                                    <span class="calc-label">Chiết khấu nhà mạng:</span>
                                                    <span id="card-fee-display" class="calc-val-red">--%</span>
                                                </div>
                                                <div id="card-promo-row" class="calc-row" style="display: none;">
                                                    <span class="calc-label">Khuyến mãi nạp tiền:</span>
                                                    <span id="card-promo-display" class="calc-val-green">+0%</span>
                                                </div>
                                                <hr class="calc-divider">
                                                <div class="calc-row calc-payout-row">
                                                    <span class="calc-label-payout">Số dư thực nhận:</span>
                                                    <span id="card-payout-display" class="calc-val-payout">0đ</span>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="np-submit-btn" id="btn-submit-card" onclick="submitCardDeposit()" style="margin-top: 5px;">GỬI THẺ</button>
                                            
                                            <div class="np-footer-left" style="margin-top: 15px;">
                                                <button type="button" class="np-hist-icon-btn" onclick="toggleHistoryPanel()" title="Lịch sử nạp tiền">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Cột bên phải: Bảng chiết khấu các nhà mạng -->
                                        <div class="np-col-right" style="align-items: stretch;">
                                            <div class="np-banner card-banner-purple">
                                                <div class="np-banner-logos">
                                                    <span class="momo-badge text-yellow-badge">10S XỬ LÝ</span>
                                                </div>
                                                <div class="np-banner-title text-green-bold">GẠCH THẺ TỰ ĐỘNG</div>
                                            </div>
                                            
                                            <div class="card-rate-table-outer">
                                                <div class="card-rate-table-header">
                                                    ⚡ BẢNG CHIẾT KHẤU HÔM NAY
                                                </div>
                                                <div id="card-rate-table" class="card-rate-table-body">
                                                    <div class="rate-table-row rate-table-head">
                                                        <span>Nhà Mạng</span>
                                                        <span>Chiết Khấu</span>
                                                    </div>
                                                    <div class="loading-spin" style="padding:10px; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
                                                </div>
                                            </div>
                                            
                                            <div class="np-footer-right" style="margin-top: auto;">
                                                <button type="button" class="np-help-icon-btn telegram-wiggle" onclick="window.open('https://t.me/hellokietne21','_blank')" title="Hỗ trợ Telegram">
                                                    <i class="fab fa-telegram-plane" style="color:#0ea5e9;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>
                        
                    </div>
                </div>
            </div> <!-- CLOSE view-deposit -->

            <!-- VIEW BUY KEY (SPA PANEL) -->
            <div class="mpage" id="view-buykey" style="display: none;">
                <div style="padding: 16px 16px 80px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <div style="font-family:'Oswald',sans-serif;font-size:26px;font-weight:700;color:#22c55e;text-shadow:0 0 20px rgba(34,197,94,0.4);">🔑 MUA KEY VIP</div>
                        <button type="button" onclick="toggleBKHistoryPanel()" style="background:transparent; border:1px solid rgba(34,197,94,0.5); color:#22c55e; padding:6px 12px; border-radius:12px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.2s;">
                            <i class="fas fa-history"></i> Lịch sử
                        </button>
                    </div>
                    <div style="text-align:center;font-size:12px;color:#94a3b8;margin-bottom:20px;">Chọn gói phù hợp để sử dụng Tool Game AI</div>

                    <!-- Balance card -->
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                        <div>
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Số dư của bạn</div>
                            <div style="font-size:20px;font-weight:800;color:#f59e0b;display:flex;align-items:center;gap:6px;">
                                                <i class="fas fa-coins" style="color:#f59e0b;font-size:16px;"></i>
                                <span id="buykey-user-balance"><?= number_format($user['balance'] ?? 0) ?></span>
                                <span style="font-size:12px;font-weight:400;color:#64748b">VNĐ</span>
                            </div>
                        </div>
                        <button type="button" onclick="switchView('deposit')" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.4);color:#f59e0b;padding:8px 14px;border-radius:12px;font-size:12px;font-weight:700;white-space:nowrap;cursor:pointer;">
                            <i class="fas fa-plus"></i> Nạp thêm
                        </button>
                    </div>

                    <!-- Main card -->
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:24px;overflow:hidden;margin-bottom:16px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                        <div style="padding:20px;">
                            <!-- Buy pane -->
                            <div id="buy-key-pane">
                                <!-- Plan list grid table -->
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
                                        <div class="plan-price-grid" style="color:#f59e0b;">400K</div>
                                        <div class="plan-check-grid"><i class="fas fa-check"></i></div>
                                    </div>
                                </div>



                                <!-- Confirm button -->
                                <button class="confirm-btn" id="btn-confirm-buy" onclick="confirmBuyKey()" style="width:100%;background:linear-gradient(135deg,#065f46,#10b981,#065f46);border:none;color:#ffffff;font-family:'Oswald',sans-serif;font-weight:900;letter-spacing:2px;cursor:pointer;box-shadow:0 8px 24px rgba(16,185,129,0.4);">
                                    XÁC NHẬN MUA
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>


            </div>

            
            <!-- THÔNG TIN CÁ NHÂN -->
            <div class="mpage" id="view-profile" style="display: none;">
                <div style="padding: 16px 16px 80px;">
                    
                    <div style="text-align:center; margin-bottom: 24px; position:relative;">
                        <div class="avatar-upload-container" style="position:relative; width:90px; height:90px; margin:0 auto 12px;">
                            <img class="user-avatar-img" src="<?= ($isLoggedIn && !empty($user['avatar'])) ? htmlspecialchars($user['avatar']) : 'assets/img/user.gif' ?>" alt="Avatar" style="width:90px;height:90px;border-radius:50%;border:3px solid var(--primary-green);object-fit:cover;">
                            <div class="avatar-upload-overlay" onclick="document.getElementById('avatar-file-input').click()" style="position:absolute; bottom:0; right:0; background:var(--primary-gradient); width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; border:2px solid #fff; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                                <i class="fas fa-camera" style="font-size:11px;"></i>
                            </div>
                            <input type="file" id="avatar-file-input" onchange="uploadAvatar(this)" style="display:none;" accept="image/*">
                        </div>
                        <div style="font-size:20px;font-weight:800;color:var(--text-dark);" id="profile-display-name"><?= htmlspecialchars(!empty($user['nickname']) ? $user['nickname'] : ($user['username'] ?? 'Khách')) ?></div>
                        <div style="font-size:12px;color:var(--text-gray);">Mã số ID: #<?= $user['id'] ?? '---' ?></div>
                    </div>
                    
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <div style="font-size:12px; color:var(--text-gray); margin-bottom:6px; text-transform:uppercase; font-weight:700;">Tài sản hiện tại</div>
                        <div style="font-size:32px; font-weight:800; color:#f59e0b; display:flex; align-items:center; gap:10px;">
                            <i class="fas fa-coins"></i>
                            <span><?= number_format($user['balance'] ?? 0) ?></span>
                            <span style="font-size:16px; font-weight:500; color:var(--text-gray)">VNĐ</span>
                        </div>
                        <button type="button" onclick="switchView('deposit')" style="margin-top:16px; width:100%; background:var(--primary-gradient); color:#fff; border:none; padding:12px; border-radius:12px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fas fa-plus-circle"></i> NẠP TIỀN NGAY
                        </button>
                    </div>

                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <div style="font-size:13px; color:var(--text-dark); margin-bottom:12px; font-weight:700;"><i class="fas fa-user-edit" style="color:var(--primary-green);"></i> Cập nhật hồ sơ</div>
                        
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; flex-direction:column; gap:4px; text-align:left;">
                                <label style="font-size:11px; font-weight:700; color:var(--text-gray);">TÊN ĐĂNG NHẬP</label>
                                <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:12px; padding:10px 14px; color:#64748b; font-size:13px; font-weight:600; outline:none; cursor:not-allowed;">
                            </div>
                            
                            <div style="display:flex; flex-direction:column; gap:4px; text-align:left;">
                                <label style="font-size:11px; font-weight:700; color:var(--text-gray);">TÊN HIỂN THỊ (NICKNAME)</label>
                                <div style="display:flex; gap:8px;">
                                    <input type="text" id="profile-nickname-input" placeholder="Nhập tên hiển thị..." value="<?= htmlspecialchars($user['nickname'] ?? '') ?>" style="flex:1; background:#fff; border:1px solid #cbd5e1; border-radius:12px; padding:10px 14px; color:#0f172a; font-size:13px; font-weight:600; outline:none;">
                                    <button type="button" onclick="updateNickname()" style="background:var(--primary-gradient); border:none; color:#fff; border-radius:12px; padding:0 16px; font-size:12px; font-weight:bold; cursor:pointer;">Lưu</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- XÁC THỰC EMAIL NHẬN THƯỞNG -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow-sm); position:relative; overflow:hidden;">
                        <!-- Hiệu ứng viền lấp lánh (trang trí đẹp) -->
                        <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #ec4899, #8b5cf6, #3b82f6, #10b981, #ec4899); background-size: 200% 100%; animation: gradientSlide 3s linear infinite;"></div>
                        <style>
                            @keyframes gradientSlide { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
                        </style>

                        <div style="font-size:14px; color:var(--text-dark); margin-bottom:12px; font-weight:800; display:flex; align-items:center; justify-content:space-between;">
                            <span style="display:flex; align-items:center; gap:6px;">
                                <i class="fas fa-envelope-open-text" style="color:#8b5cf6; font-size:16px;"></i> Xác thực Email <span style="background:#fce7f3; color:#db2777; font-size:10px; padding:2px 6px; border-radius:6px; font-weight:800; border:1px solid #fbcfe8;">+5.000đ</span>
                            </span>
                        </div>
                        
                        <div style="font-size:12px; color:#64748b; margin-bottom:15px; line-height:1.5;">
                            Xác thực Email để bảo vệ tài khoản, nhận thông báo giao dịch và nhận ngay <b style="color:#eab308;">5.000 VNĐ</b>.
                        </div>

                        <div id="email-verify-container" style="display:flex; flex-direction:column; gap:12px;">
                            <!-- Trạng thái: Chưa xác thực (Hiện form nhập email) -->
                            <div id="email-unverified-block" style="display:<?= (!empty($user['is_email_verified']) && $user['is_email_verified'] == 1) ? 'none' : 'flex' ?>; flex-direction:column; gap:12px;">
                                <div style="display:flex; flex-direction:column; gap:6px; text-align:left;">
                                    <label style="font-size:11px; font-weight:700; color:var(--text-gray);">ĐỊA CHỈ EMAIL</label>
                                    <div style="display:flex; gap:8px;">
                                        <input type="email" id="profile-email-input" placeholder="Nhập email của bạn..." value="<?= htmlspecialchars($user['email'] ?? '') ?>" style="flex:1; background:#f8fafc; border:2px solid #cbd5e1; border-radius:12px; padding:12px 14px; color:#0f172a; font-size:14px; font-weight:600; outline:none; transition:0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                                        <button type="button" id="btn-send-otp" onclick="sendEmailOTP()" style="background:linear-gradient(135deg, #8b5cf6, #6366f1); border:none; color:#fff; border-radius:12px; padding:0 20px; font-size:13px; font-weight:bold; cursor:pointer; white-space:nowrap; box-shadow:0 4px 10px rgba(99,102,241,0.3); transition:0.2s;">
                                            <i class="fas fa-paper-plane" style="margin-right:5px;"></i> Gửi OTP
                                        </button>
                                    </div>
                                </div>

                                <!-- Form nhập OTP (Ẩn mặc định, hiện ra sau khi gửi) -->
                                <div id="otp-input-block" style="display:none; flex-direction:column; gap:10px; text-align:left; background:linear-gradient(to right, #f0fdf4, #ffffff); padding:20px; border-radius:16px; border:1px solid #bbf7d0; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.05); margin-top:8px;">
                                    <div style="text-align: center; margin-bottom: 5px;">
                                        <div style="color:#10b981; font-size: 24px; margin-bottom: 5px;"><i class="fas fa-shield-check"></i></div>
                                        <label style="font-size:14px; font-weight:800; color:#064e3b; letter-spacing: 0.5px;">NHẬP MÃ OTP 6 SỐ</label>
                                        <div style="font-size:12px; color:#64748b; margin-top:4px;">Đã gửi tới <strong id="sent-otp-email-display" style="color:#334155;">email</strong>. (Nếu không thấy thư hãy vào mục thư rác, nó sẽ ở đó)</div>
                                    </div>
                                    
                                    <div style="display:flex; justify-content: center; gap:10px; margin: 10px 0;">
                                        <input type="text" id="profile-otp-input" placeholder="------" maxlength="6" style="width: 160px; background:#ffffff; border:2px dashed #10b981; border-radius:12px; padding:12px; color:#0f172a; font-size:24px; font-family: 'Courier New', Courier, monospace; font-weight:900; outline:none; text-align:center; letter-spacing:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                    </div>
                                    <div style="text-align: center;">
                                        <button type="button" id="btn-verify-otp" onclick="verifyEmailOTP()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; border-radius:12px; padding:12px 30px; font-size:14px; font-weight:bold; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.4); width: 100%; max-width: 200px; transition:0.3s;">
                                            <i class="fas fa-check-circle" style="margin-right:5px;"></i> XÁC NHẬN
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Trạng thái: Đã xác thực -->
                            <div id="email-verified-block" style="display:<?= (!empty($user['is_email_verified']) && $user['is_email_verified'] == 1) ? 'flex' : 'none' ?>; align-items:center; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:16px; padding:15px; gap:15px;">
                                <div style="width:48px; height:48px; border-radius:50%; background:#22c55e; flex-shrink:0;"></div>
                                <div style="display:flex; flex-direction:column; overflow:hidden;">
                                    <div style="font-size:12px; font-weight:800; color:#059669; text-transform:uppercase; margin-bottom:2px;">Tài khoản an toàn</div>
                                    <div id="verified-email-text" style="font-size:15px; font-weight:700; color:#064e3b; word-break:break-all;"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <div style="font-size:13px; color:var(--text-dark); margin-bottom:12px; font-weight:700;"><i class="fas fa-sliders-h" style="color:var(--primary-cyan);"></i> Cài đặt âm thanh</div>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:var(--text-dark);"><i class="fas fa-volume-up" style="color:#60c8ff; margin-right:8px;"></i> Âm thanh nút</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="profile-toggle-sfx" onchange="toggleSFX(this)">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($isLoggedIn && $user['password'] === ''): ?>
                    <!-- THIẾT LẬP MẬT KHẨU CHO GOOGLE ACCOUNT -->
                    <div style="background:#fff; border:1px solid #fca5a5; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:0 4px 10px rgba(239,68,68,0.1);">
                        <div style="font-size:14px; color:#ef4444; margin-bottom:12px; font-weight:800; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-exclamation-triangle"></i> TÀI KHOẢN CHƯA ĐẶT MẬT KHẨU
                        </div>
                        <div style="font-size:12px; color:#64748b; margin-bottom:15px; line-height:1.5;">
                            Bạn đang đăng nhập bằng Google và chưa thiết lập mật khẩu cho tài khoản này. Vui lòng thiết lập mật khẩu để có thể đăng nhập bằng tên đăng nhập <strong><?= htmlspecialchars($user['username']) ?></strong> vào lần sau.
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="password" id="profile-set-pwd-input" placeholder="Nhập mật khẩu mới..." style="flex:1; background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:10px 14px; color:#0f172a; font-size:13px; font-weight:600; outline:none;">
                            <button type="button" onclick="setProfilePassword()" style="background:var(--primary-gradient); border:none; color:#fff; border-radius:12px; padding:0 16px; font-size:12px; font-weight:bold; cursor:pointer; white-space:nowrap; height:40px;">Thiết lập</button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- LỊCH SỬ ĐĂNG NHẬP -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px; margin-bottom:20px; box-shadow:var(--shadow-sm);">
                        <div style="font-size:13px; color:var(--text-dark); margin-bottom:12px; font-weight:700;"><i class="fas fa-shield-alt" style="color:#10b981;"></i> Lịch sử đăng nhập</div>
                        <div style="max-height:180px; overflow-y:auto; padding-right:5px; display:flex; flex-direction:column; gap:8px;">
                            <?php
                            if ($isLoggedIn) {
                                $stmt_hist = $conn->prepare("SELECT ip_address, user_agent, os_info, created_at FROM login_history WHERE user_id = ? ORDER BY id DESC LIMIT 10");
                                $stmt_hist->bind_param("i", $user['id']);
                                $stmt_hist->execute();
                                $history = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);
                                
                                if (empty($history)) {
                                    echo '<div style="font-size:12px; color:#94a3b8; text-align:center; padding:10px;">Chưa có dữ liệu</div>';
                                } else {
                                    foreach ($history as $h) {
                                        echo '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:10px; font-size:11px;">';
                                        echo '<div style="display:flex; justify-content:space-between; margin-bottom:4px;">';
                                        echo '<span style="font-weight:700; color:#0f172a;">' . htmlspecialchars($h['os_info'] ?? 'Unknown OS') . '</span>';
                                        echo '<span style="color:#64748b;">' . date('H:i d/m', strtotime($h['created_at'])) . '</span>';
                                        echo '</div>';
                                        echo '<div style="color:#475569; margin-bottom:2px;">IP: <span style="font-family:monospace;">' . htmlspecialchars($h['ip_address']) . '</span></div>';
                                        echo '<div style="color:#94a3b8; font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . htmlspecialchars($h['user_agent']) . '</div>';
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:20px;">
                        <div style="font-size:14px; color:var(--text-dark); margin-bottom:16px; font-weight:700; display:flex; align-items:center; gap:8px;"><i class="fas fa-cog"></i> Cài đặt tài khoản</div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid rgba(0,0,0,0.05); margin-bottom:12px;">
                            <span style="color:var(--text-gray); font-size:13px;">Ngày tham gia</span>
                            <span style="color:var(--text-dark); font-weight:600; font-size:13px;"><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '---' ?></span>
                        </div>
                        
                        <button type="button" onclick="openModal('change-pwd-modal')" style="width:100%; text-align:left; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.08); padding:12px 16px; border-radius:12px; color:var(--text-dark); font-size:14px; font-weight:600; cursor:pointer; display:flex; justify-content:space-between; align-items:center; transition:0.2s;">
                            <span><i class="fas fa-lock" style="color:#a855f7; margin-right:8px;"></i> Đổi mật khẩu</span>
                            <i class="fas fa-chevron-right" style="font-size:10px; color:var(--text-gray);"></i>
                        </button>
                        
                        <button type="button" onclick="logout()" style="width:100%; text-align:left; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); padding:12px 16px; border-radius:12px; color:#ef4444; font-size:14px; font-weight:600; cursor:pointer; display:flex; justify-content:space-between; align-items:center; transition:0.2s; margin-top:12px;">
                            <span><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i> Đăng xuất</span>
                        </button>

                    </div>
                </div>
            </div>

            <!-- VIEW PRODUCT DETAIL (SPA PANEL) -->
            <div class="mpage" id="view-product-detail" style="display: none;">
                <div style="padding: 16px;">
                    <!-- Breadcrumb & Back Actions -->
                    <div class="pd-header-actions">
                        <div class="pd-breadcrumb">
                            <span class="pd-bc-item" onclick="switchView('home')">Trang chủ</span>
                            <i class="fas fa-chevron-right pd-bc-sep"></i>
                            <span class="pd-bc-item">Danh mục</span>
                            <i class="fas fa-chevron-right pd-bc-sep"></i>
                            <span class="pd-bc-item active" id="pd-breadcrumb-category">Category</span>
                        </div>
                        <div class="pd-actions">
                            <button type="button" class="pd-action-btn" onclick="toggleFavorite()"><i class="far fa-heart"></i> Yêu thích</button>
                            <button type="button" class="pd-action-btn" onclick="shareProduct()"><i class="fas fa-share-alt"></i> Chia sẻ</button>
                        </div>
                    </div>

                    <!-- Product Title -->
                    <h1 class="pd-title" id="pd-title">Tên sản phẩm mã nguồn</h1>

                    <!-- 2 Column Layout Grid -->
                    <div class="pd-grid-layout">
                        <!-- Left Column: Video/Image Showcase -->
                        <div class="pd-col-left">
                            <div class="pd-preview-wrapper" id="pd-preview-container">
                                <!-- Main Image or Video will be rendered here -->
                            </div>
                            <!-- Carousel Navigation dots -->
                            <div class="pd-carousel-dots" id="pd-carousel-dots">
                                <span class="pd-dot active"></span>
                                <span class="pd-dot"></span>
                                <span class="pd-dot"></span>
                            </div>
                        </div>

                        <!-- Right Column: Checkout Info & Features -->
                        <div class="pd-col-right">
                            <!-- Price Box -->
                            <div class="pd-price-box">
                                <div class="pd-price-label">Giá bán</div>
                                <div class="pd-price-wrap">
                                    <span class="pd-price-new" id="pd-price-new">0đ</span>
                                    <span class="pd-price-old" id="pd-price-old">0đ</span>
                                </div>
                            </div>

                            <!-- Features list with checkmarks -->
                            <ul class="pd-features-list" id="pd-features-list">
                                <!-- Dynamic list of features -->
                            </ul>

                            <!-- Purchase action button -->
                            <button type="button" class="pd-buy-btn" id="pd-buy-btn">
                                <i class="fas fa-shopping-cart"></i> Thanh Toán
                            </button>

                            <!-- Statistics row -->
                            <div class="pd-stats-row">
                                <div class="pd-stat-box">
                                    <span class="pd-stat-num" id="pd-stat-sales">0</span>
                                    <span class="pd-stat-lbl">Tổng số lượt bán</span>
                                </div>
                                <div class="pd-stat-box">
                                    <span class="pd-stat-num" id="pd-stat-views">0</span>
                                    <span class="pd-stat-lbl">Tổng số lượt xem</span>
                                </div>
                            </div>

                            <!-- Related Tags -->
                            <div class="pd-tags-wrap" id="pd-tags-container">
                                <!-- Dynamic hashtags -->
                            </div>
                        </div>
                    </div>

                    <!-- User Purchase Info Box (Instructions visible only after buy) -->
                    <div class="pd-instructions-card" id="pd-instructions-card" style="display: none;">
                        <div class="pd-inst-header"><i class="fas fa-info-circle"></i> HƯỚNG DẪN SỬ DỤNG CHO NGƯỜI MUA</div>
                        <div class="pd-inst-body" id="pd-instructions-body"></div>
                    </div>

                    <!-- Back to home button -->
                    <div style="margin-top: 24px; text-align: center;">
                        <button type="button" onclick="switchView('home')" class="btn btn-outline" style="border-radius:12px; padding: 10px 24px;"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</button>
                    </div>
                </div>
            </div>
            
            <!-- VIEW MY PURCHASED SOURCE CODES (SPA PANEL) -->
            <div class="mpage" id="view-my-sourcecodes" style="display: none;">
                <div style="padding: 16px;">
                    <!-- Back Bar -->
                    <div class="back-bar">
                        <button type="button" onclick="switchView('home')" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại</button>
                    </div>

                    <!-- Page Title -->
                    <div class="rank-page-title" style="background: var(--grad-rainbow); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="fa-solid fa-cart-shopping"></i> LỊCH SỬ ĐƠN HÀNG</div>
                    <p style="font-size: 13px; color: #94a3b8; margin-bottom: 24px; text-align: center; font-weight: 500;">
                        Quản lý các sản phẩm mã nguồn bạn đã sở hữu, tải xuống tập tin và xem tài liệu hướng dẫn cài đặt.
                    </p>

                    <!-- Filter Widget (Gen Z Style) -->
                    <div class="sc-history-filter-card">
                        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                            <div style="flex: 1; min-width: 180px;">
                                <input type="text" id="filter-trans-id" placeholder="Tìm kiếm tên sản phẩm hoặc mã..." class="sc-history-input" oninput="applyHistoryFilters()">
                            </div>
                            <div style="flex: 1; min-width: 180px;">
                                <select id="filter-date-range" class="sc-history-select" onchange="applyHistoryFilters()">
                                    <option value="all">Tất cả thời gian</option>
                                    <option value="today">Hôm nay</option>
                                    <option value="week">Tuần này</option>
                                    <option value="month">Tháng này</option>
                                </select>
                            </div>
                            <div class="mobile-filter-buttons">
                                <button type="button" onclick="applyHistoryFilters()" class="sc-history-btn-search"><i class="fas fa-search"></i> Tìm kiếm</button>
                                <button type="button" onclick="clearHistoryFilters()" class="sc-history-btn-clear"><i class="fas fa-undo"></i> Bỏ lọc</button>
                            </div>
                        </div>
                        
                        <div class="top-filter" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 14px; margin-top: 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label style="font-size: 11px; color: var(--text-2); font-weight: 800; letter-spacing: 0.5px;">HIỂN THỊ:</label>
                                <select id="filter-limit" onchange="applyHistoryFilters()" style="background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 4px 8px; color: var(--text); font-size: 12px; outline: none; cursor: pointer;">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- History List Table Container -->
                    <div class="sc-history-table-card">
                        <div class="table-scroll table-wrapper" style="overflow-x: auto; width: 100%;">
                            <table class="table text-nowrap table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width: 45px;">
                                            <input type="checkbox" id="check-all-purchases" style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--green);" onchange="toggleSelectAllPurchases(this)">
                                        </th>
                                        <th style="text-align: center; width: 220px;">THAO TÁC</th>
                                        <th style="text-align: center;">MÃ ĐƠN HÀNG</th>
                                        <th>SẢN PHẨM</th>
                                        <th style="text-align: center; width: 80px;">SỐ LƯỢNG</th>
                                        <th style="text-align: right; width: 120px;">THANH TOÁN</th>
                                        <th style="width: 250px;">GHI CHÚ CÁ NHÂN</th>
                                        <th style="text-align: center;">THỜI GIAN</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                    <!-- Rows will load dynamically in app.js -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                                <button type="button" id="btn-delete-selected" onclick="deleteSelectedPurchases()" class="btn-action-sm delete" style="padding: 10px 18px; border-radius: 12px;">
                                                    <i class="fa-solid fa-trash"></i> Xóa đơn hàng đã chọn
                                                </button>
                                                <div style="font-size: 13px; color: var(--text-2);">
                                                    <strong>TỔNG SỐ LƯỢNG TÀI KHOẢN:</strong> <strong style="color: var(--green);" id="stats-total-qty">0</strong>
                                                    <span style="margin: 0 8px; color: var(--border);">|</span>
                                                    <strong>TỔNG TIỀN HÀNG:</strong> <strong style="color: var(--orange);" id="stats-total-pay">0 VNĐ</strong>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Table Pagination -->
                        <div class="bottom-paginate" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; flex-wrap: wrap; gap: 12px;">
                            <p class="page-info" style="font-size: 12px; color: var(--muted);" id="history-page-info">Showing 0 of 0 Results</p>
                            <div class="pagination" id="history-pagination" style="display: flex; gap: 4px; align-items: center; white-space: nowrap; flex-wrap: nowrap;">
                                <!-- Single row pagination buttons -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            </div> <!-- CLOSE app-left-content -->

                <!-- TOOL INTERFACE -->
                <div id="tool-interface" class="tool-interface">
                    <button class="modal-close" onclick="closeTool()"><i class="fas fa-times"></i></button>
                    <h2 class="tool-target"><i class="fas fa-crosshairs"></i> MỤC TIÊU: <span id="tool-game-name"></span></h2>
                    <div class="tool-result-box"><div class="loader" id="loader"></div><div class="tool-result-text" id="result-text">?</div></div>
                    <div class="tool-result-rate" id="result-rate"></div>
                    <button class="btn-predict" id="predict-btn" onclick="predict()">BẮT ĐẦU PHÂN TÍCH</button>
                </div>
            </div>

            <?php include 'includes/side_menu.php'; ?>
        </div>
    </main>

    <?php include 'includes/bottom_nav.php'; ?>
    <?php include 'includes/modals.php'; ?>

    <div id="toast-container"></div>

    <script>
        // Truyền data từ server sang JS
        window.encryptionKeyHex = "<?= bin2hex(ENCRYPTION_KEY) ?>";
        window.encryptionIvLen = <?= ENCRYPTION_IV_LEN ?>;
        window.currentUserId = <?= json_encode($isLoggedIn ? $_SESSION['user_id'] : 0) ?>;
        window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
        // Biến tạm - sẽ bị ghi đè bởi app.js
        window.currentTargetGame = '';
        window.selectedPlanId = '';

        // showToast fallback (trước khi app.js tải)
        if (typeof window.showToast !== 'function') {
        }
        // Các hàm fallback khác để tránh lỗi ReferenceError trước khi app.js tải xong
        window.toggleSideMenu = window.toggleSideMenu || function() {
            const overlay = document.getElementById('side-menu-overlay');
            const drawer = document.getElementById('side-menu-drawer');
            if (overlay && drawer) {
                overlay.classList.toggle('active');
                drawer.classList.toggle('open');
            }
        };
        window.openTool = window.openTool || function() {};
        window.openModal = window.openModal || function(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('active');
        };
        window.closeModal = window.closeModal || function(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        };
        window.openSysNotification = window.openSysNotification || function() {};
    </script>
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
    <?php if (isset($_SESSION['pending_google_user'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if(typeof window.openModal === 'function') {
                    window.openModal('google-register-modal');
                } else {
                    document.getElementById('google-register-modal').classList.add('active');
                }
            }, 500);
        });
    </script>
    <?php endif; ?>
</body>
</html>



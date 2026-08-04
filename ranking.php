<?php
require_once __DIR__ . '/api/config.php';
$isLoggedIn = isLoggedIn();
$user = null;
if ($isLoggedIn) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bảng Xếp Hạng VIP – TOOLGAMEAI.SITE</title>
    <meta name="description" content="Bảng xếp hạng VIP hệ thống TOOLGAMEAI.SITE – Xem thứ hạng và cấp độ VIP của bạn.">
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/new_ui.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .page-wrap { max-width: 600px; margin: 0 auto; width: 100%; padding: 16px 16px 80px; }

        /* ---- Back button ---- */
        .back-bar {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 0 4px;
            margin-bottom: 8px;
        }
        .back-btn {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 8px 16px;
            color: #94a3b8; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: 0.2s; text-decoration: none;
        }
        .back-btn:hover { background: rgba(56,189,248,0.1); border-color: rgba(56,189,248,0.3); color: #38bdf8; }

        /* ---- Page title ---- */
        .rank-page-title {
            font-family: 'Oswald', sans-serif;
            font-size: 26px; font-weight: 700;
            color: #fcd34d;
            text-align: center; margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(252,211,77,0.4);
        }

        /* ---- Current VIP Panel ---- */
        .vip-panel {
            background: rgba(15,23,42,0.8);
            border: 1px solid rgba(252,211,77,0.3);
            border-radius: 24px; padding: 20px; margin-bottom: 20px;
            backdrop-filter: blur(12px);
        }
        .vip-panel-row { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .vip-panel-icon { width: 52px; height: 52px; border-radius: 50%; border: 2px solid #fcd34d; background: #000; padding: 4px; flex-shrink: 0; }
        .vip-panel-name { font-size: 22px; font-weight: 800; color: #fcd34d; }
        .vip-panel-label { font-size: 11px; color: #aaa; }
        .vip-panel-deposit { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
        .progress-labels { display: flex; justify-content: space-between; font-size: 10px; color: #ccc; margin-bottom: 4px; }
        .progress-track { background: rgba(255,255,255,0.1); border-radius: 20px; height: 14px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #fbbf24, #f59e0b); border-radius: 20px; transition: width 0.8s ease; width: 0%; }

        /* ---- Ranking List ---- */
        .rank-box {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; overflow: hidden; margin-bottom: 20px;
        }
        .rank-box-title {
            padding: 14px 20px;
            font-family: 'Oswald', sans-serif;
            font-size: 16px; font-weight: 700;
            color: #fff; background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; gap: 8px;
        }
        .rank-table-header {
            display: flex; padding: 8px 16px;
            font-size: 11px; color: rgba(255,255,255,0.5);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .rank-row {
            display: flex; align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.15s;
        }
        .rank-row:last-child { border-bottom: none; }
        .rank-row:hover { background: rgba(255,255,255,0.03); }
        .rank-row.top1 { background: rgba(252,211,77,0.05); }
        .rank-row.top2 { background: rgba(148,163,184,0.04); }
        .rank-row.top3 { background: rgba(234,88,12,0.04); }
        .rank-no { width: 50px; text-align: center; font-weight: 800; font-size: 14px; }
        .rank-user { flex: 1; font-size: 13px; font-weight: 600; }
        .rank-amount { width: 110px; text-align: right; color: #fcd34d; font-weight: 700; font-size: 13px; }
        .rank-vip-img { width: 40px; text-align: center; }
        .rank-vip-img img { width: 28px; height: 28px; }

        /* ---- VIP Guide ---- */
        .guide-box {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; padding: 20px; margin-bottom: 20px;
        }
        .guide-title {
            font-size: 13px; font-weight: 800; color: #fcd34d;
            margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }
        .guide-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        @media (min-width: 400px) { .guide-grid { grid-template-columns: repeat(4, 1fr); } }
        .guide-item {
            background: rgba(0,0,0,0.3);
            border-radius: 14px; padding: 10px 6px;
            text-align: center;
        }
        .guide-item img { width: 36px; height: 36px; margin-bottom: 4px; }
        .guide-item-name { font-size: 12px; font-weight: 700; color: #fcd34d; }
        .guide-item-amount { font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 2px; }

        /* Loading spinner */
        .loading-spin { text-align: center; padding: 40px; color: rgba(255,255,255,0.5); }
        .loading-spin i { font-size: 24px; margin-bottom: 8px; display: block; }

        /* Not logged in notice */
        .not-logged-notice {
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
            border-radius: 16px; padding: 20px; text-align: center;
            margin-bottom: 20px;
        }
        .not-logged-notice i { font-size: 32px; color: #ef4444; margin-bottom: 8px; display: block; }
        .not-logged-notice p { font-size: 13px; color: #cbd5e1; }
        .not-logged-notice a { color: #38bdf8; font-weight: 700; text-decoration: underline; }
    </style>
</head>
<body data-csrf="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <div class="bg-hex"></div>
    <div class="bg-orb-tl"></div>
    <div class="bg-orb-br"></div>

    <?php include 'includes/header.php'; ?>

    <main>
        <div class="page-wrap">
            <div class="back-bar">
                <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>

            <div class="rank-page-title">🏆 BẢNG XẾP HẠNG VIP</div>

            <?php if ($isLoggedIn): ?>
            <!-- Current VIP Panel -->
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
                <p>Vui lòng <a href="index.php">đăng nhập</a> để xem hạng VIP của bạn.</p>
            </div>
            <?php endif; ?>

            <!-- Ranking List -->
            <div class="rank-box">
                <div class="rank-box-title"><i class="fas fa-medal" style="color:#fcd34d;"></i> TOP NGƯỜI CHƠI NẠP NHIỀU NHẤT</div>
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
    </main>

    <div id="toast-container"></div>

    <script>
        window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
        window.currentUserId = <?= json_encode($isLoggedIn ? $_SESSION['user_id'] : 0) ?>;
    </script>
    <script src="assets/app.js?v=<?= time() ?>"></script>
    <script>
        // Load ranking on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadRankingData();
        });

        // Override loadRankingData to work on this standalone page
        async function loadRankingData() {
            try {
                const resp = await fetch('api/ranking.php');
                const data = await resp.json();

                if (data.status !== 'success') {
                    document.getElementById('ranking-list-container').innerHTML = '<div style="padding:30px;color:#ef4444;text-align:center;">Vui lòng đăng nhập để xem xếp hạng.</div>';
                    return;
                }

                const cur = data.data.current_user;

                // Current user VIP panel
                const nameEl = document.getElementById('current-vip-name');
                const depEl = document.getElementById('current-total-deposit');
                const iconEl = document.getElementById('current-vip-icon');
                if (nameEl) nameEl.innerText = cur.vip_name;
                if (depEl) depEl.innerHTML = 'Tổng nạp: ' + cur.total_deposit.toLocaleString('vi-VN') + 'đ';
                if (iconEl && cur.vip_icon) iconEl.src = cur.vip_icon;

                // Progress bar
                const bar = document.getElementById('vip-progress-bar');
                const labelStart = document.getElementById('bar-label-start');
                const labelEnd = document.getElementById('bar-label-end');
                if (cur.next_milestone) {
                    const milestones = [40000, 95000, 150000, 200000, 300000, 425000, 500000, 650000];
                    let prev = 0;
                    for (let m of milestones) { if (m < cur.total_deposit) prev = m; else break; }
                    const next = cur.next_milestone.amount;
                    const percent = (next - prev) > 0 ? ((cur.total_deposit - prev) / (next - prev)) * 100 : 0;
                    if (bar) bar.style.width = Math.min(percent, 100) + '%';
                    if (labelStart) labelStart.innerText = prev.toLocaleString('vi-VN') + 'đ';
                    if (labelEnd) labelEnd.innerText = next.toLocaleString('vi-VN') + 'đ';
                } else {
                    if (bar) bar.style.width = '100%';
                    if (labelEnd) labelEnd.innerText = 'MAX VIP';
                }

                // Ranking rows
                const container = document.getElementById('ranking-list-container');
                if (!data.data.top_users.length) {
                    container.innerHTML = '<div style="padding:30px;text-align:center;color:rgba(255,255,255,0.5);">Chưa có dữ liệu</div>';
                } else {
                    container.innerHTML = data.data.top_users.map(u => {
                        const rankIcon = u.rank === 1 ? '🥇' : (u.rank === 2 ? '🥈' : (u.rank === 3 ? '🥉' : '#' + u.rank));
                        const cls = u.rank === 1 ? 'top1' : (u.rank === 2 ? 'top2' : (u.rank === 3 ? 'top3' : ''));
                        return `<div class="rank-row ${cls}">
                            <div class="rank-no">${rankIcon}</div>
                            <div class="rank-user">${u.username}</div>
                            <div class="rank-amount">${u.total_deposit.toLocaleString('vi-VN')}đ</div>
                            <div class="rank-vip-img">${u.vip_icon ? `<img src="${u.vip_icon}" alt="VIP">` : '—'}</div>
                        </div>`;
                    }).join('');
                }

                // VIP guide
                const guide = document.getElementById('vip-guide-list');
                if (guide && data.data.guide) {
                    guide.innerHTML = data.data.guide.map(g => `
                        <div class="guide-item">
                            <img src="${g.icon}" alt="${g.vip}">
                            <div class="guide-item-name">${g.vip}</div>
                            <div class="guide-item-amount">${g.amount.toLocaleString('vi-VN')}đ</div>
                        </div>`).join('');
                }
            } catch (err) {
                console.error(err);
                document.getElementById('ranking-list-container').innerHTML = '<div style="padding:30px;color:#ef4444;text-align:center;">Lỗi tải dữ liệu</div>';
            }
        }
    </script>
</body>
</html>

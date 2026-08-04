<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$role = $currentAdmin['role'];

// Load settings
$settings = json_decode(file_get_contents(__DIR__.'/settings.json'), true) ?: [];

// Thống kê
$res = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COALESCE(SUM(amount),0) as total_deposit FROM transactions WHERE type='deposit' AND status='completed'");
$stats['total_deposit'] = $res->fetch_assoc()['total_deposit'];

$res = $conn->query("SELECT COUNT(*) as total_active_keys FROM user_keys WHERE expires_at > NOW()");
$stats['total_active_keys'] = $res->fetch_assoc()['total_active_keys'];

$res = $conn->query("SELECT COALESCE(SUM(amount),0) as revenue_keys FROM transactions WHERE type='buy_key' AND status='completed'");
$stats['revenue_keys'] = $res->fetch_assoc()['revenue_keys'];

$recentTxs = $conn->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$topDepositors = $conn->query("SELECT u.id, u.username, COALESCE(SUM(t.amount),0) as total_deposit FROM users u LEFT JOIN transactions t ON u.id = t.user_id AND t.type='deposit' AND t.status='completed' GROUP BY u.id ORDER BY total_deposit DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>
<!-- Sidebar -->
<div class="sidebar" id="adminSidebar"><?php include 'includes/sidebar.php'; ?></div>
<!-- Header -->
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
<!-- Main Content -->
<div class="main-content">
    <?php if (!empty($settings['maintenance_mode'])): ?>
    <div class="maintenance-banner">
        <i class="fas fa-tools"></i>
        Hệ thống đang BẢO TRÌ — Người dùng không thể truy cập trang chủ
        <a href="settings.php" style="margin-left:auto;color:inherit;font-weight:700;text-decoration:underline;">Tắt bảo trì</a>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-user-friends"></i></div>
            <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-label">Tổng người dùng</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-coins"></i></div>
            <div class="stat-value"><?= number_format($stats['total_deposit']) ?>đ</div>
            <div class="stat-label">Tổng đã nạp</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-key"></i></div>
            <div class="stat-value"><?= number_format($stats['total_active_keys']) ?></div>
            <div class="stat-label">Key đang hoạt động</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value"><?= number_format($stats['revenue_keys']) ?>đ</div>
            <div class="stat-label">Doanh thu Key</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title"><i class="fas fa-medal"></i> Top 5 người nạp nhiều nhất</div>
            </div>
            <div class="data-card-body">
                <?php foreach($topDepositors as $idx=>$u): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span style="width:26px;height:26px;border-radius:50%;background:<?= $idx===0?'#fcd34d':($idx===1?'#94a3b8':($idx===2?'#f97316':'rgba(255,255,255,0.1)')) ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:#000;"><?= $idx+1 ?></span>
                        <?= htmlspecialchars($u['username']) ?>
                    </span>
                    <span class="badge badge-success"><?= number_format($u['total_deposit']) ?>đ</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title"><i class="fas fa-chart-area"></i> Doanh thu 7 ngày</div>
            </div>
            <div class="data-card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title"><i class="fas fa-history"></i> Giao dịch gần đây</div>
            <a href="transactions.php" class="btn btn-sm btn-outline">Xem tất cả</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>ID</th><th>User</th><th>Loại</th><th>Số tiền</th><th>Mô tả</th><th>Trạng thái</th><th>Thời gian</th></tr></thead>
                <tbody>
                <?php foreach($recentTxs as $tx): ?>
                <tr>
                    <td><span class="badge badge-info">#<?= $tx['id'] ?></span></td>
                    <td><b><?= htmlspecialchars($tx['username']) ?></b></td>
                    <td><?= $tx['type']==='deposit'?'<span class="badge badge-success">Nạp tiền</span>':'<span class="badge badge-purple">Mua Key</span>' ?></td>
                    <td style="font-weight:700;color:#fcd34d;"><?= number_format($tx['amount']) ?>đ</td>
                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($tx['description']) ?></td>
                    <td><?= $tx['status']==='completed'?'<span class="badge badge-success">Hoàn thành</span>':'<span class="badge badge-warning">Chờ</span>' ?></td>
                    <td><?= date('d/m H:i', strtotime($tx['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="toast-container"></div>

<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
// Revenue Chart
fetch('ajax/dashboard_stats.php')
    .then(r=>r.json())
    .then(data=>{
        new Chart(document.getElementById('revenueChart'), {
            type:'line',
            data:{labels:data.labels, datasets:[{label:'Doanh thu (đ)',data:data.revenues,borderColor:'#38bdf8',tension:0.4,fill:true,backgroundColor:'rgba(56,189,248,0.07)',pointBackgroundColor:'#38bdf8',pointRadius:4}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8',font:{family:'Poppins'}}}},scales:{y:{ticks:{callback:v=>v.toLocaleString('vi-VN')+'đ',color:'#94a3b8',font:{family:'Poppins',size:11}},grid:{color:'rgba(255,255,255,0.04)'}},x:{ticks:{color:'#94a3b8',font:{family:'Poppins',size:11}},grid:{display:false}}}}
        });
    }).catch(()=>{});
</script>
</body>
</html>

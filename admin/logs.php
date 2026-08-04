<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$pageTitle = 'Nhật ký';
$page = max(1,(int)($_GET['page']??1));
$limit = 50;
$offset = ($page-1)*$limit;
$logs = $conn->query("SELECT l.*, a.username FROM admin_logs l JOIN admin_users a ON l.admin_id = a.id ORDER BY l.id DESC LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
$total = $conn->query("SELECT COUNT(*) as cnt FROM admin_logs")->fetch_assoc()['cnt'];
$totalPages = ceil($total/$limit);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký | Admin Panel</title>
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
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title"><i class="fas fa-history"></i> Nhật ký hành động Admin <span style="font-size:12px;color:#64748b;font-weight:400;">(<?= number_format($total) ?> bản ghi)</span></div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>ID</th><th>Admin</th><th>Hành động</th><th>IP</th><th>Thời gian</th></tr></thead>
                <tbody>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td><span class="badge badge-info">#<?= $log['id'] ?></span></td>
                    <td><b><?= htmlspecialchars($log['username']) ?></b></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><code style="font-size:11px;color:#94a3b8;"><?= $log['ip'] ?></code></td>
                    <td style="font-size:12px;color:#64748b;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>" class="<?=$i==$page?'current':''?>"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<div id="toast-container"></div>
<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
</body>
</html>

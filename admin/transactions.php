<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$pageTitle = 'Giao dịch';
$search = trim($_GET['search'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$page = max(1,(int)($_GET['page']??1));
$limit = 30;
$offset = ($page-1)*$limit;

$conditions = [];
$params = [];
$types = '';
if ($search) {
    $conditions[] = "(t.description LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
    $types .= 'ss';
}
if ($typeFilter) {
    $conditions[] = "t.type = ?";
    $params[] = $typeFilter;
    $types .= 's';
}
$where = $conditions ? 'WHERE '.implode(' AND ', $conditions) : '';
$sql = "SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id $where ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$txs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$countSql = "SELECT COUNT(*) as cnt FROM transactions t JOIN users u ON t.user_id = u.id $where";
$cStmt = $conn->prepare($countSql);
if ($params) $cStmt->bind_param($types, ...$params);
$cStmt->execute();
$total = $cStmt->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($total/$limit);

// Quick stats
$totalDeposit = $conn->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE type='deposit' AND status='completed'")->fetch_assoc()['s'];
$totalKeys = $conn->query("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE type='buy_key' AND status='completed'")->fetch_assoc()['s'];
$pendingCount = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE status='pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giao dịch | Admin Panel</title>
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
    <!-- Quick Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-coins"></i></div>
            <div class="stat-value"><?= number_format($totalDeposit) ?>đ</div>
            <div class="stat-label">Tổng nạp (hoàn thành)</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-key"></i></div>
            <div class="stat-value"><?= number_format($totalKeys) ?>đ</div>
            <div class="stat-label">Tổng mua Key</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?= number_format($pendingCount) ?></div>
            <div class="stat-label">Đang chờ xử lý</div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title"><i class="fas fa-exchange-alt"></i> Lịch sử giao dịch <span style="font-size:12px;color:#64748b;font-weight:400;">(<?= number_format($total) ?> giao dịch)</span></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <!-- Type Filter -->
                <div style="display:flex;gap:6px;">
                    <a href="?search=<?=urlencode($search)?>" class="btn btn-sm <?= !$typeFilter?'btn-primary':'btn-outline' ?>">Tất cả</a>
                    <a href="?type=deposit&search=<?=urlencode($search)?>" class="btn btn-sm <?= $typeFilter==='deposit'?'btn-primary':'btn-outline' ?>">Nạp tiền</a>
                    <a href="?type=buy_key&search=<?=urlencode($search)?>" class="btn btn-sm <?= $typeFilter==='buy_key'?'btn-primary':'btn-outline' ?>">Mua Key</a>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Mã GD, tên user..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>ID</th><th>User</th><th>Loại</th><th>Số tiền</th><th>Mô tả</th><th>Trạng thái</th><th>Thời gian</th></tr></thead>
                <tbody>
                <?php foreach($txs as $t): ?>
                <tr>
                    <td><span class="badge badge-info">#<?= $t['id'] ?></span></td>
                    <td><b><?= htmlspecialchars($t['username']) ?></b></td>
                    <td>
                        <?php if($t['type']==='deposit'): ?>
                        <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Nạp tiền</span>
                        <?php elseif($t['type']==='buy_key'): ?>
                        <span class="badge badge-purple"><i class="fas fa-key"></i> Mua Key</span>
                        <?php elseif($t['type']==='bonus'): ?>
                        <span class="badge badge-warning"><i class="fas fa-gift"></i> Bonus</span>
                        <?php else: ?>
                        <span class="badge badge-info"><?= $t['type'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:700;color:<?= $t['type']==='deposit'?'#22c55e':'#fcd34d' ?>;"><?= number_format($t['amount']) ?>đ</td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($t['description']) ?>">
                        <code style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($t['description']) ?></code>
                    </td>
                    <td><?= $t['status']==='completed'?'<span class="badge badge-success">Hoàn thành</span>':($t['status']==='pending'?'<span class="badge badge-warning">Đang chờ</span>':'<span class="badge badge-danger">Thất bại</span>') ?></td>
                    <td style="font-size:12px;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if($totalPages>1): ?>
        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>&type=<?=urlencode($typeFilter)?>&search=<?=urlencode($search)?>" class="<?=$i==$page?'current':''?>"><?=$i?></a>
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
document.getElementById('searchInput').addEventListener('keypress',function(e){ if(e.key==='Enter') location.href='?search='+encodeURIComponent(this.value)+'&type=<?=urlencode($typeFilter)?>'; });
</script>
</body>
</html>

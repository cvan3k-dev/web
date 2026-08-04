<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$isSuper = ($currentAdmin['role'] === 'superadmin');
$pageTitle = 'Quản lý Key';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = "WHERE k.key_code LIKE ? OR u.username LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tổng số key
$countRes = $conn->prepare("SELECT COUNT(*) as cnt FROM user_keys k JOIN users u ON k.user_id = u.id $where");
if ($search) {
    $countRes->bind_param("ss", $params[0], $params[1]);
}
$countRes->execute();
$total = $countRes->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($total / $limit);

// Lấy danh sách key
$sql = "SELECT k.*, u.username FROM user_keys k JOIN users u ON k.user_id = u.id $where ORDER BY k.id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($search) {
    $stmt->bind_param("ssii", $params[0], $params[1], $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$keys = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Key | Admin Panel</title>
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
            <div class="data-card-title"><i class="fas fa-key"></i> Quản lý Key VIP</div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm key hoặc username..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button class="btn btn-primary" onclick="openModal('addKeyModal')"><i class="fas fa-plus"></i> Tạo Key</button>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Key code</th>
                    <th>Hết hạn</th>
                    <th>Fingerprint</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keys)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 20px; color: var(--text-muted);">Không tìm thấy Key nào</td>
                </tr>
                <?php else: ?>
                <?php foreach($keys as $k): ?>
                <tr>
                    <td><?= $k['id'] ?></td>
                    <td><?= htmlspecialchars($k['username']) ?></td>
                    <td><code><?= $k['key_code'] ?></code></td>
                    <td><?= date('d/m/Y H:i', strtotime($k['expires_at'])) ?></td>
                    <td>
                        <?= $k['fingerprint'] ? '<span class="badge badge-success" title="'.htmlspecialchars($k['fingerprint']).'">'.substr($k['fingerprint'], 0, 10).'...</span>' : '<span class="badge badge-warning">Chưa kích hoạt</span>' ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($k['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="deleteKey(<?= $k['id'] ?>)"><i class="fas fa-trash"></i> Xóa</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i==$page?'current':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

<!-- Add Key Modal -->
<div id="addKeyModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-key"></i> Tạo Key VIP thủ công</div>
        <div class="form-group">
            <label class="form-label">Tên tài khoản (username)</label>
            <input type="text" class="form-control" id="keyUsername" placeholder="Nhập username người dùng..." required>
        </div>
        <div class="form-group">
            <label class="form-label">Thời hạn Key</label>
            <select class="form-control" id="keyDuration">
                <option value="1hour">Gói 1 Giờ</option>
                <option value="1day">Gói 1 Ngày</option>
                <option value="3day">Gói 3 Ngày</option>
                <option value="7day">Gói 1 Tuần</option>
                <option value="30day">Gói 1 Tháng</option>
                <option value="forever">Vĩnh viễn (9999 Ngày)</option>
            </select>
        </div>
        <div class="modal-actions">
            <button class="btn btn-primary" onclick="createKey()">Tạo Key</button>
            <button class="btn btn-outline" onclick="closeModal('addKeyModal')">Hủy</button>
        </div>
    </div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('show');
}
function closeModal(id){
    document.getElementById(id).classList.remove('show');
}
function deleteKey(id){
    if(confirm('Bạn chắc chắn muốn xóa key này?')) {
        fetch('ajax/key_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(d => {
            if(d.status === 'success') {
                location.reload();
            } else {
                alert(d.message);
            }
        });
    }
}
function createKey() {
    let username = document.getElementById('keyUsername').value.trim();
    let duration = document.getElementById('keyDuration').value;
    if(!username) { alert('Vui lòng nhập username'); return; }
    
    fetch('ajax/create_key.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&duration=${duration}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            alert('Đã tạo thành công Key: ' + d.data.key);
            location.reload();
        } else {
            alert(d.message);
        }
    })
    .catch(() => alert('Lỗi kết nối máy chủ'));
}
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        window.location.href = '?search=' + encodeURIComponent(this.value);
    }
});
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
<div id="toast-container"></div>
</div>
</body>
</html>

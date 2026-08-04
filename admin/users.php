<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$isSuper = ($currentAdmin['role'] === 'superadmin');
$pageTitle = 'Người dùng';
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page-1)*$limit;
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = "WHERE username LIKE ?";
    $params[] = "%$search%";
}
$totalRes = $conn->prepare("SELECT COUNT(*) as cnt FROM users $where");
if ($search) $totalRes->bind_param("s", $params[0]);
$totalRes->execute();
$totalUsers = $totalRes->get_result()->fetch_assoc()['cnt'];
$totalPages = ceil($totalUsers/$limit);
$sql = "SELECT id, username, balance, created_at FROM users $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($search) $stmt->bind_param("s", $params[0]);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Người dùng | Admin Panel</title>
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
            <div class="data-card-title"><i class="fas fa-users"></i> Quản lý người dùng <span style="font-size:12px;color:#64748b;font-weight:400;">(<?= number_format($totalUsers) ?> users)</span></div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm username..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>ID</th><th>Username</th><th>Số dư</th><th>Ngày đăng ký</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><span class="badge badge-info">#<?= $u['id'] ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#38bdf8,#818cf8);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#fff;flex-shrink:0;">
                                <?= strtoupper(substr($u['username'],0,1)) ?>
                            </div>
                            <b><?= htmlspecialchars($u['username']) ?></b>
                        </div>
                    </td>
                    <td style="font-weight:700;color:#22c55e;"><?= number_format($u['balance']) ?>đ</td>
                    <td style="color:#94a3b8;font-size:12px;"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-sm btn-primary" onclick="openEditUser(<?=$u['id']?>, '<?=htmlspecialchars(addslashes($u['username']))?>', <?=$u['balance']?>)">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <?php if($isSuper): ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?=$u['id']?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if($totalPages>1): ?>
        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>&search=<?=urlencode($search)?>" class="<?=$i==$page?'current':''?>"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-user-edit"></i> Sửa thông tin user</div>
        <input type="hidden" id="editUserId">
        <div class="form-group">
            <label class="form-label">Username</label>
            <input class="form-control" id="editUsername" disabled style="opacity:0.6;">
        </div>
        <div class="form-group">
            <label class="form-label">Số dư (đ)</label>
            <input type="number" class="form-control" id="editBalance" min="0">
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('editUserModal')">Hủy</button>
            <button class="btn btn-primary" onclick="updateUser()"><i class="fas fa-save"></i> Cập nhật</button>
        </div>
    </div>
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
function openEditUser(id, username, balance) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editBalance').value = balance;
    document.getElementById('editUserModal').classList.add('show');
}
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function updateUser() {
    const id = document.getElementById('editUserId').value;
    const balance = document.getElementById('editBalance').value;
    fetch('ajax/user_action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=update_balance&user_id=${id}&balance=${balance}`})
        .then(r=>r.json()).then(d=>{
            if(d.status==='success'){showToast('Đã cập nhật số dư!');closeModal('editUserModal');setTimeout(()=>location.reload(),1200);}
            else showToast(d.message||'Lỗi!','error');
        });
}
function deleteUser(id) {
    if(!confirm('Xóa người dùng này? Hành động không thể hoàn tác!')) return;
    fetch('ajax/user_action.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_user&user_id=${id}`})
        .then(r=>r.json()).then(d=>{
            if(d.status==='success'){showToast('Đã xóa người dùng!');setTimeout(()=>location.reload(),1200);}
            else showToast(d.message||'Lỗi!','error');
        });
}
document.getElementById('searchInput').addEventListener('keypress',function(e){ if(e.key==='Enter') location.href='?search='+encodeURIComponent(this.value); });
// Click outside modal to close
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});});
</script>
</div>
</body>
</html>

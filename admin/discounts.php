
<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$isSuper = ($currentAdmin['role'] === 'superadmin');
$pageTitle = 'Mã giảm giá';
// Xử lý thêm/sửa/xóa
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    checkCSRF();
    $action = $_POST['action'];
    if($action === 'add' && $isSuper){
        $code = strtoupper(trim($_POST['code']));
        $amount = (int)$_POST['amount'];
        $type = $_POST['type'];
        $expires_at = $_POST['expires_at'] ?: null;
        $usage_limit = (int)$_POST['usage_limit'] ?: null;
        $stmt = $conn->prepare("INSERT INTO discounts (code, amount, type, expires_at, usage_limit) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sisii", $code, $amount, $type, $expires_at, $usage_limit);
        $stmt->execute();
        logAdminAction("Thêm mã giảm giá $code");
        header('Location: discounts.php?msg=added'); exit;
    }elseif($action === 'toggle' && $isSuper){
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE discounts SET active = NOT active WHERE id=?");
        $stmt->bind_param("i", $id); $stmt->execute();
        header('Location: discounts.php'); exit;
    }elseif($action === 'delete' && $isSuper){
        $id = (int)$_POST['id'];
        $conn->prepare("DELETE FROM discounts WHERE id=?")->bind_param("i",$id)->execute();
        header('Location: discounts.php'); exit;
    }
}
$discounts = $conn->query("SELECT * FROM discounts ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã giảm giá | Admin Panel</title>
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
            <div class="data-card-title"><i class="fas fa-tag"></i> Mã giảm giá</div>
            <?php if($isSuper): ?><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm mới</button><?php endif; ?>
        </div>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Mã</th><th>Giá trị</th><th>Loại</th><th>Hạn sử dụng</th><th>Giới hạn</th><th>Đã dùng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php foreach($discounts as $d): ?>
            <tr>
                <td><?=$d['id']?></td>
                <td><?=htmlspecialchars($d['code'])?></td>
                <td><?= $d['type']==='percent' ? $d['amount'].'%' : number_format($d['amount']).'đ' ?></td>
                <td><?= $d['type']==='percent' ? 'Phần trăm' : 'Cố định' ?></td>
                <td><?= $d['expires_at'] ? date('d/m/Y',strtotime($d['expires_at'])) : 'Vĩnh viễn' ?></td>
                <td><?= $d['usage_limit'] ?? '∞' ?></td>
                <td><?= $d['used_count'] ?></td>
                <td><?= $d['active'] ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Tắt</span>' ?></td>
                <td>
                    <?php if($isSuper): ?>
                    <button class="btn btn-sm btn-warning" onclick="toggleDiscount(<?=$d['id']?>)"><i class="fas fa-power-off"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDiscount(<?=$d['id']?>)"><i class="fas fa-trash"></i></button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

<div id="addModal" class="modal-overlay"><div class="modal-box"><form method="post"><input type="hidden" name="action" value="add"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"><div class="modal-title">Thêm mã giảm giá</div><div class="form-group"><label class="form-label">Mã code</label><input name="code" class="form-control" required></div><div class="form-group"><label class="form-label">Giá trị</label><input name="amount" type="number" class="form-control" required></div><div class="form-group"><label class="form-label">Loại</label><select name="type" class="form-control"><option value="percent">Phần trăm (%)</option><option value="fixed">Cố định (VNĐ)</option></select></div><div class="form-group"><label class="form-label">Hết hạn (YYYY-MM-DD HH:MM:SS)</label><input name="expires_at" class="form-control" placeholder="Để trống nếu vĩnh viễn"></div><div class="form-group"><label class="form-label">Giới hạn sử dụng</label><input name="usage_limit" type="number" class="form-control" placeholder="Để trống không giới hạn"></div><div class="modal-actions"><button type="submit" class="btn btn-primary">Thêm</button><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Hủy</button></div></form></div></div>
<script>function openAddModal(){document.getElementById('addModal').classList.add('show');}function closeModal(id){document.getElementById(id).classList.remove('show');}function toggleAdminSidebar(){document.getElementById('adminSidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show');}function toggleDiscount(id){if(confirm('Bật/tắt mã này?')){ let f=document.createElement('form');f.method='post';f.innerHTML='<input name="action" value="toggle"><input name="id" value="'+id+'"><input name="csrf_token" value="<?=$_SESSION['csrf_token']?>">'; document.body.appendChild(f);f.submit();}}function deleteDiscount(id){if(confirm('Xóa mã giảm giá?')){ let f=document.createElement('form');f.method='post';f.innerHTML='<input name="action" value="delete"><input name="id" value="'+id+'"><input name="csrf_token" value="<?=$_SESSION['csrf_token']?>">'; document.body.appendChild(f);f.submit();}}</script>
<div id="toast-container"></div>
</div>
</body>
</html>

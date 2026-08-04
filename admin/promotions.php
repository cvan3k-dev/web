
<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$isSuper = ($currentAdmin['role'] === 'superadmin');
$pageTitle = 'Khuyến mãi';
if($_SERVER['REQUEST_METHOD'] === 'POST' && $isSuper){
    checkCSRF();
    $action = $_POST['action'];
    if($action === 'add'){
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $start_at = $_POST['start_at'];
        $end_at = $_POST['end_at'];
        $banner_url = $_POST['banner_url'];
        $stmt = $conn->prepare("INSERT INTO promotions (title, description, start_at, end_at, banner_url) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $title, $desc, $start_at, $end_at, $banner_url);
        $stmt->execute();
        header('Location: promotions.php?msg=added'); exit;
    }elseif($action === 'toggle'){
        $id = (int)$_POST['id'];
        $conn->prepare("UPDATE promotions SET active = NOT active WHERE id=?")->bind_param("i",$id)->execute();
        header('Location: promotions.php'); exit;
    }elseif($action === 'delete'){
        $conn->prepare("DELETE FROM promotions WHERE id=?")->bind_param("i",(int)$_POST['id'])->execute();
        header('Location: promotions.php'); exit;
    }
}
$promos = $conn->query("SELECT * FROM promotions ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khuyến mãi | Admin Panel</title>
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
<div class="main-content"><div class="data-card"><div class="data-card-header"><div class="data-card-title"><i class="fas fa-gift"></i> Khuyến mãi</div><?php if($isSuper): ?><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm</button><?php endif; ?></div><table class="data-table"><thead><tr><th>ID</th><th>Tiêu đề</th><th>Nội dung</th><th>Banner</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php foreach($promos as $p): ?><tr><td><?=$p['id']?></td><td><?=htmlspecialchars($p['title'])?></td><td><?=htmlspecialchars($p['description'])?></td><td><?=$p['banner_url']? '<img src="'.$p['banner_url'].'" style="height:40px;">' : '-'?></td><td><?=date('d/m/Y',strtotime($p['start_at']))?> → <?=date('d/m/Y',strtotime($p['end_at']))?></td><td><?=$p['active']?'<span class="badge badge-success">Bật</span>':'<span class="badge badge-danger">Tắt</span>'?></td><td><?php if($isSuper): ?><button class="btn btn-sm btn-warning" onclick="togglePromo(<?=$p['id']?>)"><i class="fas fa-power-off"></i></button><button class="btn btn-sm btn-danger" onclick="deletePromo(<?=$p['id']?>)"><i class="fas fa-trash"></i></button><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>

<div id="addModal" class="modal-overlay"><div class="modal-box"><form method="post"><input type="hidden" name="action" value="add"><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"><div class="modal-title">Thêm khuyến mãi</div><div class="form-group"><label>Tiêu đề</label><input name="title" class="form-control" required></div><div class="form-group"><label>Mô tả</label><textarea name="description" class="form-control" required></textarea></div><div class="form-group"><label>Banner URL</label><input name="banner_url" class="form-control" placeholder="https://..."></div><div class="form-group"><label>Bắt đầu</label><input type="datetime-local" name="start_at" class="form-control" required></div><div class="form-group"><label>Kết thúc</label><input type="datetime-local" name="end_at" class="form-control" required></div><div class="modal-actions"><button type="submit" class="btn btn-primary">Thêm</button><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Hủy</button></div></form></div></div>
<script>function openAddModal(){document.getElementById('addModal').classList.add('show');}function closeModal(id){document.getElementById(id).classList.remove('show');}function toggleAdminSidebar(){document.getElementById('adminSidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show');}function togglePromo(id){if(confirm('Bật/tắt?')){ let f=document.createElement('form');f.method='post';f.innerHTML='<input name="action" value="toggle"><input name="id" value="'+id+'"><input name="csrf_token" value="<?=$_SESSION['csrf_token']?>">'; document.body.appendChild(f);f.submit();}}function deletePromo(id){if(confirm('Xóa?')){ let f=document.createElement('form');f.method='post';f.innerHTML='<input name="action" value="delete"><input name="id" value="'+id+'"><input name="csrf_token" value="<?=$_SESSION['csrf_token']?>">'; document.body.appendChild(f);f.submit();}}</script>
</div>
</body>
</html>

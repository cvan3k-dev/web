<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$pageTitle = 'Thông báo';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $action = $_POST['action'];
    if ($action === 'add') {
        $title = trim($_POST['title']);
        $message = trim($_POST['message']);
        $target_user_id = !empty($_POST['target_user_id']) ? intval($_POST['target_user_id']) : null;
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, target_user_id) VALUES (?,?,?)");
        $stmt->bind_param("ssi", $title, $message, $target_user_id);
        $stmt->execute();
        logAdminAction("Thêm thông báo: $title");
        header('Location: notifications.php?msg=added'); exit;
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->prepare("DELETE FROM notifications WHERE id=?")->bind_param("i",$id)->execute();
        header('Location: notifications.php?msg=deleted'); exit;
    }
}
$notifs = $conn->query("SELECT n.*, u.username as target_name FROM notifications n LEFT JOIN users u ON n.target_user_id = u.id ORDER BY n.id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo | Admin Panel</title>
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
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?= $_GET['msg']==='added'?'success':'info' ?>" id="flashMsg">
        <i class="fas fa-check-circle"></i>
        <?= $_GET['msg']==='added'?'Đã thêm thông báo thành công!':'Đã xóa thông báo.' ?>
    </div>
    <?php endif; ?>

    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title"><i class="fas fa-bell"></i> Thông báo hệ thống <span style="font-size:12px;color:#64748b;font-weight:400;">(<?= count($notifs) ?> thông báo)</span></div>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('show')">
                <i class="fas fa-plus"></i> Thêm thông báo
            </button>
        </div>
        <?php if (empty($notifs)): ?>
        <div style="text-align:center;padding:60px 20px;color:#64748b;">
            <i class="fas fa-bell-slash" style="font-size:48px;margin-bottom:16px;opacity:0.3;display:block;"></i>
            Chưa có thông báo nào
        </div>
        <?php else: ?>
        <div style="padding:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
            <?php foreach($notifs as $n): ?>
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:16px;position:relative;">
                <div style="position:absolute;top:12px;right:12px;">
                    <form method="post" style="display:inline;" onsubmit="return confirm('Xóa thông báo này?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" style="width:30px;height:30px;padding:0;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.2);display:flex;align-items:center;justify-content:center;color:#38bdf8;flex-shrink:0;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:14px;color:#fff;"><?= htmlspecialchars($n['title']) ?></div>
                        <div style="font-size:11px;color:#64748b;"><?= $n['target_user_id'] ? '<i class="fas fa-user"></i> '.htmlspecialchars($n['target_name']) : '<i class="fas fa-globe"></i> Tất cả người dùng' ?></div>
                    </div>
                </div>
                <p style="font-size:13px;color:#94a3b8;line-height:1.6;margin-bottom:10px;"><?= htmlspecialchars($n['message']) ?></p>
                <div style="font-size:11px;color:#475569;"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-title"><i class="fas fa-bell"></i> Thêm thông báo mới</div>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label class="form-label">Tiêu đề</label>
                <input name="title" class="form-control" required placeholder="Nhập tiêu đề thông báo...">
            </div>
            <div class="form-group">
                <label class="form-label">Nội dung</label>
                <textarea name="message" class="form-control" required rows="4" placeholder="Nội dung thông báo..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">User ID mục tiêu</label>
                <input name="target_user_id" class="form-control" type="number" min="1" placeholder="Để trống = gửi tất cả người dùng">
                <div class="form-hint">Nhập ID user nếu muốn gửi cho 1 người cụ thể</div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('show')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi thông báo</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-container"></div>
<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});});
<?php if (isset($_GET['msg'])): ?>
setTimeout(()=>{const a=document.getElementById('flashMsg');if(a){a.style.opacity='0';a.style.transition='0.5s';setTimeout(()=>a.remove(),500);}}, 4000);
<?php endif; ?>
</script>
</div>
</body>
</html>

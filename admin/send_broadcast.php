<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$pageTitle = 'Gửi Email Hàng Loạt';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Email Hàng Loạt | Admin Panel</title>
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
            <span class="admin-name"><?= htmlspecialchars($currentAdmin['username']) ?></span>
            <span class="admin-role"><?= htmlspecialchars($currentAdmin['role']) ?></span>
        </div>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-envelope-open-text"></i> Gửi Thông Báo / Cảnh Báo</h2>
        </div>
        <div class="card-body">
            <form id="broadcastForm">
                <div class="form-group">
                    <label>Tiêu đề Email</label>
                    <input type="text" id="bc_subject" class="form-control" placeholder="Nhập tiêu đề thông báo..." required>
                </div>
                
                <div class="form-group">
                    <label>Đối tượng nhận</label>
                    <select id="bc_target_type" class="form-control" onchange="toggleSpecificUser()">
                        <option value="all">Tất cả người dùng (đã xác thực Email)</option>
                        <option value="specific">Người dùng cụ thể</option>
                    </select>
                </div>
                
                <div class="form-group" id="specificUserGroup" style="display:none;">
                    <label>Tên đăng nhập người nhận</label>
                    <input type="text" id="bc_specific_username" class="form-control" placeholder="Ví dụ: taikhoan123">
                </div>
                
                <div class="form-group">
                    <label>Nội dung Email (HTML hỗ trợ)</label>
                    <textarea id="bc_message" class="form-control" rows="8" placeholder="Nhập nội dung HTML..." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" id="btnSendBroadcast">
                    <i class="fas fa-paper-plane"></i> Gửi Email
                </button>
            </form>
            <div id="broadcastResult" style="margin-top: 15px; font-weight: bold;"></div>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
<script>
function toggleSpecificUser() {
    const type = document.getElementById('bc_target_type').value;
    document.getElementById('specificUserGroup').style.display = type === 'specific' ? 'block' : 'none';
}

document.getElementById('broadcastForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSendBroadcast');
    const resultDiv = document.getElementById('broadcastResult');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    resultDiv.innerHTML = '';
    resultDiv.style.color = 'black';
    
    const formData = new FormData();
    formData.append('subject', document.getElementById('bc_subject').value);
    formData.append('message', document.getElementById('bc_message').value);
    formData.append('target_type', document.getElementById('bc_target_type').value);
    formData.append('specific_username', document.getElementById('bc_specific_username').value);
    
    try {
        const res = await fetch('api/broadcast.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            resultDiv.style.color = 'green';
            resultDiv.innerHTML = data.message;
            document.getElementById('broadcastForm').reset();
            toggleSpecificUser();
        } else {
            resultDiv.style.color = 'red';
            resultDiv.innerHTML = 'Lỗi: ' + data.message;
        }
    } catch (e) {
        resultDiv.style.color = 'red';
        resultDiv.innerHTML = 'Lỗi kết nối';
    }
    
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Email';
});
</script>
</body>
</html>

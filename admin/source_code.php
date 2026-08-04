<?php
require_once __DIR__.'/includes/auth.php';
requireAdmin();
$currentAdmin = getCurrentAdmin();
$pageTitle = 'Quản lý Mã nguồn';

// Create directories for upload if not exist
$media_dir = __DIR__.'/../uploads/media/';
$files_dir = __DIR__.'/../uploads/files/';
if (!file_exists($media_dir)) @mkdir($media_dir, 0777, true);
if (!file_exists($files_dir)) @mkdir($files_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $price = intval($_POST['price']);
        $discount_price = !empty($_POST['discount_price']) ? intval($_POST['discount_price']) : null;
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Design & Creative');
        $tags = trim($_POST['tags'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $instructions = $_POST['instructions'] ?? '';
        
        $media_url = null;
        $file_path = null;
        
        // Handle media upload (image or video)
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('media_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['media_file']['tmp_name'], $media_dir . $filename)) {
                $media_url = 'uploads/media/' . $filename;
            }
        }
        
        // Handle source code file upload (zip/rar/etc)
        if (isset($_FILES['source_code_file']) && $_FILES['source_code_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['source_code_file']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('code_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['source_code_file']['tmp_name'], $files_dir . $filename)) {
                $file_path = 'uploads/files/' . $filename;
            }
        }
        
        if ($name && $file_path && $price >= 0) {
            $stmt = $conn->prepare("INSERT INTO source_codes (title, media_urls, download_url, price, discount_price, description, instructions, category, tags, features) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssiisssss", $name, $media_url, $file_path, $price, $discount_price, $description, $instructions, $category, $tags, $features);
            $stmt->execute();
            logAdminAction("Đăng bán mã nguồn mới: $name");
            header('Location: source_code.php?msg=added'); exit;
        } else {
            header('Location: source_code.php?msg=error'); exit;
        }
        
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        // Delete files from filesystem first
        $stmt = $conn->prepare("SELECT media_urls, download_url FROM source_codes WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            if ($res['media_urls'] && file_exists(__DIR__.'/../'.$res['media_urls'])) {
                @unlink(__DIR__.'/../'.$res['media_urls']);
            }
            if ($res['download_url'] && file_exists(__DIR__.'/../'.$res['download_url'])) {
                @unlink(__DIR__.'/../'.$res['download_url']);
            }
        }
        
        $stmt_del = $conn->prepare("DELETE FROM source_codes WHERE id=?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        logAdminAction("Xóa mã nguồn ID: $id");
        header('Location: source_code.php?msg=deleted'); exit;
    }
}

$products = $conn->query("SELECT * FROM source_codes ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng bán Mã nguồn | Admin Panel</title>
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
    <div class="alert alert-<?= $_GET['msg']==='added'?'success':($_GET['msg']==='deleted'?'info':'danger') ?>" id="flashMsg">
        <i class="fas fa-check-circle"></i>
        <?php
        if ($_GET['msg'] === 'added') echo 'Đã đăng bán mã nguồn thành công!';
        elseif ($_GET['msg'] === 'deleted') echo 'Đã xóa mã nguồn khỏi hệ thống.';
        else echo 'Đã xảy ra lỗi, vui lòng kiểm tra lại dữ liệu nhập.';
        ?>
    </div>
    <?php endif; ?>

    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title"><i class="fas fa-code"></i> Quản lý Sản phẩm Mã nguồn <span style="font-size:12px;color:#64748b;font-weight:400;">(<?= count($products) ?> sản phẩm)</span></div>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('show')">
                <i class="fas fa-plus"></i> Đăng bán mã nguồn
            </button>
        </div>
        <?php if (empty($products)): ?>
        <div style="text-align:center;padding:60px 20px;color:#64748b;">
            <i class="fas fa-file-code" style="font-size:48px;margin-bottom:16px;opacity:0.3;display:block;"></i>
            Chưa có sản phẩm mã nguồn nào được đăng bán
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ảnh/Video</th>
                        <th>Tên Mã nguồn</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Giá Gốc</th>
                        <th>Lượt xem/bán</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($products as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['media_urls']): ?>
                            <?php 
                            $ext = strtolower(pathinfo($p['media_urls'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['mp4', 'webm', 'ogg'])): 
                            ?>
                                <video src="../<?= htmlspecialchars($p['media_urls']) ?>" style="width: 60px; height: 45px; border-radius: 8px; object-fit: cover;" muted autoplay loop></video>
                            <?php else: ?>
                                <img src="../<?= htmlspecialchars($p['media_urls']) ?>" style="width: 60px; height: 45px; border-radius: 8px; object-fit: cover;">
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="font-size: 11px; color:#64748b;">Không có</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <b><?= htmlspecialchars($p['title']) ?></b>
                        <div style="font-size: 11px; color:#94a3b8; margin-top:2px;">
                            <i class="far fa-file-archive"></i> <?= basename($p['download_url']) ?>
                        </div>
                    </td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($p['category']) ?></span></td>
                    <td style="font-weight:700;color:#16a34a;"><?= number_format($p['price']) ?>đ</td>
                    <td style="font-weight:700;color:#ef4444;text-decoration:line-through;"><?= $p['discount_price'] !== null && $p['discount_price'] > 0 ? number_format($p['discount_price']).'đ' : '<span style="color:#94a3b8;font-weight:normal;text-decoration:none;">Không</span>' ?></td>
                    <td>
                        <div style="font-size:12px;">
                            <i class="far fa-eye" title="Lượt xem"></i> <?= number_format($p['views_count']) ?> | 
                            <i class="fas fa-shopping-cart" title="Lượt bán"></i> <?= number_format($p['sales_count']) ?>
                        </div>
                    </td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm mã nguồn này?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal-box" style="max-width:650px;">
        <div class="modal-title"><i class="fas fa-plus-circle"></i> Đăng bán Mã nguồn mới</div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label class="form-label">Tên mã nguồn</label>
                <input name="name" class="form-control" required placeholder="Nhập tên mã nguồn...">
            </div>
            
            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div>
                    <label class="form-label">Danh mục sản phẩm</label>
                    <select name="category" class="form-control">
                        <option value="Design & Creative">Design & Creative</option>
                        <option value="Web Designs">Web Designs</option>
                        <option value="Source Code API">Source Code API</option>
                        <option value="Plugins & Extensions">Plugins & Extensions</option>
                        <option value="Other">Khác</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Thẻ / HashTags liên quan</label>
                    <input name="tags" class="form-control" placeholder="Ví dụ: #nghip, #chuyn, #logo">
                </div>
            </div>
            
            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div>
                    <label class="form-label">Hình ảnh/Video giới thiệu</label>
                    <input name="media_file" class="form-control" type="file" accept="image/*,video/*" required>
                    <div class="form-hint">Chọn ảnh hoặc video minh họa sản phẩm</div>
                </div>
                <div>
                    <label class="form-label">File mã nguồn (.zip, .rar)</label>
                    <input name="source_code_file" class="form-control" type="file" accept=".zip,.rar,.tar.gz" required>
                    <div class="form-hint">Tải lên file nén chứa mã nguồn</div>
                </div>
            </div>

            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div>
                    <label class="form-label">Giá Bán Thực Tế (VNĐ)</label>
                    <input name="price" class="form-control" type="number" min="0" required placeholder="Nhập giá...">
                </div>
                <div>
                    <label class="form-label">Giá Gốc (bị gạch ngang) (VNĐ)</label>
                    <input name="discount_price" class="form-control" type="number" min="0" placeholder="Để trống nếu không có...">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mô tả ngắn sản phẩm</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả ngắn gọn về tính năng và công dụng..."></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Danh sách tính năng (Mỗi dòng một dòng checkmark)</label>
                <textarea name="features" class="form-control" rows="4" placeholder="Ví dụ:&#10;Mã nguồn chính chủ, không backdoor&#10;Giao toàn bộ mã nguồn&#10;Hỗ trợ cài đặt code lên web"></textarea>
                <div class="form-hint">Các dòng này sẽ hiển thị kèm dấu check xanh lá ở trang chi tiết.</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Hướng dẫn sử dụng & tải xuống</label>
                <textarea name="instructions" class="form-control" rows="3" placeholder="Nhập hướng dẫn cho người mua (hỗ trợ văn bản/HTML)..."></textarea>
                <div class="form-hint">Nội dung này hiển thị ở trang lịch sử tải mã nguồn của người mua.</div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('show')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Đăng bán</button>
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

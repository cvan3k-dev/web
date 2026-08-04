
<?php
require_once '../includes/auth.php';
requireAdmin();
$action = $_POST['action'] ?? '';
if($action === 'delete'){
    $id = (int)$_POST['id'];
    $conn->prepare("DELETE FROM user_keys WHERE id=?")->bind_param("i",$id)->execute();
    logAdminAction("Xóa key id $id");
    jsonResponse('success','Đã xóa');
}else jsonResponse('error','Invalid');
?>

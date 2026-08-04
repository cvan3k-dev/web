
<?php
require_once '../includes/auth.php';
requireAdmin();
$action = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);
if($action === 'update_balance'){
    $balance = (int)$_POST['balance'];
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("ii", $balance, $user_id);
    $stmt->execute();
    logAdminAction("Cập nhật số dư user $user_id thành $balance");
    jsonResponse('success','Cập nhật thành công');
}
elseif($action === 'delete_user' && $_SESSION['admin_role'] === 'superadmin'){
    $conn->begin_transaction();
    try{
        $conn->prepare("DELETE FROM user_keys WHERE user_id = ?")->bind_param("i",$user_id)->execute();
        $conn->prepare("DELETE FROM transactions WHERE user_id = ?")->bind_param("i",$user_id)->execute();
        $conn->prepare("DELETE FROM users WHERE id = ?")->bind_param("i",$user_id)->execute();
        $conn->commit();
        logAdminAction("Xóa user $user_id");
        jsonResponse('success','Đã xóa');
    }catch(Exception $e){ $conn->rollback(); jsonResponse('error','Lỗi: '.$e->getMessage()); }
}
else jsonResponse('error','Invalid action');
?>

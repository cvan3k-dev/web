
<?php
require_once '../includes/auth.php';
requireAdmin();
header('Content-Type: application/json');
$labels = [];
$revenues = [];
for ($i=6; $i>=0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d/m', strtotime($date));
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE type='buy_key' AND status='completed' AND DATE(created_at)=?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $revenues[] = $stmt->get_result()->fetch_assoc()['total'];
}
echo json_encode(['labels'=>$labels, 'revenues'=>$revenues]);
?>

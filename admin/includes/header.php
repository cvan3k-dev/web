<?php
// admin/includes/header.php
$currentAdmin = isset($currentAdmin) ? $currentAdmin : getCurrentAdmin();
$pageTitles = [
    'index.php'        => ['Dashboard',    'fa-chart-line'],
    'users.php'        => ['Người dùng',   'fa-users'],
    'transactions.php' => ['Giao dịch',    'fa-exchange-alt'],
    'keys.php'         => ['Quản lý Key',  'fa-key'],
    'discounts.php'    => ['Mã giảm giá',  'fa-tag'],
    'promotions.php'   => ['Khuyến mãi',   'fa-gift'],
    'notifications.php'=> ['Thông báo',    'fa-bell'],
    'settings.php'     => ['Cài đặt',      'fa-cog'],
    'logs.php'         => ['Nhật ký',      'fa-history'],
];
$page = basename($_SERVER['PHP_SELF']);
[$pageTitle, $pageIcon] = $pageTitles[$page] ?? ['Admin', 'fa-crown'];
?>
<button class="sidebar-toggle" onclick="toggleAdminSidebar()" aria-label="Menu">
    <i class="fas fa-bars"></i>
</button>
<div class="header-title">
    <i class="fas <?= $pageIcon ?>"></i> <?= $pageTitle ?>
</div>

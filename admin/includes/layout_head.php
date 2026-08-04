<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?> | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= isset($adminRoot) ? $adminRoot : '' ?>style.css">
    <?= $extraHead ?? '' ?>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>
<div class="sidebar" id="adminSidebar"><?php include (isset($adminRoot)?$adminRoot:'').'includes/sidebar.php'; ?></div>
<div class="admin-header">
    <div class="header-left"><?php include (isset($adminRoot)?$adminRoot:'').'includes/header.php'; ?></div>
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

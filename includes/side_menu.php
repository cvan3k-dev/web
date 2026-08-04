<!-- Backdrop overlay for mobile drawer -->
<div class="side-menu-overlay" id="side-menu-overlay" onclick="toggleSideMenu()"></div>

<aside class="side-menu-drawer" id="side-menu-drawer">
    <div class="side-menu-header side-menu-header-mobile">
        <div class="side-menu-brand">
            <img src="assets/img/icon.png" alt="Logo" style="height:24px;vertical-align:middle;" onerror="this.style.display='none'">
            TOOLGAMEAI<span>.SITE</span>
        </div>
        <button class="side-menu-close" onclick="toggleSideMenu()"><i class="fas fa-times"></i></button>
    </div>

    <!-- User Profile Area -->
    <div class="side-menu-profile" <?= $isLoggedIn ? 'style="cursor:pointer;" onclick="toggleSideMenu();"' : '' ?>>
        <?php if ($isLoggedIn && $user): ?>
            <div class="sm-profile-avatar">
                <img src="assets/img/user.gif" alt="Avatar">
                <div class="sm-profile-online"></div>
            </div>
            <div class="sm-profile-info">
                <div class="sm-profile-username"><?= htmlspecialchars($user['username']) ?></div>
                <div class="sm-profile-balance">
                    <i class="fas fa-coins" style="color:#d97706;font-size:11px;"></i>
                    <span><?= number_format($user['balance']) ?> <span style="font-size:9px;opacity:0.6">VNĐ</span></span>
                </div>
            </div>
        <?php else: ?>
            <div class="sm-profile-guest" onclick="openModal('login-modal'); toggleSideMenu();">
                <div class="sm-profile-avatar">
                    <img src="assets/img/user.gif" alt="Avatar">
                </div>
                <div class="sm-profile-info">
                    <div class="sm-profile-username">Khách</div>
                    <div class="sm-profile-login-btn">Đăng nhập ngay <i class="fas fa-chevron-right" style="font-size:8px;"></i></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $smSettings      = json_decode(file_get_contents(__DIR__.'/../admin/settings.json'), true) ?: [];
    $smPromoActive   = ($smSettings['deposit_promo_percent']??0) > 0 && ($smSettings['deposit_promo_expiry']??0) > time();
    $smDiscountActive= ($smSettings['key_discount_percent']??0)   > 0 && ($smSettings['key_discount_expiry']??0)   > time();
    $smPromoRemain   = $smPromoActive   ? ceil(($smSettings['deposit_promo_expiry']-time())/86400) : 0;
    $smDiscountRemain= $smDiscountActive? ceil(($smSettings['key_discount_expiry']-time())/86400)  : 0;
    ?>

    <?php if ($smPromoActive || $smDiscountActive): ?>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php if ($smPromoActive): ?>
        <div onclick="switchView('deposit'); toggleSideMenu();" style="cursor:pointer;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:10px 12px;display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(22,163,74,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-gift" style="color:#16a34a;font-size:14px;"></i>
            </div>
            <div>
                <div style="font-size:12px;font-weight:800;color:#16a34a;">🎉 NẠP TIỀN +<?= $smSettings['deposit_promo_percent'] ?>% BONUS</div>
                <div style="font-size:11px;color:#64748b;">Còn <?= $smPromoRemain ?> ngày — Nạp ngay!</div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($smDiscountActive): ?>
        <div onclick="switchView('buykey'); toggleSideMenu();" style="cursor:pointer;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:10px 12px;display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-percent" style="color:#f59e0b;font-size:14px;"></i>
            </div>
            <div>
                <div style="font-size:12px;font-weight:800;color:#d97706;">🔥 GIẢM GIÁ <?= $smSettings['key_discount_percent'] ?>% MUA KEY</div>
                <div style="font-size:11px;color:#64748b;">Còn <?= $smDiscountRemain ?> ngày — Mua key ngay!</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="side-menu-section-label">HỆ THỐNG</div>
    <ul class="side-menu-list">
        <li>
            <a href="javascript:void(0)" onclick="switchView('home'); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-home-icon"><i class="fas fa-home"></i></span>
                <span>Trang chủ</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="javascript:void(0)" onclick="toggleMobileSubmenu(event)" class="side-menu-item">
                <span class="sm-item-icon sm-code-icon"><i class="fas fa-file-code"></i></span>
                <span>Kho Mã nguồn</span>
                <i class="fas fa-chevron-down submenu-arrow" style="margin-left:auto; font-size:10px; transition: transform 0.2s;"></i>
            </a>
            <ul class="side-submenu" style="display:none; list-style:none; padding-left:42px; margin-top:4px; margin-bottom:4px;">
                <li>
                    <a href="javascript:void(0)" onclick="toggleSideMenu(); scrollToSourceCode();" class="side-menu-item" style="padding: 6px 12px; font-size: 13px;">
                        <i class="fas fa-shopping-bag" style="font-size:11px; margin-right:8px; color:var(--green);"></i>
                        <span>Mua mã nguồn</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" onclick="toggleSideMenu(); <?= $isLoggedIn ? "switchView('my-sourcecodes')" : "openModal('login-modal')" ?>;" class="side-menu-item" style="padding: 6px 12px; font-size: 13px;">
                        <i class="fas fa-download" style="font-size:11px; margin-right:8px; color:var(--green);"></i>
                        <span>Mã nguồn đã mua</span>
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" onclick="switchView('ranking'); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-rank-icon"><i class="fas fa-trophy"></i></span>
                <span>Bảng xếp hạng VIP</span>
            </a>
        </li>
        <?php if ($isLoggedIn): ?>
        <li>
            <a href="javascript:void(0)" onclick="switchView('deposit'); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-deposit-icon"><i class="fas fa-wallet"></i></span>
                <span>Nạp tiền tự động</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" onclick="switchView('buykey'); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-key-icon"><i class="fas fa-key"></i></span>
                <span>Mua Key VIP</span>
            </a>
        </li>
        <?php else: ?>
        <li>
            <a href="javascript:void(0)" onclick="openModal('login-modal'); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-login-icon"><i class="fas fa-sign-in-alt"></i></span>
                <span>Đăng nhập / Đăng ký</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="javascript:void(0)" onclick="openSysNotification(); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-notif-icon"><i class="fas fa-bell"></i></span>
                <span>Thông báo hệ thống</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0)" onclick="openNewsInbox(); toggleSideMenu();" class="side-menu-item">
                <span class="sm-item-icon sm-inbox-icon" style="background:rgba(99,102,241,0.15); color:#6366f1;"><i class="fas fa-envelope"></i></span>
                <span>Hòm thư</span>
            </a>
        </li>
        <li>
            <a href="https://t.me/hellokietne21" target="_blank" rel="noopener" class="side-menu-item telegram-wiggle">
                <span class="sm-item-icon sm-tg-icon"><i class="fab fa-telegram-plane"></i></span>
                <span>Kênh Telegram hỗ trợ</span>
            </a>
        </li>
    </ul>

    <!-- Settings Section -->
    <div class="side-menu-section-label">CÀI ĐẶT & HỖ TRỢ</div>
    <div class="side-menu-settings">
        <div class="sm-setting-row">
            <span class="sm-setting-label"><i class="fas fa-music"></i> Nhạc nền</span>
            <label class="toggle-switch">
                <input type="checkbox" id="sm-toggle-bgm" onchange="toggleBGM(this)">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <div class="sm-setting-row">
            <span class="sm-setting-label"><i class="fas fa-volume-up"></i> Âm thanh nút</span>
            <label class="toggle-switch">
                <input type="checkbox" id="sm-toggle-sfx" onchange="toggleSFX(this)">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <?php if ($isLoggedIn): ?>
        <button class="sm-settings-btn sm-btn-pwd" onclick="openModal('change-pwd-modal'); toggleSideMenu();">
            <i class="fas fa-shield-alt"></i> ĐỔI MẬT KHẨU
        </button>
        <button class="sm-settings-btn sm-btn-logout" onclick="logout();">
            <i class="fas fa-sign-out-alt"></i> ĐĂNG XUẤT
        </button>
        <?php endif; ?>
    </div>
</aside>

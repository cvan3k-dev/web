    <!-- Header -->
    <header class="hdr">
        <!-- LEFT: user info -->
        <div class="hdr-left">
            <?php if ($isLoggedIn && $user): ?>
                <div class="hdr-user">
                    <img src="assets/img/user.gif" alt="User" class="hdr-user-gif" onclick="openModal('settings-modal')" style="cursor:pointer;">
                    <div class="hdr-user-info" onclick="openModal('settings-modal')" style="cursor:pointer;">
                        <div class="hdr-username"><?= htmlspecialchars($user['username']) ?></div>
                        <div class="hdr-balance"><i class="fas fa-coins"></i> <?= number_format($user['balance']) ?></div>
                    </div>
                    <button class="hdr-logout" onclick="logout()" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></button>
                </div>
            <?php else: ?>
                <div class="hdr-guest" onclick="openModal('login-modal')" style="cursor:pointer;">
                    <img src="assets/img/user.gif" alt="User" class="hdr-user-gif">
                    <span class="hdr-guest-txt">Đăng nhập</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- CENTER: animated HTML logo -->
        <div class="hdr-center">
            <div class="hdr-html-logo" id="hdrLogoAnim" onclick="window.location.reload()" style="cursor:pointer;">
                <span class="hdr-logo-t">TG</span><span class="hdr-logo-m">MAI</span><span class="hdr-logo-s">.SITE</span>
            </div>
            <div class="hdr-logo-glow"></div>
        </div>

        <!-- RIGHT: logo.png -->
        <div class="hdr-right">
            <img src="logo.png" alt="Logo" class="hdr-logo-img" onerror="this.style.display='none'">
        </div>
    </header>

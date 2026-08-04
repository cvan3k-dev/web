<!-- ===== PC TOP NAVIGATION (hiển thị >= 769px) ===== -->
<nav class="pc-nav" id="pc-nav">
    <div class="pc-nav-inner">
        <!-- Logo -->
        <div class="pc-nav-logo" onclick="window.location.reload()">
            <img src="assets/img/icon.png" alt="Logo" onerror="this.style.display='none'">
            <div class="pc-nav-logo-text">TOOLGAMEAI<span>.SITE</span></div>
        </div>

        <!-- Nav Links -->
        <nav class="pc-nav-links" aria-label="Main navigation">
            <div class="pc-nav-item">
                <button class="pc-nav-link active" onclick="switchView('home')">
                    <i class="fas fa-home" style="font-size:13px;"></i> Trang chủ
                </button>
            </div>

            <div class="pc-nav-item">
                <button class="pc-nav-link">
                    <i class="fas fa-file-code" style="font-size:13px;"></i> Mã nguồn <i class="fas fa-chevron-down" style="font-size:10px;margin-left:2px;opacity:0.7;"></i>
                </button>
                <div class="pc-nav-dropdown">
                    <a class="pc-nav-dd-item" onclick="scrollToSourceCode()">
                        <i class="fas fa-shopping-bag"></i> Mua mã nguồn
                    </a>
                    <a class="pc-nav-dd-item" onclick="<?= $isLoggedIn ? "switchView('my-sourcecodes')" : "openModal('login-modal')" ?>">
                        <i class="fas fa-download"></i> Quản lý mã nguồn
                    </a>
                </div>
            </div>

            <div class="pc-nav-item">
                <button class="pc-nav-link" onclick="<?= $isLoggedIn ? "switchView('buykey')" : "openModal('login-modal')" ?>">
                    <i class="fas fa-key" style="font-size:13px;"></i> Mua key
                </button>
            </div>

            <div class="pc-nav-item">
                <button class="pc-nav-link" onclick="<?= $isLoggedIn ? "switchView('deposit')" : "openModal('login-modal')" ?>">
                    <i class="fas fa-wallet" style="font-size:13px;"></i> Nạp tiền
                </button>
            </div>

            <div class="pc-nav-item">
                <button class="pc-nav-link">
                    Lịch sử <i class="fas fa-chevron-down"></i>
                </button>
                <div class="pc-nav-dropdown">
                    <?php if ($isLoggedIn): ?>
                    <a class="pc-nav-dd-item" onclick="toggleBKHistoryPanel()">
                        <i class="fas fa-key"></i> Lịch sử mua Key
                    </a>
                    <a class="pc-nav-dd-item" onclick="toggleHistoryPanel()">
                        <i class="fas fa-history"></i> Lịch sử nạp tiền
                    </a>
                    <?php else: ?>
                    <a class="pc-nav-dd-item" onclick="openModal('login-modal')">
                        <i class="fas fa-lock"></i> Đăng nhập để xem
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pc-nav-item">
                <button class="pc-nav-link" onclick="openNewsInbox()">
                    <i class="fas fa-envelope" style="font-size:13px;"></i> Hòm thư
                </button>
            </div>

            <div class="pc-nav-item">
                <a class="pc-nav-link telegram-wiggle" href="https://t.me/hellokietne21" target="_blank" rel="noopener">
                    <i class="fab fa-telegram-plane" style="font-size:13px;color:#0ea5e9;"></i> Liên hệ
                </a>
            </div>
        </nav>

        <!-- Right: User -->
        <div class="pc-nav-right">
            <?php if ($isLoggedIn && $user): ?>
            <div class="pc-nav-user-wrapper">
                <div class="pc-nav-user" title="Tài khoản">
                    <div class="pc-nav-avatar">
                        <img class="user-avatar-img" src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'assets/img/user.gif' ?>" alt="Avatar">
                    </div>
                    <div class="pc-nav-user-info">
                        <div class="pc-nav-username"><?= htmlspecialchars(!empty($user['nickname']) ? $user['nickname'] : $user['username']) ?> <i class="fas fa-chevron-down" style="font-size:9px;margin-left:2px;opacity:0.7;"></i></div>
                        <div class="pc-nav-balance">
                            <i class="fas fa-coins" style="font-size:10px;color:#d97706;"></i>
                            <?= number_format($user['balance']) ?> VNĐ
                        </div>
                    </div>
                </div>
                <!-- User Dropdown Menu -->
                <div class="pc-user-dropdown">
                    <div class="pc-user-dd-header">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['username']) ?>
                    </div>
                    <div class="pc-user-dd-item cursor-default">
                        <span><i class="fas fa-volume-up"></i> Âm thanh nút</span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pc-toggle-sfx" onchange="toggleSFX(this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="pc-user-dd-sep"></div>
                    <a class="pc-user-dd-item" onclick="switchView('profile')">
                        <i class="fas fa-user"></i> Thông tin tài khoản
                    </a>
                    <a class="pc-user-dd-item" onclick="openModal('change-pwd-modal')">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </a>
                    <a class="pc-user-dd-item logout-btn" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
            <button class="pc-nav-btn pc-nav-btn-register" onclick="switchView('deposit')">
                <i class="fas fa-plus"></i> Nạp tiền
            </button>
            <?php else: ?>
            <button class="pc-nav-btn pc-nav-btn-login" onclick="openModal('login-modal')">Đăng nhập</button>
            <button class="pc-nav-btn pc-nav-btn-register" onclick="openModal('login-modal')">Đăng ký</button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ===== MOBILE HEADER (hiển thị < 769px) ===== -->
<header class="hdr-pmg" id="hdr-pmg">
    <div class="hdr-pmg-inner">
        <!-- LEFT: User Box -->
        <div class="hdr-user-box" onclick="<?= $isLoggedIn ? "switchView('profile')" : "openModal('login-modal')" ?>">
            <div class="hdr-avatar-wrap">
                <img class="user-avatar-img" src="<?= ($isLoggedIn && !empty($user['avatar'])) ? htmlspecialchars($user['avatar']) : 'assets/img/user.gif' ?>" alt="Avatar">
                <div class="hdr-online-dot"></div>
            </div>
            <div class="hdr-user-info">
                <div class="hdr-username"><?= $isLoggedIn ? htmlspecialchars(!empty($user['nickname']) ? $user['nickname'] : $user['username']) : 'Khách' ?></div>
                <div class="hdr-balance">
                    <i class="fas fa-coins" style="color:#d97706;font-size:9px;"></i>
                    <?= $isLoggedIn ? number_format($user['balance']) : '0' ?> <span style="opacity:0.6">đ</span>
                </div>
            </div>
        </div>

        <!-- CENTER: Logo -->
        <div class="hdr-logo-box" onclick="switchView('home')">
            <img src="assets/img/icon.png" class="hdr-logo-img" alt="Logo" onerror="this.style.display='none'">
            <div class="hdr-logo-text">
                <span style="color:#1e293b;">TOOLGAME</span><span style="color:#16a34a;">AI</span>
            </div>
        </div>

        <!-- RIGHT: Auth Box & Hamburger Menu -->
        <div class="hdr-auth-box" style="display:flex; align-items:center; gap:8px;">
            <button class="hdr-auth-btn" onclick="openNewsInbox()" aria-label="Hòm thư" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: none; background: rgba(99,102,241,0.15); color: #6366f1; border-radius: 8px; cursor: pointer;">
                <i class="fas fa-envelope"></i>
            </button>
            <?php if ($isLoggedIn): ?>
                <button class="hdr-auth-btn logout" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
                </button>
            <?php else: ?>
                <button class="hdr-auth-btn login" onclick="openModal('login-modal')">
                    <i class="fas fa-sign-in-alt"></i> <span>Đăng nhập</span>
                </button>
            <?php endif; ?>
            
            <button class="hdr-hamburger" onclick="toggleSideMenu()" aria-label="Mở menu" style="display: flex; flex-direction: column; justify-content: center; gap: 4px; border: none; background: none; width: 36px; height: 36px; cursor: pointer; padding: 0; align-items: center;">
                <span class="hamburger-line" style="width: 20px; height: 2px; background: #1e293b; border-radius: 2px;"></span>
                <span class="hamburger-line" style="width: 20px; height: 2px; background: #1e293b; border-radius: 2px;"></span>
                <span class="hamburger-line" style="width: 20px; height: 2px; background: #1e293b; border-radius: 2px;"></span>
            </button>
        </div>
    </div>
</header>

<!-- ===== MOBILE BOTTOM TAB NAV ===== -->
<nav class="mbotnav" id="mbotnav" aria-label="Mobile navigation">
    <div class="mbotnav-inner">

        <!-- Trang chủ -->
        <button class="mbn-item active" id="mbn-home" onclick="switchView('home'); setActiveNav('home')">
            <div class="mbn-icon">
                <i class="fas fa-home"></i>
            </div>
            <span class="mbn-label">TRANG CHỦ</span>
        </button>

        <!-- Mã nguồn -->
        <button class="mbn-item" id="mbn-sourcecode" onclick="openModal('mobile-sourcecode-choice-modal'); setActiveNav('sourcecode')">
            <div class="mbn-icon">
                <i class="fas fa-file-code"></i>
            </div>
            <span class="mbn-label">MÃ NGUỒN</span>
        </button>

        <!-- Lịch sử (Mua key / Nạp tiền) -->
        <button class="mbn-item" id="mbn-history" onclick="<?= $isLoggedIn ? "openModal('mobile-history-choice-modal')" : "openModal('login-modal')" ?>; setActiveNav('history')">
            <div class="mbn-icon">
                <i class="fas fa-history"></i>
            </div>
            <span class="mbn-label">LỊCH SỬ</span>
        </button>

        <!-- Nạp tiền (center highlight) -->
        <button class="mbn-item mbn-center" id="mbn-deposit" onclick="<?= $isLoggedIn ? "switchView('deposit')" : "openModal('login-modal')" ?>; setActiveNav('deposit')">
            <div class="mbn-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <span class="mbn-label">NẠP TIỀN</span>
        </button>

        <!-- Đơn hàng / Buy Key -->
        <button class="mbn-item" id="mbn-order" onclick="<?= $isLoggedIn ? "switchView('buykey')" : "openModal('login-modal')" ?>; setActiveNav('order')">
            <div class="mbn-icon">
                <i class="fas fa-key"></i>
            </div>
            <span class="mbn-label">MUA KEY</span>
        </button>

        <!-- Thông tin / Profile -->
        <button class="mbn-item" id="mbn-info" onclick="<?= $isLoggedIn ? "switchView('profile')" : "openModal('login-modal')" ?>; setActiveNav('info')">
            <div class="mbn-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <span class="mbn-label">THÔNG TIN</span>
        </button>

    </div>
</nav>

<script>
function setActiveNav(tab) {
    document.querySelectorAll('.mbn-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('mbn-' + tab);
    if (el) el.classList.add('active');
}
</script>

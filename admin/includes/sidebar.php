<?php
// admin/includes/sidebar.php
$currentAdmin = getCurrentAdmin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar-logo">
    <div class="sidebar-logo-icon"><i class="fas fa-crown"></i></div>
    <div class="sidebar-logo-text">ADMIN<span>PANEL</span></div>
</div>

<div class="sidebar-nav">
    <div class="sidebar-section">
        <div class="sidebar-section-title">Tổng quan</div>
        <a href="index.php" class="nav-item <?= $currentPage==='index.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="users.php" class="nav-item <?= $currentPage==='users.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Người dùng
        </a>
        <a href="transactions.php" class="nav-item <?= $currentPage==='transactions.php'?'active':'' ?>">
            <i class="fas fa-exchange-alt"></i> Giao dịch
        </a>
        <a href="keys.php" class="nav-item <?= $currentPage==='keys.php'?'active':'' ?>">
            <i class="fas fa-key"></i> Quản lý Key
        </a>
        <a href="source_code.php" class="nav-item <?= $currentPage==='source_code.php'?'active':'' ?>">
            <i class="fas fa-code"></i> Quản lý Mã nguồn
        </a>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-title">Marketing</div>
        <a href="discounts.php" class="nav-item <?= $currentPage==='discounts.php'?'active':'' ?>">
            <i class="fas fa-tag"></i> Mã giảm giá
        </a>
        <a href="promotions.php" class="nav-item <?= $currentPage==='promotions.php'?'active':'' ?>">
            <i class="fas fa-gift"></i> Khuyến mãi
        </a>
        <a href="notifications.php" class="nav-item <?= $currentPage==='notifications.php'?'active':'' ?>">
            <i class="fas fa-bell"></i> Thông báo
        </a>
        <a href="send_broadcast.php" class="nav-item <?= $currentPage==='send_broadcast.php'?'active':'' ?>">
            <i class="fas fa-envelope-open-text"></i> Gửi Email Hàng Loạt
        </a>
    </div>
    <div class="sidebar-section">
        <div class="sidebar-section-title">Hệ thống</div>
        <a href="settings.php" class="nav-item <?= $currentPage==='settings.php'?'active':'' ?>">
            <i class="fas fa-cog"></i> Cài đặt
        </a>
        <a href="logs.php" class="nav-item <?= $currentPage==='logs.php'?'active':'' ?>">
            <i class="fas fa-history"></i> Nhật ký
        </a>
    </div>
</div>

<?php if ($currentAdmin): ?>
<div class="sidebar-profile">
    <div class="sidebar-profile-inner">
        <div class="sidebar-avatar"><?= strtoupper(substr($currentAdmin['username'],0,1)) ?></div>
        <div>
            <div class="sidebar-profile-name"><?= htmlspecialchars($currentAdmin['username']) ?></div>
            <div class="sidebar-profile-role"><?= $currentAdmin['role'] ?></div>
        </div>
        <a href="logout.php" class="sidebar-logout-btn" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hijack click events for links within the sidebar and other internal links
    document.body.addEventListener('click', function(e) {
        const link = e.target.closest('.nav-item, .btn-outline, .btn-primary, .btn-warning, .btn-danger, table a');
        if (!link) return;
        
        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('logout.php') || href.includes('://')) return;
        
        // Only hijack admin php pages
        if (href.endsWith('.php') || href.includes('.php?')) {
            e.preventDefault();
            loadAdminPage(href);
        }
    });

    // Hijack form submissions
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        const action = form.getAttribute('action') || window.location.pathname;
        if (action.startsWith('logout.php')) return;
        
        e.preventDefault();
        submitAdminForm(form, action);
    });
});

async function loadAdminPage(url) {
    try {
        const response = await fetch(url);
        const html = await response.text();
        
        // Parse the retrieved page
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Replace main content
        const currentMain = document.querySelector('.main-content');
        const newMain = doc.querySelector('.main-content');
        if (currentMain && newMain) {
            currentMain.innerHTML = newMain.innerHTML;
            
            // Execute any scripts within the loaded content
            const scripts = newMain.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript);
            });
        }
        
        // Update document title
        document.title = doc.title;
        
        // Update active class on nav items
        const currentPath = url.split('?')[0];
        document.querySelectorAll('.nav-item').forEach(item => {
            const itemHref = item.getAttribute('href').split('?')[0];
            if (currentPath === itemHref) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Update URL
        history.pushState({url: url}, doc.title, url);
        
    } catch (err) {
        console.error('SPA load error:', err);
        window.location.href = url; // Fallback
    }
}

async function submitAdminForm(form, action) {
    try {
        const formData = new FormData(form);
        
        // Detect which button triggered the submit to include its value
        const activeEl = document.activeElement;
        if (activeEl && activeEl.name) {
            formData.append(activeEl.name, activeEl.value);
        }

        const response = await fetch(action, {
            method: 'POST',
            body: formData
        });
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const currentMain = document.querySelector('.main-content');
        const newMain = doc.querySelector('.main-content');
        if (currentMain && newMain) {
            currentMain.innerHTML = newMain.innerHTML;
            
            // Re-run script tags
            const scripts = newMain.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript);
            });
            
            // Look for alert messages to show as toast
            const alertEl = doc.querySelector('.alert');
            if (alertEl && typeof showToast === 'function') {
                const isError = alertEl.classList.contains('alert-danger');
                showToast(alertEl.innerText.trim(), isError ? 'error' : 'success');
            }
        }
    } catch (err) {
        console.error('SPA form submit error:', err);
        form.submit(); // Fallback
    }
}

// Handle browser navigation (back/forward)
window.addEventListener('popstate', function(e) {
    if (e.state && e.state.url) {
        loadAdminPage(e.state.url);
    } else {
        window.location.reload();
    }
});
</script>

</div><!-- /.main-content -->
<div id="toast-container"></div>
<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function showToast(msg, type='success') {
    const tc = document.getElementById('toast-container');
    const t = document.createElement('div');
    const icons = {success:'check-circle',error:'times-circle',info:'info-circle',warning:'exclamation-triangle'};
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i> ${msg}`;
    tc.appendChild(t);
    setTimeout(()=>{ t.style.animation='toastOut 0.3s forwards'; setTimeout(()=>t.remove(),300); }, 3500);
}
</script>
<?= $extraScript ?? '' ?>
</body>
</html>

<?php
// admin/logout.php
session_start();
// Clear admin session variables
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
// Destroy session safely
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: login.php');
exit;
?>

<?php
require_once __DIR__.'/includes/auth.php';
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT id, password, role FROM admin_users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && $password === $res['password']) {
        $_SESSION['admin_id'] = $res['id'];
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $res['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Đăng nhập | ToolGameAI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap');
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(34, 197, 94, 0.03) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(14, 165, 233, 0.03) 0%, transparent 45%);
            pointer-events: none;
            z-index: 0;
        }
        /* Stars / Floating Bubbles Wrap */
        .stars { position:fixed; inset:0; pointer-events:none; z-index:0; }
        .login-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.04);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo-icon {
            width: 60px; height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, #16a34a 0%, #0ea5e9 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: #fff;
            margin: 0 auto 14px;
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.18);
        }
        .login-logo h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 22px; font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.5px;
        }
        .login-logo p { font-size: 13px; color: #94a3b8; font-weight: 500; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        .form-input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            color: #1e293b;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus { 
            border-color: #16a34a; 
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08); 
            background: #ffffff;
        }
        .form-input::placeholder { color: #94a3b8; }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #16a34a 0%, #0ea5e9 50%, #7c3aed 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 8px;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.15);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(14, 165, 233, 0.25); filter: brightness(1.05); }
        .btn-login:active { transform: translateY(0); }
        .error-msg {
            background: #fef2f2;
            border: 1.5px solid #fee2e2;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            color: #ef4444;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px; color: #94a3b8;
        }
        .back-link a { color: #16a34a; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }

        @keyframes floatUp {
            0% { transform: translateY(0) scale(0.6); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1.2); opacity: 0; }
        }
    </style>
</head>
<body>
<div class="stars" id="stars"></div>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon"><i class="fas fa-crown"></i></div>
            <h1>ADMIN PANEL</h1>
            <p>ToolGameAI — Khu vực quản trị</p>
        </div>
        <?php if ($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label">Tên đăng nhập</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-input" placeholder="Nhập username admin..." required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu..." required>
                </div>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Đăng nhập</button>
        </form>
        <div class="back-link"><a href="../"><i class="fas fa-arrow-left"></i> Quay về trang chủ</a></div>
    </div>
</div>
<script>
const s=document.getElementById('stars');
const colors = ['rgba(22,163,74,0.06)', 'rgba(14,165,233,0.06)', 'rgba(124,58,237,0.05)'];
for(let i=0;i<30;i++){
    const el=document.createElement('div');
    const size = Math.random()*40 + 15;
    const color = colors[Math.floor(Math.random()*colors.length)];
    el.style.cssText=`position:absolute;border-radius:50%;background:${color};width:${size}px;height:${size}px;left:${Math.random()*100}%;bottom:-50px;animation:floatUp ${10+Math.random()*15}s linear infinite;animation-delay:${Math.random()*15}s;filter:blur(2px);`;
    s.appendChild(el);
}
</script>
</body>
</html>

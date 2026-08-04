<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang | TOOLGAMEAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #050d1a;
            color: #f0f6ff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(56, 189, 248, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56, 189, 248, 0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
        }

        .bg-glow {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(140px);
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow-1 {
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
            top: -150px;
            left: -150px;
            animation: glow-move-1 10s ease-in-out infinite alternate;
        }
        .bg-glow-2 {
            background: radial-gradient(circle, rgba(56, 189, 248, 0.2) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            animation: glow-move-2 12s ease-in-out infinite alternate;
        }

        @keyframes glow-move-1 {
            from { transform: translate(0, 0); }
            to   { transform: translate(60px, 80px); }
        }
        @keyframes glow-move-2 {
            from { transform: translate(0, 0); }
            to   { transform: translate(-60px, -50px); }
        }

        /* Card */
        .card {
            position: relative;
            z-index: 10;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(56, 189, 248, 0.18);
            border-radius: 28px;
            padding: 60px 50px;
            max-width: 540px;
            width: 92%;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(56, 189, 248, 0.08),
                0 40px 80px rgba(0, 0, 0, 0.5),
                0 0 60px rgba(99, 102, 241, 0.1);
            animation: card-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes card-in {
            from { opacity: 0; transform: translateY(40px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0)    scale(1); }
        }

        /* 404 number */
        .error-code {
            font-size: clamp(90px, 20vw, 140px);
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -4px;
            filter: drop-shadow(0 0 40px rgba(56, 189, 248, 0.35));
            animation: pulse-num 3s ease-in-out infinite;
        }
        @keyframes pulse-num {
            0%, 100% { filter: drop-shadow(0 0 40px rgba(56, 189, 248, 0.35)); }
            50%       { filter: drop-shadow(0 0 70px rgba(129, 140, 248, 0.55)); }
        }

        /* Icon lock */
        .lock-icon {
            font-size: 48px;
            margin: 10px 0 20px;
            display: block;
            animation: bounce-icon 2.5s ease-in-out infinite;
        }
        @keyframes bounce-icon {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
        }

        .error-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        /* Divider */
        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            border-radius: 99px;
            margin: 0 auto 28px;
        }

        /* Button */
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #2563eb, #4f46e5);
        }
        .btn-home svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* Status bar */
        .status-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 36px;
            font-size: 12px;
            color: #475569;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: dot-pulse 1.8s ease-in-out infinite;
        }
        @keyframes dot-pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <div class="card">
        <div class="error-code">404</div>

        <span class="lock-icon">🔒</span>

        <div class="error-title">Trang không tồn tại</div>

        <div class="divider"></div>

        <p class="error-desc">
            Trang bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc không được phép truy cập.<br>
            Vui lòng quay lại trang chủ để tiếp tục.
        </p>

        <a href="/" class="btn-home">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            Về trang chủ
        </a>

        <div class="status-bar">
            <div class="status-dot"></div>
            Hệ thống hoạt động bình thường · TOOLGAMEAI.SITE
        </div>
    </div>
</body>
</html>

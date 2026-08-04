<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì hệ thống | ToolGameAI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Poppins:wght@400;600;700&display=swap');
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:#03040a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}
        .bg{position:fixed;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(56,189,248,0.07),transparent 60%),radial-gradient(ellipse at 80% 80%,rgba(168,85,247,0.05),transparent 50%);pointer-events:none;}
        /* Stars */
        .stars{position:fixed;inset:0;pointer-events:none;}
        .star{position:absolute;border-radius:50%;background:#fff;animation:twinkle var(--d,3s) ease-in-out infinite var(--delay,0s);}
        @keyframes twinkle{0%,100%{opacity:0.2;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}

        .card{position:relative;z-index:10;text-align:center;padding:60px 48px;max-width:520px;width:90%;background:rgba(12,18,35,0.8);backdrop-filter:blur(20px);border:1px solid rgba(56,189,248,0.2);border-radius:28px;box-shadow:0 30px 80px rgba(0,0,0,0.6);}
        .icon-wrap{width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,rgba(56,189,248,0.15),rgba(168,85,247,0.15));border:2px solid rgba(56,189,248,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;animation:pulse 2s ease-in-out infinite;}
        @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(56,189,248,0.3)}50%{box-shadow:0 0 0 20px rgba(56,189,248,0)}}
        .icon-wrap i{font-size:48px;color:#38bdf8;}
        h1{font-family:'Oswald',sans-serif;font-size:32px;font-weight:700;color:#fff;margin-bottom:8px;letter-spacing:1px;}
        .subtitle{font-size:14px;color:#94a3b8;margin-bottom:20px;}
        .message{background:rgba(56,189,248,0.06);border:1px solid rgba(56,189,248,0.15);border-radius:16px;padding:16px 20px;font-size:15px;color:#e2e8f0;margin-bottom:28px;line-height:1.7;}
        .progress-wrap{background:rgba(255,255,255,0.05);border-radius:20px;height:6px;overflow:hidden;margin-bottom:28px;}
        .progress-bar{height:100%;background:linear-gradient(90deg,#38bdf8,#818cf8,#38bdf8);background-size:200%;border-radius:20px;animation:progress 2.5s linear infinite;}
        @keyframes progress{0%{background-position:0%}100%{background-position:200%}}
        .links{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;}
        .links a{display:flex;align-items:center;gap:7px;padding:10px 20px;border-radius:14px;font-size:13px;font-weight:700;text-decoration:none;transition:all 0.2s;}
        .btn-tg{background:rgba(41,182,246,0.12);border:1px solid rgba(41,182,246,0.3);color:#38bdf8;}
        .btn-tg:hover{background:rgba(41,182,246,0.25);transform:translateY(-2px);}
        .btn-reload{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#94a3b8;}
        .btn-reload:hover{background:rgba(255,255,255,0.1);color:#fff;transform:translateY(-2px);}
        .timer{font-family:'Oswald',sans-serif;font-size:13px;color:#64748b;margin-top:20px;}
    </style>
</head>
<body>
<div class="bg"></div>
<div class="stars" id="stars"></div>
<div class="card">
    <div class="icon-wrap"><i class="fas fa-tools"></i></div>
    <h1>BẢO TRÌ HỆ THỐNG</h1>
    <p class="subtitle">Chúng tôi đang nâng cấp để phục vụ bạn tốt hơn ✨</p>
    <div class="message"><?= htmlspecialchars($maintenanceMsg ?? 'Hệ thống đang bảo trì, vui lòng quay lại sau!') ?></div>
    <div class="progress-wrap"><div class="progress-bar"></div></div>
    <div class="links">
        <a href="https://t.me/hellokietne21" target="_blank" class="btn-tg"><i class="fab fa-telegram"></i> Liên hệ hỗ trợ</a>
        <a href="javascript:location.reload()" class="btn-reload"><i class="fas fa-redo"></i> Thử lại</a>
    </div>
    <div class="timer" id="reloadTimer">Tự động kiểm tra lại sau <span id="countdown">60</span>s</div>
</div>
<script>
// Generate stars
const s=document.getElementById('stars');
for(let i=0;i<80;i++){const el=document.createElement('div');el.className='star';const sz=Math.random()*3+1;el.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--d:${2+Math.random()*4}s;--delay:${Math.random()*4}s;opacity:${Math.random()*0.5}`;s.appendChild(el);}
// Countdown
let c=60;const cd=document.getElementById('countdown');
const t=setInterval(()=>{c--;cd.textContent=c;if(c<=0){clearInterval(t);location.reload();}},1000);
</script>
</body>
</html>

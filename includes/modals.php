<!-- Modals -->
<div class="modal-overlay" id="login-modal">
    <div class="auth-modal new-auth-style">
        <button class="auth-close" onclick="closeModal('login-modal')"><i class="fas fa-times"></i></button>
        
        <div class="auth-header-new">
            <img src="logo.png" alt="Logo" class="auth-logo-new" onerror="this.style.display='none'">
            <h2 id="auth-title" class="auth-title-new">Chào mừng trở lại</h2>
            <p id="auth-subtitle" class="auth-subtitle-new">Đăng nhập để tiếp tục</p>
        </div>

        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login" onclick="switchAuthTab('login')">ĐĂNG NHẬP</button>
            <button class="auth-tab" id="tab-register" onclick="switchAuthTab('register')">ĐĂNG KÝ</button>
        </div>

        <div id="auth-login-pane">
            <form id="login-form">
                <div class="auth-form-group">
                    <label class="new-auth-label">Tài khoản</label>
                    <div class="auth-input-container">
                        <i class="fas fa-user auth-field-icon-new"></i>
                        <input type="text" name="username" id="login-username" class="auth-input-new input-username-bg" placeholder="Nhập tên đăng nhập" required>
                    </div>
                </div>
                <div class="auth-form-group">
                    <label class="new-auth-label">Mật khẩu</label>
                    <div class="auth-input-container">
                        <i class="fas fa-lock auth-field-icon-new"></i>
                        <input type="password" name="password" id="login-password" class="auth-input-new" placeholder="Nhập mật khẩu" required>
                        <button type="button" class="auth-eye-new" onclick="togglePwd('login-password',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="auth-row" style="margin-bottom: 20px; margin-top: 10px;">
                    <label class="auth-check">
                        <input type="checkbox" id="remember-me">
                        <span class="auth-checkmark"></span>
                        <span style="font-size: 13px; color: #4b5563;">Ghi nhớ</span>
                    </label>
                    <span class="auth-forgot" onclick="closeModal('login-modal'); openModal('forgot-password-modal');" style="font-size: 13px; color: #6b7280; cursor: pointer; transition: 0.2s;">Quên mật khẩu?</span>
                </div>
                <button type="submit" class="new-auth-btn">Đăng nhập</button>
            </form>
        </div>

        <div id="auth-register-pane" style="display:none">
            <form id="register-form">
                <div class="auth-form-group">
                    <label class="new-auth-label">Tên đăng nhập</label>
                    <div class="auth-input-container">
                        <i class="fas fa-user auth-field-icon-new"></i>
                        <input type="text" name="username" id="reg-username" class="auth-input-new input-username-bg" placeholder="Nhập tên đăng nhập" required>
                    </div>
                </div>
                <div class="auth-form-group">
                    <label class="new-auth-label">Mật khẩu</label>
                    <div class="auth-input-container">
                        <i class="fas fa-lock auth-field-icon-new"></i>
                        <input type="password" name="password" id="reg-pass" class="auth-input-new" placeholder="Nhập mật khẩu" required oninput="updatePwdStrength(this.value)">
                        <button type="button" class="auth-eye-new" onclick="togglePwd('reg-pass',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="pwd-strength-wrap" style="margin-top: 4px; margin-bottom: 12px;">
                    <div class="pwd-strength-bar" style="background:#e5e7eb; height:4px; border-radius:2px; overflow:hidden;"><div class="pwd-strength-fill" id="pwd-strength-fill" style="height:100%; width:0%; transition:width 0.3s;"></div></div>
                    <div class="pwd-strength-label" id="pwd-strength-lbl" style="font-size: 11px; margin-top: 4px; text-align: right; color:#9ca3af;"></div>
                </div>
                <div class="auth-form-group">
                    <label class="new-auth-label">Xác nhận mật khẩu</label>
                    <div class="auth-input-container">
                        <i class="fas fa-check-circle auth-field-icon-new"></i>
                        <input type="password" name="confirm_password" id="reg-confirm" class="auth-input-new" placeholder="Xác nhận mật khẩu" required oninput="checkPasswordMatch()">
                        <button type="button" class="auth-eye-new" onclick="togglePwd('reg-confirm',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div id="confirm-error" style="color:#ef4444; font-size:11px; margin-top:-4px; margin-bottom:12px; display:none;">Mật khẩu xác nhận không khớp</div>
                <button type="submit" class="new-auth-btn">Tạo tài khoản</button>
            </form>
        </div>



        <div class="auth-divider" style="margin-top: 25px; margin-bottom: 25px;">
            <span>hoặc</span>
        </div>

        <button type="button" class="new-google-btn" onclick="window.location.href='/api/google_login.php'">
            <svg class="google-svg" viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            Tiếp tục bằng Google
        </button>

        <div class="auth-toggle-link">
            <span id="auth-switch-text">Chưa có tài khoản? </span>
            <a href="javascript:void(0)" id="auth-switch-btn" onclick="switchAuthTab('register')" class="auth-switch-link">Đăng ký ngay</a>
        </div>
    </div>
</div>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal-overlay" id="forgot-password-modal">
    <div class="modal auth-modal new-auth-style" style="padding-top:20px;">
        <button class="auth-close" onclick="closeModal('forgot-password-modal')"><i class="fas fa-times"></i></button>
        
        <div class="auth-header-new">
            <h2 class="auth-title-new" style="font-size: 20px; color: #3b82f6;"><i class="fas fa-unlock-alt"></i> QUÊN MẬT KHẨU</h2>
            <p class="auth-subtitle-new">Nhập tên tài khoản hoặc email để khôi phục</p>
        </div>

        <div id="forgot-step-1">
            <div class="auth-form-group">
                <label class="new-auth-label">Tài khoản hoặc Email đã liên kết</label>
                <div class="auth-input-container">
                    <i class="fas fa-user-shield auth-field-icon-new"></i>
                    <input type="text" id="forgot-identifier" class="auth-input-new" placeholder="Nhập tên đăng nhập hoặc email..." required>
                </div>
            </div>
            <button type="button" class="new-auth-btn" onclick="sendForgotOTP()" style="background: linear-gradient(135deg, #3b82f6, #2563eb); margin-top: 10px;">LẤY MÃ KHÔI PHỤC</button>
        </div>

        <div id="forgot-step-2" style="display:none; text-align:center;">
            <div style="background: #f0fdf4; color: #166534; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #bbf7d0;">
                <i class="fas fa-check-circle"></i> Đã gửi mã khôi phục tới Email liên kết với tài khoản này.
                <div style="font-size: 11px; margin-top: 4px;">(Nếu không thấy thư hãy vào mục thư rác, nó sẽ ở đó)</div>
            </div>
            <div class="auth-form-group">
                <div class="auth-input-container">
                    <input type="text" id="forgot-otp" placeholder="------" maxlength="6" style="width: 100%; background: #ffffff; border: 2px dashed #10b981; border-radius: 12px; padding: 12px; color: #0f172a; font-size: 24px; font-family: monospace; font-weight: 900; outline: none; text-align: center; letter-spacing: 12px;">
                </div>
            </div>
            <button type="button" class="new-auth-btn" onclick="verifyForgotOTP()" style="background: linear-gradient(135deg, #10b981, #059669); margin-top: 10px;">XÁC MINH MÃ</button>
        </div>

        <div id="forgot-step-3" style="display:none;">
            <div class="auth-form-group">
                <label class="new-auth-label">Mật khẩu mới</label>
                <div class="auth-input-container">
                    <i class="fas fa-key auth-field-icon-new"></i>
                    <input type="password" id="forgot-new-pass" class="auth-input-new" placeholder="Nhập mật khẩu mới" required>
                </div>
            </div>
            <button type="button" class="new-auth-btn" onclick="resetPassword()" style="background: linear-gradient(135deg, #f59e0b, #d97706); margin-top: 10px;">ĐẶT LẠI MẬT KHẨU</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="settings-modal">
    <div class="modal auth-modal" style="padding-top:20px;">
        <button class="auth-close" onclick="closeModal('settings-modal')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title" style="font-size:18px; color:#60a5fa; text-shadow:none; letter-spacing:.05em; text-align:center; margin-bottom:20px;"><i class="fas fa-cog"></i> CÀI ĐẶT & BẢO MẬT</h3>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 15px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);border-radius:12px;">
                <span style="font-size:13px;font-weight:600;"><i class="fas fa-volume-up" style="color:#60c8ff;width:20px;"></i> Âm thanh nút</span>
                <label class="toggle-switch"><input type="checkbox" id="toggle-sfx" onchange="toggleSFX(this)"><span class="toggle-slider"></span></label>
            </div>
            <button class="auth-btn" onclick="openModal('change-pwd-modal')" style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.5); color:#f59e0b; border-radius:12px; padding:12px; font-size:13px;"><i class="fas fa-key"></i> ĐỔI MẬT KHẨU</button>
        </div>
        <button class="hdr-logout" onclick="logout()" style="width:100%;justify-content:center;height:44px;font-size:13px;font-weight:bold;letter-spacing:1px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.5); border-radius:12px;"><i class="fas fa-sign-out-alt"></i> ĐĂNG XUẤT</button>
    </div>
</div>

<!-- Password Change Modal -->
<div class="modal-overlay" id="change-pwd-modal">
    <div class="modal auth-modal" style="padding-top:20px;">
        <button class="auth-close" onclick="closeModal('change-pwd-modal')"><i class="fas fa-arrow-left"></i></button>
        <h3 class="modal-title" style="font-size:18px; color:#f59e0b; text-shadow:none; letter-spacing:.05em; text-align:center; margin-bottom:20px;"><i class="fas fa-lock"></i> ĐỔI MẬT KHẨU</h3>
        <form id="change-pwd-form" style="display:flex;flex-direction:column;gap:10px;">
            <div class="auth-field"><i class="fas fa-lock auth-field-icon"></i><input type="password" name="old_password" class="auth-input" placeholder="Mật khẩu hiện tại" required></div>
            <div class="auth-field"><i class="fas fa-key auth-field-icon"></i><input type="password" name="new_password" class="auth-input" placeholder="Mật khẩu mới" required></div>
            <button type="submit" class="auth-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:12px;font-size:13px;border-radius:12px; margin-top:10px;">XÁC NHẬN ĐỔI</button>
        </form>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal-overlay" id="deposit-modal">
    <div class="deposit-modal">
        <div class="dp-head">
            <button class="modal-close" style="background:linear-gradient(135deg,#ef4444,#b91c1c);" onclick="closeModal('deposit-modal')"><i class="fas fa-times"></i></button>
            <h2 class="dp-title">NẠP TIỀN</h2>
        </div>
        <div class="dp-tabs" style="margin: 0; border-radius:0; background:rgba(0,0,0,0.2); border-bottom:1px solid rgba(60,130,255,0.2);">
            <button class="dp-tab active" onclick="switchDpTab('banking')">I-BANKING</button>
            <button class="dp-tab disabled">THẺ CÀO</button>
            <button class="dp-tab disabled">VÍ ĐIỆN TỬ</button>
        </div>
        <div class="dp-body" style="padding:15px;">
            <div class="dp-balance-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:13px;color:rgba(255,255,255,0.7);">Số dư:</span>
                    <div class="dp-balance"><i class="fas fa-coins" style="color:#fcd34d;"></i> <span style="color:#fcd34d;font-weight:bold;"><?= number_format($user['balance'] ?? 0) ?></span> <span style="font-size:10px;font-weight:400;color:rgba(255,255,255,0.6);">VNĐ</span></div>
                </div>
                <button class="dp-history-btn" onclick="closeModal('deposit-modal'); switchView('deposit'); toggleHistoryPanel();" style="background:transparent; border:1px solid rgba(60,130,255,0.5); color:#60c8ff; padding:4px 10px; border-radius:12px; font-size:11px; cursor:pointer;"><i class="far fa-clock"></i> Lịch sử</button>
            </div>

            <div id="dp-step-selection">
                <div class="dp-amount-box" style="padding:15px; margin-bottom:15px; border:1px solid rgba(60,130,255,0.3); border-radius:12px; background:rgba(0,0,0,0.2);">
                    <input type="number" id="deposit-amount" class="dp-input" value="0" placeholder="0" style="background:rgba(0,0,0,0.4); border:1px solid rgba(60,130,255,0.2); border-radius:8px; padding:12px; width:100%; color:#fff; font-size:16px;">
                    <div class="dp-quick-grid" style="margin-top:15px; display:grid; grid-template-columns:repeat(4, 1fr); gap:6px;">
                        <button class="dp-quick-btn" onclick="setDepositAmount(10000)">10.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(20000)">20.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(50000)">50.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(100000)">100.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(200000)">200.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(500000)">500.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(1000000)">1.000.000</button>
                        <button class="dp-quick-btn" onclick="setDepositAmount(5000000)">5.000.000</button>
                    </div>
                    <button class="dp-submit-btn" onclick="submitDeposit()" style="margin-top:15px; background:linear-gradient(135deg, #d97706, #92400e); color:#fcd34d; font-family:'Orbitron',sans-serif; font-weight:900; letter-spacing:4px; font-size:16px;">N Ạ P</button>
                    <div class="dp-submit-hint" style="text-align:center; font-size:11px; color:rgba(255,255,255,0.5); margin-top:8px;">Nhập số tiền và bấm NẠP để tiếp tục giao dịch.</div>
                </div>
                <div class="dp-notes" style="background:rgba(0,0,0,0.2); padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.05);">
                    <div class="dp-notes-title" style="font-weight:700; font-size:12px; margin-bottom:5px;">Lưu ý:</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7); margin-bottom:4px;">- Mã QR có giá trị 1 lần. Vui lòng chuyển đúng số tiền đã nhập.</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7); margin-bottom:4px;">- Nạp tối đa <span style="color:#fcd34d;font-weight:bold;">30M/phiếu</span>. Có thể chuyển nhiều lần trong ngày.</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7);">- Mọi thắc mắc liên hệ: <a href="#" style="color:#fcd34d;text-decoration:underline;font-weight:700;">Hỗ Trợ</a></div>
                </div>
            </div>

            <div id="dp-step-qr" class="dp-qr-view" style="display:none;">
                <div style="text-align:center; font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:15px;">Vui lòng chuyển tiền theo thông tin bên dưới</div>
                <div class="dp-qr-info" style="margin-bottom:15px;">
                    <div class="dp-info-row"><span>Ngân hàng:</span><div class="dp-info-val highlight" style="color:#60c8ff;">OCB</div></div>
                    <div class="dp-info-row"><span>Số tài khoản:</span><div class="dp-info-val highlight" style="color:#10b981;">0369823800 <i class="far fa-copy dp-copy-btn" onclick="copyText('0369823800')"></i></div></div>
                    <div class="dp-info-row"><span>Chủ TK:</span><div class="dp-info-val">NGUYEN THI DAI <i class="far fa-copy dp-copy-btn" onclick="copyText('NGUYEN THI DAI')"></i></div></div>
                    <div class="dp-info-row"><span>Số tiền:</span><div class="dp-info-val highlight" id="qr-display-amount">0đ <i class="far fa-copy dp-copy-btn" onclick="copyText(document.getElementById('qr-display-amount').innerText.replace('đ','').replace(/\./g,''))"></i></div></div>
                    <div class="dp-info-row"><span>Nội dung CK:</span><div class="dp-info-val highlight" id="qr-display-content" style="color:#10b981;">---</div></div>
                </div>
                <div style="text-align:center; font-size:11px; color:rgba(255,255,255,0.5); margin-bottom:8px;">Quét mã QR để nạp nhanh</div>
                <div class="dp-qr-wrap" style="background:#fff; padding:10px; border-radius:12px; width:200px; height:200px; margin:0 auto 15px; position:relative;">
                    <img src="" id="deposit-qr-img" class="dp-qr-img" style="width:100%; height:100%; object-fit:contain;">
                </div>
                <div class="dp-timer-box" style="background:rgba(0,0,0,0.4); border:1px solid rgba(60,130,255,0.2); border-radius:12px; padding:10px; text-align:center; margin-bottom:10px;">
                    <i class="far fa-clock"></i> Mã QR hết hạn sau: <span id="qr-timer" class="dp-timer-val" style="color:#f59e0b; font-weight:bold;">30:00s</span>
                </div>
                <div class="dp-refresh-link" onclick="refreshDeposit()" style="text-align:center; color:rgba(255,255,255,0.5); text-decoration:underline; font-size:12px; cursor:pointer;">Tạo giao dịch mới?</div>
                <div class="dp-notes" style="margin-top:20px; background:rgba(0,0,0,0.2); padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.05);">
                    <div class="dp-notes-title" style="font-weight:700; font-size:12px; margin-bottom:5px;">Lưu ý:</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7); margin-bottom:4px;">- Mã QR có giá trị 1 lần. Vui lòng chuyển đúng số tiền đã nhập.</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7); margin-bottom:4px;">- Nạp tối đa <span style="color:#fcd34d;font-weight:bold;">30M/phiếu</span>. Có thể chuyển nhiều lần trong ngày.</div>
                    <div class="dp-note-item" style="font-size:11px; color:rgba(255,255,255,0.7);">- Mọi thắc mắc liên hệ: <a href="#" style="color:#fcd34d;text-decoration:underline;font-weight:700;">Hỗ Trợ</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Modals -->
<div class="modal-overlay" id="ntLsdOverlay">
    <div class="modal" style="max-width:440px;">
        <button class="modal-close" onclick="closeModal('ntLsdOverlay')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title"><i class="fas fa-history"></i> LỊCH SỬ NẠP</h3>
        <div id="ntLsdList" style="max-height:400px;overflow-y:auto;padding:5px;"></div>
    </div>
</div>

<!-- Buy Key History Modal -->
<div class="modal-overlay" id="bkLsdOverlay">
    <div class="modal" style="max-width:550px;">
        <button class="modal-close" onclick="closeModal('bkLsdOverlay')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title"><i class="fas fa-key"></i> LỊCH SỬ MUA KEY</h3>
        <div id="bkLsdList" style="max-height:400px;overflow-y:auto;padding:5px;"></div>
    </div>
</div>

<!-- News Inbox Modal -->
<div class="modal-overlay" id="newsInboxOverlay">
    <div class="modal" style="max-width:550px;">
        <button class="modal-close" onclick="closeModal('newsInboxOverlay')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title"><i class="fas fa-envelope"></i> HÒM THƯ TIN TỨC</h3>
        <div id="newsInboxList" style="max-height:400px;overflow-y:auto;padding:5px;display:flex;flex-direction:column;gap:8px;">
            <!-- Render notification list here -->
        </div>
    </div>
</div>

<!-- News Detail Modal -->
<div class="modal-overlay" id="newsDetailOverlay">
    <div class="modal" style="max-width:500px;">
        <button class="modal-close" onclick="closeModal('newsDetailOverlay')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title" id="newsDetailTitle" style="font-size:16px; margin-bottom:10px; color:#22c1c3; text-align:left;">Tiêu đề tin tức</h3>
        <div id="newsDetailTime" style="font-size:11px; color:#94a3b8; margin-bottom:15px; display:flex; align-items:center; gap:5px; justify-content:flex-start;">
            <i class="far fa-clock"></i> <span>--/--/----</span>
        </div>
        <div id="newsDetailContent" style="max-height:300px; overflow-y:auto; line-height:1.6; font-size:13px; color:#2c3e50; text-align:left; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.08); padding:15px; border-radius:8px;">
            Nội dung chi tiết...
        </div>
        <div style="margin-top:20px; text-align:center;">
            <button class="auth-btn" onclick="closeModal('newsDetailOverlay')" style="max-width:120px; padding:10px; font-size:12px; border-radius:8px;">ĐÓNG</button>
        </div>
    </div>
</div>

<div id="notification-overlay" onclick="closeSysNotification()"></div>
<div id="notification" class="notif-pmg">
    <div class="notif-hdr">
        <span class="notif-title" id="notif-popup-title">THÔNG ĐIỆP HỆ THỐNG</span>
        <button class="notif-close-btn" onclick="closeSysNotification()"><i class="fas fa-times"></i></button>
    </div>
    <div class="notif-icon-wrap">
        <div class="notif-icon-circle"><i class="fas fa-bell"></i></div>
    </div>
    <div class="notif-body" id="notif-popup-body" style="max-height: 180px; overflow-y: auto; text-align: left; line-height: 1.6; font-size: 13px;">
        Đang tải thông báo từ hệ thống...
    </div>
    <div class="notif-actions">
        <button class="notif-btn-snooze" onclick="closeSysNotification()">ĐÓNG</button>
        <button class="notif-btn-ok" onclick="closeSysNotification()">ĐÃ HIỂU</button>
    </div>
</div>

<!-- KEY ENTRY MODAL -->
<div class="modal-overlay" id="key-entry-modal">
    <div class="modal auth-modal" style="padding-top:20px; text-align:center;">
        <button class="auth-close" onclick="closeModal('key-entry-modal')"><i class="fas fa-times"></i></button>
        <div style="margin-bottom:20px;">
            <div style="width:100px; height:auto; background:rgba(0,0,0,0.3); border-radius:16px; margin:0 auto 15px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.1); overflow:hidden; box-shadow:inset 0 0 15px rgba(0,0,0,0.5);">
                <img id="key-game-logo" src="" style="width:100%; max-height:100px; object-fit:contain; padding:8px;">
            </div>
            <h3 style="font-family:'Orbitron',sans-serif; font-weight:900; font-size:18px; color:#fff; letter-spacing:.05em; text-shadow:0 0 10px rgba(100,180,255,0.5);" id="key-target-game">GAME NAME</h3>
        </div>
        <div class="auth-field" style="margin-bottom:15px;">
            <i class="fas fa-key auth-field-icon"></i>
            <input type="text" id="activation-key" class="auth-input" placeholder="Nhập mã Key VIP...">
        </div>
        <div class="auth-row" style="margin-bottom:20px;">
            <label class="auth-check"><input type="checkbox" id="save-key"><span class="auth-checkmark"></span><span>Lưu Key cho lần sau</span></label>
        </div>
        <button class="auth-btn auth-btn-login" onclick="validateKey()" style="font-size:14px;padding:12px;">KÍCH HOẠT TOOL</button>
    </div>
</div>



<!-- RANKING MODAL - VIP THẬT (đã xóa tab Ngày/Tháng, thêm thanh bar & hướng dẫn) -->
<div class="modal-overlay" id="ranking-modal">
    <div class="modal" style="padding:0; overflow:hidden; max-width:520px;">
        <div class="dp-head" style="position:relative;">
            <button class="modal-close" onclick="closeModal('ranking-modal')"><i class="fas fa-times"></i></button>
            <h2 class="dp-title" style="color:#fcd34d;">🏆 BẢNG XẾP HẠNG VIP</h2>
        </div>

        <!-- Thông tin VIP của user đang đăng nhập + thanh bar -->
        <div id="current-vip-panel" style="padding:15px 20px; background:rgba(0,0,0,0.3); border-bottom:1px solid rgba(255,215,0,0.3);">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                <img id="current-vip-icon" src="assets/img/icon1.png" style="width:44px; height:44px; border-radius:50%; background:#000; padding:4px;">
                <div>
                    <div style="font-size:12px; color:#aaa;">HẠNG VIP CỦA BẠN</div>
                    <div style="font-size:18px; font-weight:800; color:#fcd34d;" id="current-vip-name">VIP1</div>
                    <div style="font-size:11px;" id="current-total-deposit">Tổng nạp: 0đ</div>
                </div>
            </div>
            <div style="margin:8px 0;">
                <div style="display:flex; justify-content:space-between; font-size:10px; color:#ccc;">
                    <span id="bar-label-start">0đ</span>
                    <span id="bar-label-end">40.000đ</span>
                </div>
                <div style="background:rgba(255,255,255,0.1); border-radius:20px; height:14px; overflow:hidden; margin-top:4px;">
                    <div id="vip-progress-bar" style="width:0%; height:100%; background:linear-gradient(90deg, #fbbf24, #f59e0b); border-radius:20px;"></div>
                </div>
            </div>
        </div>

        <!-- Danh sách top người chơi -->
        <div style="padding:10px 0 0; background:rgba(0,0,0,0.2);">
            <div class="rank-header" style="display:flex; padding:8px 15px; font-size:11px; color:rgba(255,255,255,0.7); border-bottom:1px solid rgba(255,255,255,0.05);">
                <div style="width:50px;text-align:center;">Hạng</div>
                <div style="flex:1;text-align:left;">Người chơi</div>
                <div style="width:100px;text-align:right;">Tổng nạp</div>
                <div style="width:40px;text-align:center;">VIP</div>
            </div>
            <div class="dp-body" id="ranking-list-container" style="padding:0; max-height:280px; overflow-y:auto;">
                <div style="text-align:center; padding:30px;"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>
            </div>
        </div>
    </div>
</div>



<!-- checkPasswordMatch is handled in app.js -->

<!-- Mobile History Choice Modal -->
<div class="modal-overlay" id="mobile-history-choice-modal">
    <div class="modal auth-modal mobile-choice-modal-inner" style="max-width:400px; padding-top:20px; border-radius: 20px;">
        <button class="auth-close" onclick="closeModal('mobile-history-choice-modal')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title" style="font-size:18px; font-weight:800; text-align:center; margin-bottom:20px; color:#1e293b;"><i class="fas fa-history"></i> LỊCH SỬ GIAO DỊCH</h3>
        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:10px;">
            <button class="choice-modal-btn key-history-choice" onclick="closeModal('mobile-history-choice-modal'); openModal('bkLsdOverlay'); loadBKHistoryInModal();">
                <div class="choice-icon-wrap"><i class="fas fa-key"></i></div>
                <div class="choice-text-wrap">
                    <span class="choice-title">LỊCH SỬ MUA KEY</span>
                    <span class="choice-sub">Xem danh sách key vip đã mua</span>
                </div>
                <i class="fas fa-chevron-right choice-arrow"></i>
            </button>
            <button class="choice-modal-btn deposit-history-choice" onclick="closeModal('mobile-history-choice-modal'); openModal('ntLsdOverlay'); loadDepositHistory();">
                <div class="choice-icon-wrap"><i class="fas fa-wallet"></i></div>
                <div class="choice-text-wrap">
                    <span class="choice-title">LỊCH SỬ NẠP TIỀN</span>
                    <span class="choice-sub">Xem lịch sử nạp thẻ & banking</span>
                </div>
                <i class="fas fa-chevron-right choice-arrow"></i>
            </button>
        </div>
    </div>
</div>

<!-- Mobile Source Code Choice Modal -->
<div class="modal-overlay" id="mobile-sourcecode-choice-modal">
    <div class="modal auth-modal mobile-choice-modal-inner" style="max-width:400px; padding-top:20px; border-radius: 20px;">
        <button class="auth-close" onclick="closeModal('mobile-sourcecode-choice-modal')"><i class="fas fa-times"></i></button>
        <h3 class="modal-title" style="font-size:18px; font-weight:800; text-align:center; margin-bottom:20px; color:#1e293b;"><i class="fas fa-file-code"></i> MÃ NGUỒN</h3>
        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:10px;">
            <button class="choice-modal-btn key-history-choice" onclick="closeModal('mobile-sourcecode-choice-modal'); scrollToSourceCode();">
                <div class="choice-icon-wrap" style="background:rgba(22,163,74,0.15); color:#16a34a;"><i class="fas fa-shopping-bag"></i></div>
                <div class="choice-text-wrap">
                    <span class="choice-title" style="color:#16a34a;">MUA MÃ NGUỒN</span>
                    <span class="choice-sub">Khám phá kho mã nguồn game nổi bật</span>
                </div>
                <i class="fas fa-chevron-right choice-arrow"></i>
            </button>
            <button class="choice-modal-btn deposit-history-choice" onclick="closeModal('mobile-sourcecode-choice-modal'); <?= $isLoggedIn ? "switchView('my-sourcecodes')" : "openModal('login-modal')" ?>;">
                <div class="choice-icon-wrap" style="background:rgba(14,165,233,0.15); color:#0ea5e9;"><i class="fas fa-download"></i></div>
                <div class="choice-text-wrap">
                    <span class="choice-title" style="color:#0ea5e9;">MÃ NGUỒN ĐÃ MUA</span>
                    <span class="choice-sub">Tải xuống mã nguồn & hướng dẫn cài đặt</span>
                </div>
                <i class="fas fa-chevron-right choice-arrow"></i>
            </button>
        </div>
        </div>
    </div>
</div>

<!-- Google Registration Modal -->
<div class="modal-overlay" id="google-register-modal">
    <div class="modal auth-modal new-auth-style" style="padding-top:20px;">
        <button class="auth-close" onclick="closeModal('google-register-modal')"><i class="fas fa-times"></i></button>
        
        <div class="auth-header-new">
            <h2 class="auth-title-new" style="font-size: 20px; color: #4285F4;"><i class="fab fa-google"></i> HOÀN TẤT ĐĂNG KÝ</h2>
            <p class="auth-subtitle-new">Bạn đang đăng nhập bằng Google.<br>Vui lòng chọn một Tên Đăng Nhập cho tài khoản này.</p>
        </div>

        <form id="google-register-form">
            <div class="auth-form-group">
                <label class="new-auth-label">Tên đăng nhập (Username)</label>
                <div class="auth-input-container">
                    <i class="fas fa-user auth-field-icon-new"></i>
                    <input type="text" name="username" id="google-reg-username" class="auth-input-new input-username-bg" placeholder="Viết liền không dấu..." required>
                </div>
            </div>
            
            <button type="submit" class="new-auth-btn" style="background: linear-gradient(135deg, #4285F4, #34A853); margin-top: 10px;">
                XÁC NHẬN VÀ ĐĂNG NHẬP
            </button>
        </form>
    </div>
</div>

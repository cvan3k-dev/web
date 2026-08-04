<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

/**
 * Gửi email sử dụng Gmail SMTP
 * Yêu cầu: Bạn cần cung cấp tài khoản Gmail và Mật khẩu ứng dụng (App Password)
 */
function sendAppEmail($toEmail, $subject, $htmlContent) {
    // THIẾT LẬP GMAIL SMTP (NGƯỜI DÙNG CẦN THAY ĐỔI THÔNG TIN NÀY)
    $smtp_user = 'toolgameai.site2810@gmail.com'; // Thay bằng Gmail của bạn
    $smtp_pass = 'zrqbvrevdxueuire';    // Thay bằng Mật khẩu ứng dụng (16 ký tự, ví dụ: abcd efgh ijkl mnop)
    
    // Nếu chưa cấu hình, return false để không bị lỗi ứng dụng
    if (empty($smtp_user) || $smtp_user === 'your_gmail_here@gmail.com') {
        return ['status' => false, 'message' => 'Hệ thống chưa cấu hình Email gửi đi. Vui lòng liên hệ Admin.'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom($smtp_user, 'TOOLGAMEAI.SITE');
        $mail->addAddress($toEmail);
        
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        
        // --- BẢO MẬT VÀ CHỐNG SPAM ---
        // Gỡ XMailer giả mạo để tránh bộ lọc của Google nghi ngờ
        $mail->XMailer = ' '; 
        // 2. Không đính kèm tệp để tránh bị đưa vào Thư rác
        $mail->Subject = strip_tags($subject);

        // Sử dụng một ảnh Logo trên mạng (CDN) thay vì đính kèm tệp trực tiếp
        // Điều này giúp email nhẹ hơn và không bị đánh dấu Spam do CID attachment
        $logoHtml = '<div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 10px; border-radius: 12px; margin-bottom: 10px;"><img src="https://cdn-icons-png.flaticon.com/512/2906/2906274.png" alt="Logo" style="max-height: 50px; position: relative; z-index: 1;"></div>';
        
        // Bọc nội dung trong template đẹp
        $template = '
        <div style="font-family: \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f8fafc; padding: 20px;">
            <div style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                
                <!-- HEADER -->
                <div style="background: linear-gradient(135deg, #0f172a, #1e293b, #3b82f6); padding: 30px 20px; text-align: center; position: relative;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url(\'https://www.transparenttextures.com/patterns/cubes.png\'); opacity: 0.05;"></div>
                    ' . $logoHtml . '
                    <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 2px; position: relative; z-index: 1;">
                        TOOLGAMEAI.SITE
                    </h1>
                    <p style="color: #94a3b8; margin: 8px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; position: relative; z-index: 1;">Hệ Thống AI Tối Ưu</p>
                </div>

                <!-- BODY CONTENT -->
                <div style="padding: 40px 30px; color: #334155; line-height: 1.7; font-size: 16px;">
                    ' . $htmlContent . '
                </div>

                <!-- FOOTER -->
                <div style="background: #f1f5f9; padding: 25px 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                    <div style="margin-bottom: 15px;">
                        <a href="https://toolgameai.site" style="display: inline-block; padding: 10px 24px; background: #3b82f6; color: #fff; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 14px; transition: all 0.3s;">Truy Cập Trang Chủ</a>
                    </div>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">Đây là email gửi tự động từ hệ thống, vui lòng không phản hồi.</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #94a3b8;">&copy; ' . date('Y') . ' ToolGameAI. Đã đăng ký bản quyền.</p>
                </div>
            </div>
        </div>';

        $mail->Body = $template;

        $mail->send();
        return ['status' => true, 'message' => 'Gửi email thành công'];
    } catch (Exception $e) {
        return ['status' => false, 'message' => 'Không thể gửi email. Lỗi: ' . $mail->ErrorInfo];
    }
}
?>

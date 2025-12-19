<?php
/**
 * Email Helper Class
 * Handles sending emails for borrowing notifications using PHPMailer
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/EnvLoader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
EnvLoader::load();

class EmailHelper {
    private $adminEmail;
    private $siteName = 'INLET Lab - Politeknik Negeri Malang';
    private $siteUrl;
    
    public function __construct($adminEmail = null) {
        $this->adminEmail = $adminEmail ?? 'inlet@polinema.ac.id';
        $this->siteUrl = $this->getBaseUrl();
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . '/inlet-web';
    }
    
    /**
     * Static method to send borrowing notification to admin
     * Used by: views/pages/borrowing.php
     */
    public static function sendBorrowingNotificationToAdmin($borrowingData, $adminEmail) {
        $helper = new self($adminEmail);
        return $helper->sendBorrowingRequestToAdmin($borrowingData);
    }
    
    /**
     * Static method to send approval notification to borrower
     * Used by: admin/borrowing.php
     */
    public static function sendApprovalNotification($borrowingData, $approvalStatus, $notes = '') {
        $helper = new self();
        $approved = ($approvalStatus === 'approved');
        return $helper->sendApprovalToBorrower($borrowingData, $approved, $notes);
    }
    
    /**
     * Send email notification to admin when new borrowing request is submitted
     */
    public function sendBorrowingRequestToAdmin($borrowingData) {
        // Debug logging
        error_log("EmailHelper: Starting sendBorrowingRequestToAdmin");
        error_log("EmailHelper: Admin email = " . $this->adminEmail);
        error_log("EmailHelper: Borrower = " . $borrowingData['borrower_name']);
        
        $to = $this->adminEmail;
        $subject = "[INLET] Permintaan Peminjaman Baru - " . $borrowingData['borrower_name'];
        
        $itemType = $borrowingData['type'] === 'equipment' ? 'Alat' : 'Ruangan';
        $borrowDate = date('d F Y H:i', strtotime($borrowingData['borrow_date']));
        $returnDate = date('d F Y H:i', strtotime($borrowingData['return_date']));
        
        $adminUrl = $this->siteUrl . '/admin/borrowing.php';
        
        $message = $this->getEmailTemplate('admin_notification', [
            'borrower_name' => $borrowingData['borrower_name'],
            'borrower_email' => $borrowingData['borrower_email'] ?? '-',
            'occupation' => $borrowingData['occupation'],
            'item_type' => $itemType,
            'item_name' => $borrowingData['item_name'],
            'borrow_date' => $borrowDate,
            'return_date' => $returnDate,
            'notes' => $borrowingData['notes'] ?? '-',
            'admin_url' => $adminUrl
        ]);
        
        error_log("EmailHelper: Calling sendEmail to {$to}");
        $result = $this->sendEmail($to, $subject, $message);
        error_log("EmailHelper: sendEmail result = " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
    }
    
    /**
     * Send approval notification to borrower
     */
    public function sendApprovalToBorrower($borrowingData, $approved = true, $adminMessage = '') {
        error_log("EmailHelper: sendApprovalToBorrower called");
        error_log("EmailHelper: Borrower email = " . ($borrowingData['borrower_email'] ?? 'NULL'));
        
        $to = $borrowingData['borrower_email'];
        
        if (empty($to)) {
            error_log("EmailHelper: Borrower email is empty, cannot send");
            return false;
        }
        
        $status = $approved ? 'Disetujui' : 'Ditolak';
        $subject = "[INLET] Peminjaman Anda $status";
        
        $itemType = $borrowingData['type'] === 'equipment' ? 'Alat' : 'Ruangan';
        $borrowDate = date('d F Y H:i', strtotime($borrowingData['borrow_date']));
        $returnDate = date('d F Y H:i', strtotime($borrowingData['return_date']));
        
        // Use item_names (from getBorrowingForEmail), fallback to item_name or room_name
        $itemName = $borrowingData['item_names'] ?? $borrowingData['item_name'] ?? $borrowingData['room_name'] ?? '-';
        
        $message = $this->getEmailTemplate($approved ? 'borrower_approved' : 'borrower_rejected', [
            'borrower_name' => $borrowingData['borrower_name'],
            'item_type' => $itemType,
            'item_name' => $itemName,
            'borrow_date' => $borrowDate,
            'return_date' => $returnDate,
            'admin_message' => $adminMessage,
            'site_url' => $this->siteUrl
        ]);
        
        error_log("EmailHelper: Calling sendEmail to {$to}");
        $result = $this->sendEmail($to, $subject, $message);
        error_log("EmailHelper: sendEmail result = " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
    }
    
    /**
     * Get email template
     */
    private function getEmailTemplate($type, $data) {
        $templates = [
            'admin_notification' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .info-table td:first-child { font-weight: bold; color: #64748b; width: 40%; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
        .badge { display: inline-block; background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">📋 Permintaan Peminjaman Baru</h1>
        </div>
        <div class="content">
            <p>Halo Admin,</p>
            <p>Ada permintaan peminjaman baru yang memerlukan persetujuan Anda:</p>
            
            <table class="info-table">
                <tr><td>Nama Peminjam</td><td><strong>{borrower_name}</strong></td></tr>
                <tr><td>Email</td><td>{borrower_email}</td></tr>
                <tr><td>Pekerjaan</td><td>{occupation}</td></tr>
                <tr><td>Jenis Peminjaman</td><td><span class="badge">{item_type}</span></td></tr>
                <tr><td>Item</td><td>{item_name}</td></tr>
                <tr><td>Waktu Pinjam</td><td>{borrow_date}</td></tr>
                <tr><td>Waktu Kembali</td><td>{return_date}</td></tr>
                <tr><td>Catatan</td><td>{notes}</td></tr>
            </table>
            
            <p style="text-align: center;">
                <a href="{admin_url}" class="btn">🔍 Lihat & Setujui di Admin Panel</a>
            </p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem INLET Lab.<br>
            Politeknik Negeri Malang</p>
        </div>
    </div>
</body>
</html>',

            'borrower_approved' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #059669, #047857); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .info-table td:first-child { font-weight: bold; color: #64748b; width: 40%; }
        .success-box { background: #d1fae5; border: 1px solid #059669; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">✅ Peminjaman Disetujui!</h1>
        </div>
        <div class="content">
            <p>Halo <strong>{borrower_name}</strong>,</p>
            
            <div class="success-box">
                <strong>🎉 Selamat!</strong> Permintaan peminjaman Anda telah <strong>DISETUJUI</strong>.
            </div>
            
            <p>Berikut detail peminjaman Anda:</p>
            
            <table class="info-table">
                <tr><td>Jenis Peminjaman</td><td>{item_type}</td></tr>
                <tr><td>Item</td><td><strong>{item_name}</strong></td></tr>
                <tr><td>Waktu Pinjam</td><td>{borrow_date}</td></tr>
                <tr><td>Waktu Kembali</td><td>{return_date}</td></tr>
            </table>
            
            ' . (!empty($data['admin_message'] ?? '') ? '<p><strong>Pesan dari Admin:</strong><br>{admin_message}</p>' : '') . '
            
            <p><strong>⚠️ Penting:</strong></p>
            <ul>
                <li>Harap datang tepat waktu sesuai jadwal peminjaman</li>
                <li>Jaga dan rawat peralatan/ruangan yang dipinjam</li>
                <li>Kembalikan tepat waktu sesuai jadwal</li>
            </ul>
            
            <p>Terima kasih telah menggunakan layanan INLET Lab!</p>
        </div>
        <div class="footer">
            <p>INLET Lab - Politeknik Negeri Malang<br>
            Jl. Soekarno Hatta No.9, Malang</p>
        </div>
    </div>
</body>
</html>',

            'borrower_rejected' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .info-table td:first-child { font-weight: bold; color: #64748b; width: 40%; }
        .reject-box { background: #fee2e2; border: 1px solid #dc2626; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #64748b; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">❌ Peminjaman Ditolak</h1>
        </div>
        <div class="content">
            <p>Halo <strong>{borrower_name}</strong>,</p>
            
            <div class="reject-box">
                Mohon maaf, permintaan peminjaman Anda <strong>tidak dapat disetujui</strong>.
            </div>
            
            <p>Detail peminjaman yang ditolak:</p>
            
            <table class="info-table">
                <tr><td>Jenis Peminjaman</td><td>{item_type}</td></tr>
                <tr><td>Item</td><td>{item_name}</td></tr>
                <tr><td>Waktu Pinjam</td><td>{borrow_date}</td></tr>
                <tr><td>Waktu Kembali</td><td>{return_date}</td></tr>
            </table>
            
            ' . (!empty($data['admin_message'] ?? '') ? '<p><strong>Alasan Penolakan:</strong><br>{admin_message}</p>' : '') . '
            
            <p>Jika Anda memiliki pertanyaan, silakan hubungi kami melalui email atau datang langsung ke INLET Lab.</p>
            
            <p>Terima kasih atas pengertian Anda.</p>
        </div>
        <div class="footer">
            <p>INLET Lab - Politeknik Negeri Malang<br>
            Jl. Soekarno Hatta No.9, Malang</p>
        </div>
    </div>
</body>
</html>'
        ];
        
        $template = $templates[$type] ?? '';
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', htmlspecialchars($value ?? ''), $template);
        }
        
        return $template;
    }
    
    /**
     * Send email using PHPMailer with SMTP
     */
    private function sendEmail($to, $subject, $message) {
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = EnvLoader::get('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = EnvLoader::get('MAIL_USERNAME');
            $mail->Password = EnvLoader::get('MAIL_PASSWORD');
            $mail->SMTPSecure = EnvLoader::get('MAIL_ENCRYPTION', 'tls');
            $mail->Port = (int)EnvLoader::get('MAIL_PORT', 587);
            
            // Sender info
            $fromAddress = EnvLoader::get('MAIL_FROM_ADDRESS', 'noreply@gmail.com');
            $fromName = EnvLoader::get('MAIL_FROM_NAME', 'inletpolinema');
            $mail->setFrom($fromAddress, $fromName);
            $mail->addReplyTo($this->adminEmail, 'INLET Lab Admin');
            
            // Recipient
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            
            // Send email
            $result = $mail->send();
            
            // Log success
            $this->logEmail($to, $subject, true, 'Email sent successfully');
            
            return true;
            
        } catch (Exception $e) {
            $errorMsg = "PHPMailer Error: {$mail->ErrorInfo}";
            error_log($errorMsg);
            
            // Log failure
            $this->logEmail($to, $subject, false, $errorMsg);
            
            return false;
        } catch (\Exception $e) {
            $errorMsg = "General Error: {$e->getMessage()}";
            error_log($errorMsg);
            
            // Log failure
            $this->logEmail($to, $subject, false, $errorMsg);
            
            return false;
        }
    }
    
    /**
     * Log email for debugging
     */
    private function logEmail($to, $subject, $success, $details = '') {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/email_' . date('Y-m-d') . '.log';
        $logEntry = sprintf(
            "[%s] %s | To: %s | Subject: %s | Details: %s\n",
            date('Y-m-d H:i:s'),
            $success ? 'SUCCESS' : 'FAILED',
            $to,
            $subject,
            $details
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

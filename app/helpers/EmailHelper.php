<?php
// app/helpers/EmailHelper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// โหลดไฟล์ PHPMailer
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';

// โหลดไฟล์ Database
require_once __DIR__ . '/../config/database.php';

// ฟังก์ชันหลัก: เปลี่ยนไปลง Queue แทนการส่งจริงทันที
function sendEmail($toEmail, $toName, $subject, $bodyHtml, $imagePath = null) {
    try {
        $pdo = Database::getInstance()->getConnection();
        $sql = "INSERT INTO mail_queue (to_email, to_name, subject, body, image_path, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$toEmail, $toName, $subject, $bodyHtml, $imagePath]);
        
        // *Optional*: ถ้าต้องการให้ส่งทันทีใน Background (สำหรับ Windows/XAMPP อาจจะยากหน่อย)
        // หรือใช้วิธี AJAX Trigger ที่หน้าเว็บหลักเอา
        return true; 
    } catch (Exception $e) {
        error_log("Mail Queue Error: " . $e->getMessage());
        return false;
    }
}

// ฟังก์ชันสำหรับ Cronjob/Worker เรียกใช้เพื่อส่งจริง
function sendEmailNow($toEmail, $toName, $subject, $bodyHtml, $imagePath = null) {
    $mail = new PHPMailer(true);

    try {
        // 1. ตั้งค่า Server (ตัวอย่างใช้ Gmail)
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        // ใช้ค่าจาก Environment Variable
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
        $mail->CharSet    = 'UTF-8';

        // 2. ผู้ส่งและผู้รับ
        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? $mail->Username;
        $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'Bangkok Spa Academy';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);

        // ฝังรูปภาพ
        if ($imagePath && file_exists($imagePath)) {
            $mail->addEmbeddedImage($imagePath, 'refund_slip_cid');
        }

        // 3. เนื้อหา
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
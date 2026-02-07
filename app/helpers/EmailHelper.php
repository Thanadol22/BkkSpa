<?php
// app/helpers/EmailHelper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// โหลดไฟล์ PHPMailer (ปรับ Path ตามจริง)
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';

// [แก้ไข] เพิ่มตัวแปร $imagePath = null เป็นตัวสุดท้ายในวงเล็บ
function sendEmail($toEmail, $toName, $subject, $bodyHtml, $imagePath = null) {
    $mail = new PHPMailer(true);

    try {
        // 1. ตั้งค่า Server (ตัวอย่างใช้ Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'thanadolpetch22@gmail.com';   // **ใส่อีเมลของคุณ**
        $mail->Password   = 'lazi cdal ysqq uvax';      // **ใส่รหัสผ่านแอป (App Password)**
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // 2. ผู้ส่งและผู้รับ
        $mail->setFrom('thanadolpetch22@gmail.com', 'Bangkok Spa Academy'); // ชื่อผู้ส่ง
        $mail->addAddress($toEmail, $toName);     // ผู้รับ

        // --- [ส่วนที่เพิ่มใหม่] ---
        // ถ้ามีการส่ง path รูปภาพมา ให้ทำการฝังรูป (Embed)
        // ตอนนี้ตัวแปร $imagePath จะใช้งานได้แล้ว เพราะเรารับค่าเข้ามาจากฟังก์ชันด้านบน
        if ($imagePath && file_exists($imagePath)) {
            // พารามิเตอร์: (Path ไฟล์จริง, CID ที่จะใช้ใน HTML)
            $mail->addEmbeddedImage($imagePath, 'refund_slip_cid');
        }
        // -----------------------

        // 3. เนื้อหา
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = strip_tags($bodyHtml); // สำหรับ Client ที่ไม่รองรับ HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        // error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
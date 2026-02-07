<?php
// public/process_mail_queue.php

// 1. Setup Environment
// ต้องกำหนด Base Path ให้ถูกต้อง
define('BASE_PATH', dirname(__DIR__));

// Load .env keys
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\''); // Remove quotes
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

define('APP_PATH', BASE_PATH . '/app');

// Include necessary files
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/helpers/EmailHelper.php'; // Contains sendEmailNow

// ป้องกันการเข้าถึงโดยตรงถ้าไม่ใช่ AJAX (Optional)
// if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
//     die('Restricted access');
// }

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();

    // 2. Lock & Select Pending Emails (Limit 2 per request to keep it fast)
    // ใช้ FOR UPDATE SKIP LOCKED ถ้า MySQL 8.0+ แต่ถ้า 5.x ใช้ธรรมดาไปก่อน
    $sql = "SELECT * FROM mail_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 2";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($emails)) {
        echo json_encode(['status' => 'empty', 'msg' => 'No pending emails']);
        exit;
    }

    $count = 0;
    foreach ($emails as $email) {
        // Mark as processing (เพื่อกัน process ซ้ำถ้า request ชนกัน)
        // แต่ถ้าจะให้ชัวร์ควร update status='processing' ก่อน
        
        // ส่งอีเมลจริง
        $sent = sendEmailNow(
            $email['to_email'], 
            $email['to_name'], 
            $email['subject'], 
            $email['body'], 
            $email['image_path']
        );

        // อัปเดตสถานะ
        $status = $sent ? 'sent' : 'failed';
        $error  = $sent ? null : 'Mailer Error'; // จริงๆควร return error จาก sendEmailNow ถ้าทำได้

        $updSql = "UPDATE mail_queue SET status = ?, updated_at = NOW() WHERE id = ?";
        $pdo->prepare($updSql)->execute([$status, $email['id']]);
        
        $count++;
    }

    echo json_encode(['status' => 'success', 'processed' => $count]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}

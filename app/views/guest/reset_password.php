<?php
    $error_message = '';
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
    }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - Bangkok Spa Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="main-container">
        <div class="left-side">
            <div class="auth-card">
                <h2 class="auth-title">เปลี่ยนรหัสผ่าน</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="alert" style="background-color:#f8d7da; color:#721c24;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?action=doResetPassword">
                    <div class="form-group">
                        <label class="form-label">ชื่อผู้ใช้งาน</label>
                        <input type="text" name="username" class="input-field" placeholder="กรอกชื่อผู้ใช้งานของคุณ" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">รหัสผ่านปัจจุบัน</label>
                        <input type="password" name="current_password" class="input-field" placeholder="กรอกรหัสผ่านเดิม" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" name="new_password" class="input-field" placeholder="กำหนดรหัสผ่านใหม่" required>
                    </div>

                    <button type="submit" class="btn-primary">บันทึกรหัสผ่านใหม่</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="right-content">
                <img src="assets/images/logo/logo.png" class="banner-logo" alt="BSA Logo">
                <h2 class="banner-title">ตรวจสอบความปลอดภัย</h2>
                <p class="banner-text">จำรหัสผ่านได้แล้ว?</p>
                <a href="index.php?action=login" class="btn-switch-page">เข้าสู่ระบบ</a>
            </div>
        </div>
    </div>
</body>
</html>
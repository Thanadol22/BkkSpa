<?php
    $error_message = '';
    if (isset($_SESSION['error'])) {
        $error_message = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    $success_message = '';
    if (isset($_SESSION['success'])) {
        $success_message = $_SESSION['success'];
        unset($_SESSION['success']);
    }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Bangkok Spa Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="main-container">
        <div class="left-side">
            <div class="auth-card">
                <h2 class="auth-title">เข้าสู่ระบบ</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="alert" style="background-color:#f8d7da; color:#721c24;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert" style="background-color:#d4edda; color:#155724;">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?action=doLogin">
                    <div class="form-group">
                        <label class="form-label">ชื่อผู้ใช้งาน</label>
                        <input type="text" name="username" class="input-field" placeholder="กรอกชื่อผู้ใช้งาน" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">รหัสผ่าน</label>
                        <input type="password" name="password" class="input-field" placeholder="กรอกรหัสผ่าน" required>
                    </div>

                    <div style="overflow: hidden;">
                        <a href="index.php?action=resetPassword" class="forgot-link">ลืมรหัสผ่าน?</a>
                    </div>

                    <button type="submit" class="btn-primary">เข้าสู่ระบบ</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="right-content">
                <img src="assets/images/logo/logo.png" class="banner-logo" alt="BSA Logo">
                <h2 class="banner-title">ยินดีต้อนรับกลับ</h2>
                <p class="banner-text">ยังไม่มีบัญชีผู้ใช้งาน?</p>
                <a href="index.php?action=register" class="btn-switch-page">สมัครสมาชิก</a>
            </div>
        </div>
    </div>
</body>
</html>
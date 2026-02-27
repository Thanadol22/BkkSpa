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
    <title>สมัครสมาชิก - Bangkok Spa Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="main-container">
        <div class="left-side">
            <div class="auth-card wide">
                <h2 class="auth-title">สมัครสมาชิกใหม่</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="alert" style="background-color:#f8d7da; color:#721c24;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?action=doRegister">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ชื่อ - นามสกุล</label>
                            <input type="text" name="full_name" class="input-field" placeholder="ชื่อ-นามสกุล" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="tel" name="phone" class="input-field" placeholder="08X-XXX-XXXX" 
                                   oninput="formatPhoneNumber(this)" 
                                   maxlength="12" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ชื่อธนาคาร</label>
                            <select name="bank_name" class="input-field">
                                <option value="">-- เลือกธนาคาร --</option>
                                <option value="ธนาคารกสิกรไทย (KBANK)">ธนาคารกสิกรไทย (KBANK)</option>
                                <option value="ธนาคารไทยพาณิชย์ (SCB)">ธนาคารไทยพาณิชย์ (SCB)</option>
                                <option value="ธนาคารกรุงเทพ (BBL)">ธนาคารกรุงเทพ (BBL)</option>
                                <option value="ธนาคารกรุงไทย (KTB)">ธนาคารกรุงไทย (KTB)</option>
                                <option value="ธนาคารกรุงศรีอยุธยา (BAY)">ธนาคารกรุงศรีอยุธยา (BAY)</option>
                                <option value="ธนาคารทหารไทยธนชาต (TTB)">ธนาคารทหารไทยธนชาต (TTB)</option>
                                <option value="ธนาคารออมสิน (GSB)">ธนาคารออมสิน (GSB)</option>
                                <option value="ธนาคารอาคารสงเคราะห์ (GHB)">ธนาคารอาคารสงเคราะห์ (GHB)</option>
                                <option value="ธนาคารเพื่อการเกษตรฯ (BAAC)">ธนาคารเพื่อการเกษตรฯ (BAAC)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">เลขที่บัญชี</label>
                            <input type="text" name="bank_account" class="input-field" placeholder="XXX-X-XXXXX-X"
                                   oninput="formatBankAccount(this)" maxlength="19">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" class="input-field" placeholder="example@mail.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ชื่อผู้ใช้งาน</label>
                            <input type="text" name="username" class="input-field" placeholder="ตั้งชื่อ Username" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">รหัสผ่าน</label>
                            <input type="password" name="password" class="input-field" placeholder="กำหนดรหัสผ่าน" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="password" name="confirm_password" class="input-field" placeholder="รหัสผ่านอีกครั้ง" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">ลงทะเบียน</button>
                </form>
            </div>
        </div>

        <div class="right-side">
            <div class="right-content">
                <img src="assets/images/logo/logo.png" class="banner-logo" alt="BSA Logo">
                <h2 class="banner-title">ยินดีต้อนรับกลับ</h2>
                <p class="banner-text">มีบัญชีผู้ใช้งานอยู่แล้ว?</p>
                <a href="index.php?action=login" class="btn-switch-page">เข้าสู่ระบบ</a>
            </div>
        </div>
    </div>
    <script>
        function formatPhoneNumber(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 10) value = value.substring(0, 10);
            let formatted = '';
            if (value.length > 3) {
                formatted += value.substring(0, 3) + '-';
                if (value.length > 6) {
                    formatted += value.substring(3, 6) + '-' + value.substring(6);
                } else {
                    formatted += value.substring(3);
                }
            } else {
                formatted = value;
            }
            input.value = formatted;
        }

        function formatBankAccount(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 15) value = value.substring(0, 15);
            let formatted = '';
            if (value.length > 3) {
                formatted += value.substring(0, 3) + '-';
                if (value.length > 4) {
                    formatted += value.substring(3, 4) + '-';
                    if (value.length > 9) {
                        formatted += value.substring(4, 9) + '-' + value.substring(9);
                    } else {
                        formatted += value.substring(4);
                    }
                } else {
                    formatted += value.substring(3);
                }
            } else {
                formatted = value;
            }
            input.value = formatted;
        }
    </script>
</body>
</html>
<div class="container" style="margin-top: 40px; margin-bottom: 60px;">

    <?= $message ?? '' ?>

    <div class="profile-layout">

      
        <main class="profile-content">

            <div id="tab-profile" class="tab-pane active">
                <h2 class="tab-title">ข้อมูลส่วนตัว</h2>

                <form method="post" action="index.php?action=staff_profile">

                    <h4 class="form-section-title">ข้อมูลทั่วไป</h4>
                    
                    <div class="form-group">
                        <label class="form-label">ชื่อ - นามสกุล</label>
                        <input type="text" name="full_name" class="input-field" value="<?= htmlspecialchars($staff['full_name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" class="input-field"
                            value="<?= htmlspecialchars($staff['phone'] ?? '') ?>"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            maxlength="10" placeholder="กรอกเฉพาะตัวเลข">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="input-field" value="<?= htmlspecialchars($staff['email'] ?? '') ?>">
                    </div>

                    <h4 class="form-section-title mt-4">เปลี่ยนรหัสผ่าน</h4>
                    
                    <div class="form-group">
                        <label class="form-label">รหัสผ่านปัจจุบัน</label>
                        <input type="password" name="current_password" class="input-field" placeholder="กรอกรหัสผ่านเดิมเพื่อยืนยัน">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" class="input-field" placeholder="ตั้งรหัสผ่านใหม่">
                        </div>
                        <div class="form-group">
                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="confirm_password" class="input-field" placeholder="กรอกอีกครั้ง">
                        </div>
                    </div>

                    <div style="text-align: left; margin-top: 20px;">
                        <button type="submit" class="btn-save">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>

<script>
    function openTab(tabName) {
        // เนื่องจากหน้าพนักงานมีแค่ Tab เดียว ฟังก์ชันนี้จึงมีไว้เพื่อรองรับการขยายในอนาคต
        // หรือเพื่อให้ UI ทำงานเหมือนหน้าสมาชิก
        document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

        document.getElementById(tabName).style.display = 'block';
        if (tabName === 'tab-profile') document.getElementById('btn-profile').classList.add('active');
    }
</script>
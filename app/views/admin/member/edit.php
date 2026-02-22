<div class="form-container">
    <h2 class="section-title">แก้ไขข้อมูลสมาชิก</h2>
    
    <form action="index.php?action=admin_member_save" method="POST">
        <input type="hidden" name="user_id" value="<?= $member['user_id'] ?? '' ?>">
        
        <div class="form-group">
            <label class="form-label">Username (สำหรับเข้าสู่ระบบ)</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($member['username'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($member['full_name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">รหัสผ่าน (เว้นว่างถ้าไม่เปลี่ยน)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="form-actions">
            <a href="index.php?action=admin_manage_member" class="link-cancel">ยกเลิก</a>
            <button type="submit" class="btn-primary">บันทึกข้อมูล</button>
        </div>
    </form>
</div>
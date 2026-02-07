<div class="section-container" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>แก้ไขข้อมูล: <?= htmlspecialchars($member['full_name']) ?></h3>
        <a href="index.php?action=staff_members" class="link-action"><i class="fas fa-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <form method="POST" action="index.php?action=staff_member_edit&id=<?= $member['user_id'] ?>" style="background: #fff; padding: 30px; border-radius: 10px; border: 1px solid #ddd;">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">ชื่อ-นามสกุล</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
            </div>
            
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">Username (แก้ไขไม่ได้)</label>
                <input type="text" value="<?= htmlspecialchars($member['username']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #eee; background: #f9f9f9; color: #777; border-radius: 5px;" readonly>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">อีเมล</label>
                <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">ธนาคาร</label>
                <select name="bank_name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">-- ไม่ระบุ --</option>
                    <?php 
                        $banks = ["ธนาคารกสิกรไทย (KBANK)", "ธนาคารไทยพาณิชย์ (SCB)", "ธนาคารกรุงเทพ (BBL)", "ธนาคารกรุงไทย (KTB)", "ธนาคารกรุงศรีอยุธยา (BAY)"];
                        foreach($banks as $b) {
                            $sel = ($member['bank_name'] == $b) ? 'selected' : '';
                            echo "<option value='$b' $sel>$b</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom: 5px; font-weight:600;">เลขที่บัญชี</label>
                <input type="text" name="bank_account" value="<?= htmlspecialchars($member['bank_account']) ?>" 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>
        </div>

        <div style="margin-top: 20px;">
            <label style="display:block; margin-bottom: 5px; font-weight:600;">สถานะการใช้งาน</label>
            <select name="is_active" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="1" <?= ($member['is_active'] == 1) ? 'selected' : '' ?>>ใช้งานปกติ (Active)</option>
                <option value="0" <?= ($member['is_active'] == 0) ? 'selected' : '' ?>>ระงับการใช้งาน (Banned)</option>
            </select>
        </div>

        <div style="margin-top: 30px; text-align: right;">
            <a href="index.php?action=staff_members" class="btn-quick-gray" style="margin-right: 10px;">ยกเลิก</a>
            <button type="submit" class="btn-quick-green" style="border: none; cursor: pointer;">บันทึกการเปลี่ยนแปลง</button>
        </div>

    </form>
</div>
<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0;">แก้ไขโปรโมชั่นหลักสูตร</h2>
        <p style="font-size: 14px; color: #888; margin-top: 5px;">แก้ไขส่วนลดและระยะเวลาสำหรับ <?= htmlspecialchars($promo['item_name']) ?></p>
    </div>

    <form method="POST" action="index.php?action=staff_promotion_course_update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $promo['promotion_c_id'] ?>">

        <div class="form-group">
            <label class="form-label">หลักสูตร</label>
            <input type="text" class="form-input" value="<?= htmlspecialchars($promo['item_name']) ?>" disabled style="background-color: #f8f9fa;">
        </div>

        <div class="form-group">
            <label class="form-label">ส่วนลด (%)</label>
            <input type="number" name="discount" class="form-input" value="<?= $promo['discount'] ?>" min="0" max="100" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">วันที่เริ่มโปรโมชั่น</label>
                <input type="datetime-local" name="start_at" class="form-input" value="<?= date('Y-m-d\TH:i', strtotime($promo['start_at'])) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">วันที่สิ้นสุดโปรโมชั่น</label>
                <input type="datetime-local" name="end_at" class="form-input" value="<?= date('Y-m-d\TH:i', strtotime($promo['end_at'])) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">รูปภาพโปรโมชั่น (อัปโหลดใหม่เพื่อเปลี่ยน)</label>
            <?php if (!empty($promo['promotion_p_picture'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?= htmlspecialchars($promo['promotion_p_picture']) ?>" style="height: 100px; border-radius: 6px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
            <input type="file" name="promotion_picture" class="form-input" accept="image/*">
            <p style="font-size: 12px; color: #888; margin-top: 5px;">* หากไม่อัปโหลดจะใช้รูปเดิม</p>
        </div>

        <div class="form-group">
            <label class="form-label">สถานะการแสดงผล</label>
            <select name="visible" class="form-input">
                <option value="1" <?= $promo['visible'] == 1 ? 'selected' : '' ?>>เปิดใช้งาน (Visible)</option>
                <option value="0" <?= $promo['visible'] == 0 ? 'selected' : '' ?>>ปิดใช้งาน (Hidden)</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="index.php?action=staff_promotion_list" class="btn-cancel">ยกเลิก</a>
            <button type="submit" class="btn-save">บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

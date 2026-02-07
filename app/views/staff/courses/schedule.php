<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0 0 10px 0;">เปิดรอบเรียนใหม่</h2>
        <p style="color: #666; font-size: 14px;">
            หลักสูตร: <strong style="color: var(--primary-green);"><?= htmlspecialchars($course['name']) ?></strong>
        </p>
    </div>

    <form method="POST" action="index.php?action=staff_course_open_schedule" enctype="multipart/form-data">
        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">วันที่เริ่มเรียน</label>
                <input type="date" name="start_at" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" name="end_at" class="form-input" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">จำนวนที่รับ (คน)</label>
            <input type="number" name="capacity" class="form-input" min="1" value="10" placeholder="ระบุจำนวนคน" required>
        </div>

        <div class="form-group">
            <label class="form-label">อัปเดตรูปปก (ถ้าต้องการเปลี่ยน)</label>
            <div class="upload-area">
                <div class="upload-content">
                    <p class="upload-title">อัปโหลดรูปภาพ</p>
                    <p class="upload-desc">ลากไฟล์มาวาง หรือคลิกเพื่ออัปโหลด</p>
                    <span class="btn-choose-file">เลือกไฟล์</span>
                </div>
                <input type="file" name="course_picture" id="course_picture" class="hidden-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <?php if($course['course_picture']): ?>
                    <img src="<?= $course['course_picture'] ?>" style="height: 120px; border-radius: 8px; border: 1px solid #eee; opacity: 0.7;">
                    <p style="font-size: 12px; color: #999;">รูปปัจจุบัน</p>
                <?php endif; ?>
                <img id="img-preview" class="preview-img" src="#" alt="New Preview">
                <p id="file-name" style="font-size: 13px; color: var(--primary-green); margin-top: 5px; font-weight: 500;"></p>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?action=staff_courses" class="btn-back">ย้อนกลับ</a>
            <button type="submit" class="btn-save">ยืนยันการเปิดสอน</button>
        </div>
    </form>
</div>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('img-preview');
            img.src = e.target.result; img.style.display = 'inline-block';
        }
        reader.readAsDataURL(input.files[0]);
        document.getElementById('file-name').innerText = "ไฟล์ที่เลือก: " + input.files[0].name;
    }
}
</script>
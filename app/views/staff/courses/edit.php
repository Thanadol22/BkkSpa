<div class="course-form-container">
   

    <form method="POST" action="index.php?action=staff_course_edit&id=<?= $course['course_id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="old_picture" value="<?= htmlspecialchars($course['course_picture']) ?>">
        
        <div class="form-group">
            <label class="form-label">ชื่อหลักสูตร</label>
            <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($course['name']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">รายละเอียด</label>
            <textarea name="description" class="form-textarea" required><?= htmlspecialchars($course['description']) ?></textarea>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ราคา (บาท)</label>
                <input type="number" name="price" class="form-input" value="<?= $course['price'] ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">ระยะเวลาเรียน (วัน)</label>
                <input type="number" name="duration_day" class="form-input" value="<?= $course['duration_day'] ?>" required>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ประเภทหลักสูตร</label>
                <select name="course_type" class="form-select">
                    <option value="สปาเพื่อสุขภาพ" <?= $course['course_type']=='สปาเพื่อสุขภาพ'?'selected':'' ?>>สปาเพื่อสุขภาพ</option>
                    <option value="สปาเพื่อความงาม" <?= $course['course_type']=='สปาเพื่อความงาม'?'selected':'' ?>>สปาเพื่อความงาม</option>
                    <option value="สปาขั้นสูง" <?= $course['course_type']=='สปาขั้นสูง'?'selected':'' ?>>สปาขั้นสูง</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">สถานะ</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $course['is_active']==1?'selected':'' ?>>เปิดสอน (Active)</option>
                    <option value="0" <?= $course['is_active']==0?'selected':'' ?>>ปิดรับ (Inactive)</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">เปลี่ยนรูปภาพปก (ถ้ามี)</label>
            <div class="upload-area">
                <div class="upload-content">
                    <p class="upload-title">อัปโหลดรูปภาพใหม่</p>
                    <p class="upload-desc">ลากไฟล์มาวาง หรือคลิกเพื่อเปลี่ยนรูป</p>
                    <span class="btn-choose-file">เลือกไฟล์</span>
                </div>
                <input type="file" name="course_picture" id="course_picture" class="hidden-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div style="text-align: center; margin-top: 15px;">
                <?php if($course['course_picture']): ?>
                    <div style="margin-bottom:10px;">
                        <span style="font-size:12px; color:#999;">รูปปัจจุบัน:</span><br>
                        <img id="current-img" src="<?= $course['course_picture'] ?>" style="height: 100px; border-radius: 8px; border: 1px solid #eee;">
                    </div>
                <?php endif; ?>
                <img id="img-preview" class="preview-img" src="#" alt="New Preview">
                <p id="file-name" style="font-size: 13px; color: var(--primary-green); margin-top: 5px;"></p>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?action=staff_courses" class="btn-back">ย้อนกลับ</a>
            <button type="submit" class="btn-save">บันทึกการแก้ไข</button>
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
            if(document.getElementById('current-img')) document.getElementById('current-img').style.opacity = '0.5';
        }
        reader.readAsDataURL(input.files[0]);
        document.getElementById('file-name').innerText = "ไฟล์ใหม่ที่เลือก: " + input.files[0].name;
    }
}

</script>
<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0;">สร้างโปรโมชั่นหลักสูตร</h2>
        <p style="font-size: 14px; color: #888; margin-top: 5px;">กำหนดส่วนลดและระยะเวลาสำหรับหลักสูตร</p>
    </div>

    <form method="POST" action="index.php?action=staff_promotion_course_store" enctype="multipart/form-data">

        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <label class="form-label" style="margin:0;">เลือกหลักสูตรที่ร่วมรายการ</label>

                <div style="display: flex; align-items: center;">

                    <label for="selectAllProducts"
                        style="font-size: 13px; cursor: pointer; color: var(--primary-green); font-weight: 600; margin-right: 8px; margin-bottom: 0;">
                        เลือกทั้งหมด
                    </label>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="selectAllProducts" onchange="toggleSelectAllProducts(this)">
                        <label class="custom-control-label" for="selectAllProducts"></label>
                    </div>

                </div>
            </div>

            <div class="checkbox-list-container">
                <?php foreach ($courses as $c): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="course_id[]" value="<?= $c['course_id'] ?>" class="course-checkbox">
                        <span class="item-name"><?= htmlspecialchars($c['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <p style="font-size: 12px; color: #888; margin-top: 5px;">
                * สามารถติ๊กเลือกได้มากกว่า 1 รายการ
            </p>
        </div>

        <div class="form-group">
            <label class="form-label">ส่วนลด (%)</label>
            <input type="number" name="discount" class="form-input" placeholder="เช่น 10, 20" min="0" max="100" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">วันที่เริ่มโปรโมชั่น</label>
                <input type="datetime-local" name="start_at" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">วันที่สิ้นสุดโปรโมชั่น</label>
                <input type="datetime-local" name="end_at" class="form-input" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">สถานะการแสดงผล</label>
            <select name="visible" class="form-select">
                <option value="1">เปิดใช้งาน (Visible)</option>
                <option value="0">ซ่อน (Hidden)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">รูปภาพแบนเนอร์โปรโมชั่น (ถ้ามี)</label>
            <div class="upload-area">
                <div class="upload-content">
                    <p class="upload-title">อัปโหลดรูปภาพ</p>
                    <p class="upload-desc">ถ้าไม่ใส่ จะใช้รูปปกหลักสูตรปกติ</p>
                    <span class="btn-choose-file">เลือกไฟล์</span>
                </div>
                <input type="file" name="promotion_picture" class="hidden-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div style="text-align: center;">
                <img id="img-preview" class="preview-img" src="#" alt="Preview" style="display:none; margin-top:15px; max-height:200px; border-radius:10px;">
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?action=staff_promotion_list" class="btn-back">ย้อนกลับ</a>
            <button type="submit" class="btn-save">บันทึกโปรโมชั่น</button>
        </div>
    </form>
</div>
<script>
    // ฟังก์ชันเลือกทั้งหมด สำหรับ Checkbox
    function toggleSelectAllProducts(source) {
        // หา input ทุกตัวที่มีคลาส course-checkbox
        const checkboxes = document.querySelectorAll('.course-checkbox');

        // วนลูปกำหนดสถานะ checked ตามตัวแม่ (source)
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
        });
    }

    // ฟังก์ชัน Preview รูปภาพ (New)
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // แสดงรูปตัวอย่าง
                var img = document.getElementById('img-preview');
                img.src = e.target.result;
                img.style.display = 'inline-block';

                // อัปเดตข้อความให้รู้ว่าเลือกไฟล์แล้ว
                const container = input.closest('.upload-area');
                const title = container.querySelector('.upload-title');
                const desc = container.querySelector('.upload-desc');
                const btn = container.querySelector('.btn-choose-file');

                if(title && desc && btn) {
                    title.innerHTML = '<i class="fas fa-check-circle" style="color:#28a745;"></i> อัปโหลดเรียบร้อย';
                    desc.innerText = input.files[0].name; // แสดงชื่อไฟล์
                    desc.style.color = '#28a745';
                    desc.style.fontWeight = 'bold';
                    
                    btn.innerText = 'เปลี่ยนรูปภาพ';
                    btn.style.backgroundColor = '#6c757d';
                    container.style.borderColor = '#28a745'; // เปลี่ยนสีขอบเป็นเขียว
                    container.style.backgroundColor = '#f0fff4'; // เปลี่ยนพื้นหลังให้อ่อนๆ
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
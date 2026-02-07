<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0;">สร้างโปรโมชั่นสินค้า</h2>
        <p style="font-size: 14px; color: #888; margin-top: 5px;">กำหนดส่วนลดและระยะเวลาสำหรับสินค้า</p>
    </div>

    <form method="POST" action="index.php?action=staff_promotion_product_store" enctype="multipart/form-data">

        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <label class="form-label" style="margin:0;">เลือกสินค้าที่ร่วมรายการ</label>

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
                <?php foreach ($products as $p): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="product_id[]" value="<?= $p['product_id'] ?>" class="product-checkbox">
                        <span class="item-name"><?= htmlspecialchars($p['name']) ?></span>
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
                    <p class="upload-desc">ถ้าไม่ใส่ จะใช้รูปสินค้าปกติ</p>
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
    function toggleSelectAllProducts(source) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
        });
    }

    // ฟังก์ชัน Preview รูปภาพ (ของเดิม)
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('img-preview');
                img.src = e.target.result;
                img.style.display = 'inline-block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
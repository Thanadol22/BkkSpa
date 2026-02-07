<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0;">แก้ไขข้อมูลสินค้า</h2>
        <p style="font-size: 14px; color: #888; margin-top: 5px;">แก้ไขรายละเอียด ราคา หรือรูปภาพสินค้า</p>
    </div>

    <form method="POST" action="index.php?action=staff_product_update&id=<?= $product['product_id'] ?>" enctype="multipart/form-data">
        
        <div class="form-group">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">รายละเอียดสินค้า</label>
            <textarea name="description" class="form-textarea" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ราคา (บาท)</label>
                <input type="number" name="price" class="form-input" value="<?= $product['price'] ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">รหัสสินค้า (SKU)</label>
                <input type="text" name="sku" class="form-input" value="<?= htmlspecialchars($product['sku']) ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">สต็อกปัจจุบัน (แก้ไขที่หน้าจัดการสต็อก)</label>
                <input type="number" class="form-input" value="<?= $product['stock'] ?>" disabled style="background-color: #f9f9f9; color: #999;">
            </div>
            <div class="form-group">
                <label class="form-label">ประเภทสินค้า</label>
                <select name="product_type" class="form-select">
                    <?php 
                        $types = [
                            'Body Scrub' => 'สครับขัดผิว', 
                            'Body Mask' => 'มาสก์พอกตัว', 
                            'Body Massage Oil' => 'น้ำมันนวดตัว',

                        ];
                        foreach($types as $key => $label): 
                    ?>
                        <option value="<?= $key ?>" <?= ($product['product_type'] == $key) ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">รูปภาพสินค้า</label>
            
            <?php if(!empty($product['product_picture'])): ?>
                <div style="margin-bottom: 10px; text-align: center;">
                    <p style="font-size:12px; color:#888;">รูปภาพปัจจุบัน:</p>
                    <img src="<?= $product['product_picture'] ?>" style="height: 100px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>

            <div class="upload-area">
                <div class="upload-content">
                    <p class="upload-title">เปลี่ยนรูปภาพใหม่</p>
                    <p class="upload-desc">ถ้าไม่ต้องการเปลี่ยน ให้เว้นว่างไว้</p>
                    <span class="btn-choose-file">เลือกไฟล์ใหม่</span>
                </div>
                <input type="file" name="product_picture" class="hidden-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div style="text-align: center;">
                <img id="img-preview" class="preview-img" src="#" alt="Preview" style="display:none; margin-top:15px; max-height:200px; border-radius:10px;">
                <p id="file-name" style="font-size: 13px; color: var(--primary-green); margin-top: 5px;"></p>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">สถานะการใช้งาน</label>
            <select name="is_active" class="form-select">
                <option value="1" <?= ($product['is_active'] == 1) ? 'selected' : '' ?>>เปิดใช้งาน (Active)</option>
                <option value="0" <?= ($product['is_active'] == 0) ? 'selected' : '' ?>>ปิดใช้งาน (Inactive)</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="index.php?action=staff_product_list" class="btn-back">ยกเลิก</a>
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
            img.src = e.target.result; 
            img.style.display = 'inline-block';
        }
        reader.readAsDataURL(input.files[0]);
        document.getElementById('file-name').innerText = "ไฟล์ที่เลือก: " + input.files[0].name;
    }
}
</script>
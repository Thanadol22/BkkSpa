<div class="course-form-container">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 600; color: #333; margin: 0;">เพิ่มหลักสูตรใหม่</h2>
        <p style="font-size: 14px; color: #888; margin-top: 5px;">กรอกข้อมูลรายละเอียดของหลักสูตร</p>
    </div>

    <form method="POST" action="index.php?action=staff_course_create" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">ชื่อหลักสูตร</label>
            <input type="text" name="name" class="form-input" placeholder="ระบุชื่อหลักสูตร" required>
        </div>
        <div class="form-group">
            <label class="form-label">รายละเอียด</label>
            <textarea name="description" class="form-textarea" placeholder="ระบุรายละเอียดของหลักสูตร..." required></textarea>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ราคา (บาท)</label>
                <input type="number" name="price" class="form-input" placeholder="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">ระยะเวลาเรียน (วัน)</label>
                <input type="number" name="duration_day" class="form-input" placeholder="0" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">ประเภทหลักสูตร</label>
            <div class="input-group-row">
                <select name="course_type" id="course_type_select" class="form-select">
                    <option value="สปาเพื่อสุขภาพ">สปาเพื่อสุขภาพ</option>
                    <option value="สปาเพื่อความงาม">สปาเพื่อความงาม</option>
                    <option value="สปาขั้นสูง">สปาขั้นสูง</option>
                    <?php if (isset($course['course_type']) && !in_array($course['course_type'], ['สปาเพื่อสุขภาพ', 'สปาเพื่อความงาม', 'สปาขั้นสูง'])): ?>
                        <option value="<?= htmlspecialchars($course['course_type']) ?>" selected><?= htmlspecialchars($course['course_type']) ?></option>
                    <?php endif; ?>
                </select>

                <button type="button" class="btn-add-type" onclick="addNewOption('course_type_select')" title="เพิ่มประเภทใหม่">+</button>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">รูปภาพปก</label>
            <div class="upload-area">
                <div class="upload-content">
                    <p class="upload-title">อัปโหลดรูปภาพ</p>
                    <p class="upload-desc">ลากไฟล์มาวาง หรือคลิกเพื่อเลือกไฟล์</p>
                    <span class="btn-choose-file">เลือกไฟล์</span>
                </div>
                <input type="file" name="course_picture" id="course_picture" class="hidden-input" accept="image/*" onchange="previewImage(this)">
            </div>
            <div style="text-align: center;">
                <img id="img-preview" class="preview-img" src="#" alt="Preview">
                <p id="file-name" style="font-size: 13px; color: var(--primary-green); margin-top: 5px;"></p>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?action=staff_courses" class="btn-back">ย้อนกลับ</a>
            <button type="submit" class="btn-save">บันทึก</button>
        </div>
    </form>
 <div id="addTypeModal" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> เพิ่มประเภทใหม่</h3>
            </div>
            <div class="modal-body">
                <p>กรุณาระบุชื่อประเภทที่ต้องการเพิ่ม:</p>
                <input type="text" id="newTypeInput" class="form-input" placeholder="เช่น สบู่สมุนไพร, นวดประคบ" autocomplete="off">
                <p id="modalError" style="color: red; font-size: 13px; display: none; margin-top: 5px;">* กรุณาระบุข้อมูล</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">ยกเลิก</button>
                <button type="button" class="btn-confirm" onclick="confirmAddType()">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ตัวแปรสำหรับจำว่า กดปุ่มมาจาก Select ตัวไหน (สินค้า หรือ หลักสูตร)
    let currentSelectId = '';

    // 1. ฟังก์ชันเปิด Modal
    function addNewOption(selectId) {
        currentSelectId = selectId; // จำไว้ว่ากดมาจากช่องไหน
        document.getElementById('newTypeInput').value = ''; // ล้างค่าเก่า
        document.getElementById('modalError').style.display = 'none'; // ซ่อน error
        document.getElementById('addTypeModal').classList.add('show'); // แสดง Modal
        document.getElementById('newTypeInput').focus(); // โฟกัสที่ช่องพิมพ์
    }

    // 2. ฟังก์ชันปิด Modal
    function closeModal() {
        document.getElementById('addTypeModal').classList.remove('show');
    }

    // 3. ฟังก์ชันเมื่อกด "ยืนยัน"
    function confirmAddType() {
        const input = document.getElementById('newTypeInput');
        const newType = input.value.trim();

        if (newType === "") {
            document.getElementById('modalError').style.display = 'block';
            return;
        }

        // เพิ่มค่าลงใน Select
        const selectBox = document.getElementById(currentSelectId);
        const newOption = document.createElement("option");
        newOption.text = newType;
        newOption.value = newType;
        newOption.selected = true; // เลือกตัวนี้ทันที

        selectBox.add(newOption);

        // ปิด Modal
        closeModal();
    }

    // (แถม) กด Enter ในช่องพิมพ์เพื่อยืนยันได้เลย
    document.getElementById('newTypeInput').addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            confirmAddType();
        }
    });

    // (แถม) คลิกพื้นหลังสีดำเพื่อปิด
    window.onclick = function(event) {
        const modal = document.getElementById('addTypeModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // ฟังก์ชันเดิมสำหรับ Preview รูปภาพ
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
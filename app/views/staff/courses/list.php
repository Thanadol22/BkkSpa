<div class="section-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin:0; font-size:20px; color:var(--primary-green);">หลักสูตรทั้งหมด</h3>
        <a href="index.php?action=staff_course_create" class="btn-quick-green">
            <i class="fas fa-plus"></i> เพิ่มหลักสูตรใหม่
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th style="width: 80px;">รูปภาพ</th>
                    <th>ชื่อหลักสูตร</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                    <th style="text-align: right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($courses)): ?>
                    <?php foreach($courses as $c): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($c['course_picture'] ?? 'assets/images/logo/banner2.png') ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 15px; color: #333;"><?= htmlspecialchars($c['name']) ?></div>
                            <small style="color:#888;"><?= $c['course_type'] ?> • <?= $c['duration_day'] ?> วัน</small>
                        </td>
                        <td style="font-weight:500;"><?= number_format($c['price'], 0) ?> บาท</td>
                        <td>
                            <?php if($c['is_active'] == 1): ?>
                                <span class="status-badge badge-approved">เปิดสอน</span>
                            <?php else: ?>
                                <span class="status-badge badge-gray">ปิดรับ</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" 
                                    class="btn-icon" 
                                    title="เปิดรอบเรียนใหม่"
                                    onclick="openScheduleModal(<?= $c['course_id'] ?>, '<?= htmlspecialchars($c['name']) ?>')">
                                <i class="fas fa-calendar-plus" style="color: var(--primary-green);"></i>
                            </button>

                            <a href="index.php?action=staff_course_details&id=<?= $c['course_id'] ?>" 
                               class="btn-icon" title="ดูประวัติรอบเรียน">
                                <i class="fas fa-list-ul" style="color: #007bff;"></i>
                            </a>

                            <a href="index.php?action=staff_course_edit&id=<?= $c['course_id'] ?>" 
                               class="btn-icon" title="แก้ไข">
                                <i class="fas fa-edit" style="color: #FFC107;"></i>
                            </a>

                            <?php if($c['is_active'] == 1): ?>
                            <a href="index.php?action=staff_course_delete&id=<?= $c['course_id'] ?>" 
                               class="btn-icon" title="ปิดการใช้งาน"
                               onclick="return confirm('ต้องการปิดการใช้งานหลักสูตรนี้ใช่หรือไม่?');">
                                <i class="fas fa-trash-alt" style="color: #dc3545;"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 40px; color:#888;">ไม่พบข้อมูลหลักสูตร</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="modal-card">
        <form action="index.php?action=staff_course_open_schedule" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="course_id" id="modal_course_id">
            
            <div class="modal-header">
                <div class="modal-title-group">
                    <h3 id="modal_course_name">ชื่อหลักสูตร</h3>
                    <p>กำหนดวันและจำนวนผู้เรียนเพื่อเปิดรับสมัคร</p>
                </div>
                <button type="button" class="btn-close-x" onclick="closeScheduleModal()"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="modal-body">
                <div class="modal-grid-layout">
                    
                    <div class="left-col">
                        <span class="modal-section-title">เลือกช่วงเวลาเรียน</span>
                        <div class="date-input-group">
                            <div class="date-row">
                                <label>วันที่เริ่มเรียน (Start Date)</label>
                                <input type="date" name="start_at" class="custom-date-input" required>
                            </div>
                            <div class="date-row">
                                <label>วันที่สิ้นสุด (End Date)</label>
                                <input type="date" name="end_at" class="custom-date-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="right-col">
                        <span class="modal-section-title">จำนวนที่รับ</span>
                        <input type="number" name="capacity" class="capacity-input" placeholder="0" min="1" value="10" required>
                        <span class="capacity-unit">คน / รอบ</span>

                        
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-cancel" onclick="closeScheduleModal()">ยกเลิก</button>
                <button type="submit" class="btn-modal-confirm">ยืนยัน</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('scheduleModal');

    function openScheduleModal(id, name) {
        document.getElementById('modal_course_id').value = id;
        document.getElementById('modal_course_name').innerText = name;
        modal.classList.add('show'); // แสดง Modal
    }

    function closeScheduleModal() {
        modal.classList.remove('show'); // ซ่อน Modal
        document.getElementById('file-name-display').innerText = ""; // ล้างชื่อไฟล์
    }

    function previewFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('file-name-display').innerText = "เลือกไฟล์: " + input.files[0].name;
        }
    }

    // ปิดเมื่อคลิกพื้นหลัง
    window.onclick = function(event) {
        if (event.target == modal) closeScheduleModal();
    }
</script>
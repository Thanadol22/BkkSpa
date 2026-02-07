<div class="section-container attendance-container">
    
    <div class="attendance-page-header">
        <div>
            <h1 class="attendance-title"><i class="fas fa-user-check"></i> เช็กชื่อเข้าเรียน</h1>
        </div>
        
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="staff_attendance_checkin">
            <div class="attendance-date-picker">
                <span style="margin-right:10px; color:#666; font-size:14px;">วันที่:</span>
                <input type="date" name="date" value="<?= $filter_date ?>" 
                       style="border:none; outline:none; color:#333; font-family:inherit; font-weight:600;"
                       onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <?php if (empty($scheduleData)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times" style="font-size: 40px; margin-bottom: 20px; color: #ddd;"></i>
            <p style="font-size: 16px;">ไม่พบตารางเรียนในวันที่เลือก</p>
        </div>
    <?php else: ?>

        <?php foreach ($scheduleData as $sch): ?>
            <div class="course-card">
                
                <h3 class="course-header">
                    <?= htmlspecialchars($sch['course_name']) ?>
                    <span class="course-time">
                        <i class="far fa-clock"></i> <?= date('H:i', strtotime($sch['start_at'])) ?> - <?= date('H:i', strtotime($sch['end_at'])) ?>
                    </span>
                </h3>

                <form method="POST" action="index.php?action=staff_attendance_save">
                    <input type="hidden" name="schedule_id" value="<?= $sch['schedule_id'] ?>">
                    <input type="hidden" name="redirect_date" value="<?= $filter_date ?>">
                    <input type="hidden" name="attendance_date" value="<?= $filter_date ?>">

                    <div class="attendance-box">
                        
                        <div class="attendance-box-header">
                            <span class="attendance-col-name">รายชื่อนักเรียน</span>
                            <div class="check-all-wrapper">
                                <label for="check-all-<?= $sch['schedule_id'] ?>" class="check-all-label">เลือกทั้งหมด</label>
                                <input type="checkbox" id="check-all-<?= $sch['schedule_id'] ?>" 
                                       class="check-all-box custom-checkbox" 
                                       data-target="list-<?= $sch['schedule_id'] ?>">
                            </div>
                        </div>

                        <div id="list-<?= $sch['schedule_id'] ?>">
                            <?php if (!empty($sch['students'])): ?>
                                <?php foreach ($sch['students'] as $stu): 
                                    $isChecked = ($stu['attendance_status'] == 1) ? 'checked' : '';
                                ?>
                                    <div class="student-row">
                                        <span class="student-name">
                                            <?= htmlspecialchars($stu['full_name']) ?>
                                        </span>
                                        <input type="checkbox" name="present_users[]" value="<?= $stu['user_id'] ?>" <?= $isChecked ?>
                                               class="student-check custom-checkbox">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 40px; text-align: center; color: #9ca3af;">
                                    ยังไม่มีนักเรียนลงทะเบียนในคลาสนี้
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($sch['students'])): ?>
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="submit" class="btn-save-attendance">
                                <i class="fas fa-save" style="margin-right: 5px;"></i> บันทึกการเช็กชื่อ
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<script>
    // Logic สำหรับปุ่ม เลือกทั้งหมด (Check All)
    document.querySelectorAll('.check-all-box').forEach(box => {
        box.addEventListener('change', function() {
            const targetId = this.getAttribute('data-target');
            const container = document.getElementById(targetId);
            const checkboxes = container.querySelectorAll('.student-check');
            
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });
</script>
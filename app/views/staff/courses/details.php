<div class="section-container">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="index.php?action=staff_courses" class="link-action"><i class="fas fa-arrow-left"></i> กลับไปหน้าหลักสูตร</a>
            <h2 style="margin-top: 10px; color: var(--primary-green);"><?= htmlspecialchars($course['name']) ?></h2>
            <p style="color: #666; font-size: 14px;">ประวัติการเปิดรอบเรียนทั้งหมด</p>
        </div>
        <a href="index.php?action=staff_course_schedule&id=<?= $course['course_id'] ?>" class="btn-quick-green">
            <i class="fas fa-plus"></i> เปิดรอบใหม่
        </a>
    </div>

    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>วันที่เริ่ม</th>
                    <th>วันที่สิ้นสุด</th>
                    <th>สถานะ</th>
                    <th>ผู้สมัคร (คน)</th>
                    <th>รับได้ทั้งหมด</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($schedules)): ?>
                    <?php $i=1; foreach($schedules as $s): ?>
                    <?php 
                        $today = date('Y-m-d');
                        $statusText = ''; $statusClass = '';
                        if ($s['start_at'] > $today) {
                            $statusText = 'ยังไม่เริ่ม'; $statusClass = 'badge-approved';
                        } elseif ($s['end_at'] < $today) {
                            $statusText = 'จบแล้ว'; $statusClass = 'badge-gray';
                        } else {
                            $statusText = 'กำลังเรียน'; $statusClass = 'badge-pending';
                        }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= date('d/m/Y', strtotime($s['start_at'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($s['end_at'])) ?></td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td><span style="font-weight: bold; color: var(--primary-green); font-size: 16px;"><?= $s['booked_count'] ?></span></td>
                        <td>/ <?= $s['capacity'] ?> คน</td>
                        <td>
                            <a href="index.php?action=staff_schedule_history&id=<?= $s['schedule_id'] ?>" class="btn-sm btn-info text-white" style="text-decoration:none;">
                                <i class="fas fa-history"></i> ประวัติเข้าเรียน
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding: 40px; color:#888;">ยังไม่มีประวัติการเปิดรอบเรียน</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">ประวัติรอบเรียน: <?= htmlspecialchars($course['name']) ?></h1>
            <p class="text-muted">เลือกรายการรอบเรียนเพื่อพิมพ์ใบเช็คชื่อ/รายงานประวัติ</p>
        </div>
        <div>
            <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายละเอียด
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายการรอบเรียนทั้งหมด</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="scheduleTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>วันที่เรียน (เริ่ม - จบ)</th>
                            <th>จำนวนวัน</th>
                            <th>ความจุ</th>
                            <th>ผู้ลงทะเบียน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="7" class="text-center">ไม่พบข้อมูลรอบเรียน</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $sch): 
                                $startDate = date('d/m/Y', strtotime($sch['start_at']));
                                $endDate = date('d/m/Y', strtotime($sch['end_at']));
                                
                                $startTs = strtotime($sch['start_at']);
                                $nowTs = time();
                                
                                $status = "";
                                if ($nowTs < $startTs) {
                                    $status = '<span class="badge badge-info">เปิดรับสมัคร</span>';
                                } elseif ($nowTs >= $startTs && $nowTs <= strtotime($sch['end_at'])) {
                                    $status = '<span class="badge badge-success">กำลังเรียน</span>';
                                } else {
                                    $status = '<span class="badge badge-secondary">จบแล้ว</span>';
                                }
                            ?>
                                <tr>
                                    <td>#<?= $sch['schedule_id'] ?></td>
                                    <td>
                                        <i class="far fa-calendar-alt text-muted"></i> <?= $startDate ?> - <?= $endDate ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $diff = (strtotime($sch['end_at']) - strtotime($sch['start_at'])) / (60 * 60 * 24);
                                            echo round($diff) + 1;
                                        ?> วัน
                                    </td>
                                    <td><?= number_format($sch['capacity']) ?></td>
                                    <td>
                                        <strong><?= $sch['student_count'] ?></strong> คน
                                    </td>
                                    <td><?= $status ?></td>
                                    <td>
                                        <a href="index.php?action=staff_schedule_history&id=<?= $sch['schedule_id'] ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-print"></i> ใบเช็คชื่อ (PDF)
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

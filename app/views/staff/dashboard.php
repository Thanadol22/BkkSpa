<div class="dashboard-stats-grid">
    <div class="stat-card card-border-warning">
        <div class="stat-content">
            <h3 class="stat-title">การจองใหม่ (รอตรวจสอบ)</h3>
            <div class="stat-value text-warning">
                <?= $stats['new_bookings'] ?? 0 ?>
            </div>
            <p class="stat-desc">รายการที่ต้องดำเนินการ</p>
        </div>
        <div class="stat-icon-right icon-bg-warning">
            <i class="fas fa-clipboard-list"></i>
        </div>
    </div>

    <div class="stat-card card-border-success">
        <div class="stat-content">
            <h3 class="stat-title">โปรโมชั่นที่ใช้งานอยู่</h3>
            <div class="stat-value text-success">
                <?= $stats['active_promotions'] ?? 0 ?>
            </div>
            <p class="stat-desc">แคมเปญทั้งหมด</p>
        </div>
        <div class="stat-icon-right icon-bg-success">
            <i class="fas fa-bullhorn"></i>
        </div>
    </div>

    <div class="stat-card card-border-info">
        <div class="stat-content">
            <h3 class="stat-title">ยอดขายวันนี้</h3>
            <div class="stat-value text-info">
                <?= number_format($stats['daily_sales'] ?? 0) ?>
            </div>
            <p class="stat-desc">บาท (THB)</p>
        </div>
        <div class="stat-icon-right icon-bg-info">
            <i class="fas fa-coins"></i>
        </div>
    </div>
</div>
<div class="stat-card card-border-danger">
    <div class="stat-content">
        <h3 class="stat-title">รอคืนเงิน (Refund)</h3>
        <div class="stat-value text-danger">
            <?= $stats['refund_pending'] ?? 0 ?>
        </div>
        <p class="stat-desc">รายการที่ลูกค้ายกเลิก</p>
    </div>
    <div class="stat-icon-right icon-bg-danger">
        <i class="fas fa-undo-alt"></i>
    </div>
</div>

<div class="section-container" style="padding: 25px; margin-bottom: 30px;">
    <h3 style="margin-top: 0; font-size: 18px; color: var(--primary-green); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <i class="fas fa-bolt"></i> เมนูด่วน (Quick Actions)
    </h3>

    <div class="action-buttons-grid">
        <a href="index.php?action=staff_members" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-users"></i></div>
            <span>จัดการสมาชิก</span>
        </a>

        <a href="index.php?action=staff_courses" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-book-open"></i></div>
            <span>จัดการหลักสูตร</span>
        </a>

        <a href="index.php?action=staff_booking_list" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-calendar-check"></i></div>
            <span>ดูรายการจอง</span>
        </a>

        <a href="index.php?action=staff_sale_list" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-chart-line"></i></div>
            <span>ดูยอดขาย</span>
        </a>
    </div>
</div>

<div class="section-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 18px; color: var(--primary-green);">รายการจองล่าสุด</h3>
        <a href="index.php?action=staff_booking_list" style="font-size: 14px; color: #666; text-decoration: none;">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>รหัสจอง</th>
                    <th>ชื่อลูกค้า</th>
                    <th>คอร์สเรียน</th>
                    <th>วันที่ทำรายการ</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_bookings)): ?>
                    <?php foreach ($recent_bookings as $b): ?>
                        <tr>
                            <td style="font-weight: 500;">#<?= $b['booking_id'] ?></td>
                            <td>
                                <span style="font-weight: 600; color: #333;"><?= htmlspecialchars($b['full_name']) ?></span>
                            </td>
                            <td style="color: var(--primary-green);"><?= htmlspecialchars($b['course_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($b['booked_at'])) ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'Pending' => ['text' => 'รอตรวจสอบ', 'class' => 'badge-pending'],
                                    'Confirmed' => ['text' => 'อนุมัติแล้ว', 'class' => 'badge-approved'],
                                    'Cancelled' => ['text' => 'ยกเลิก', 'class' => 'badge-rejected'],
                                    'Rejected' => ['text' => 'ไม่อนุมัติ', 'class' => 'badge-rejected']
                                ];
                                $s = $statusMap[$b['status']] ?? ['text' => $b['status'], 'class' => 'badge-gray'];
                                ?>
                                <span class="status-badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                            </td>
                            <td>
                                <a href="index.php?action=staff_booking_detail&id=<?= $b['booking_id'] ?>" class="link-action">
                                    <i class="fas fa-eye"></i> ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 30px; color: #999;">ไม่มีรายการจองล่าสุด</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
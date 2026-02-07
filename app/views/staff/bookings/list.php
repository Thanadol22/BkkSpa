<div class="container-fluid">
   

    <div class="section-container" style="background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 25px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 20px; color: var(--primary-green, #28a745); font-weight: bold;">
                <i class="fas fa-list-alt mr-2"></i> รายการจองทั้งหมด
            </h3>
            <span class="text-muted small">ทั้งหมด <?php echo count($bookings); ?> รายการ</span>
        </div>

        <div class="table-responsive">
            <table class="staff-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #f0f0f0;">
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">รหัสจอง</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">ชื่อลูกค้า</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">คอร์สเรียน</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">วันที่ทำรายการ</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">สถานะ</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr style="border-bottom: 1px solid #f9f9f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                                
                                <td style="padding: 15px; font-weight: 500; color: #333;">
                                    #<?= $b['booking_id'] ?>
                                </td>
                                
                                <td style="padding: 15px;">
                                    <span style="font-weight: 600; color: #333; display: block;"><?= htmlspecialchars($b['full_name']) ?></span>
                                    <small style="color: #999; font-size: 12px;"><?= $b['phone'] ?></small>
                                </td>
                                
                                <td style="padding: 15px; color: var(--primary-green, #28a745); font-weight: 500;">
                                    <?= htmlspecialchars($b['course_name']) ?>
                                </td>
                                
                                <td style="padding: 15px; color: #555;">
                                    <?= date('d/m/Y', strtotime($b['booked_at'])) ?>
                                </td>
                                
                                <td style="padding: 15px;">
                                    <?php
                                    $statusMap = [
                                        'Pending' => ['text' => 'รอตรวจสอบ', 'class' => 'badge-pending'],
                                        'Confirmed' => ['text' => 'อนุมัติแล้ว', 'class' => 'badge-approved'],
                                        'Cancelled' => ['text' => 'ยกเลิก', 'class' => 'badge-rejected'],
                                        'Rejected' => ['text' => 'ไม่อนุมัติ', 'class' => 'badge-rejected'],
                                        'RefundPending' => ['text' => 'รอคืนเงิน', 'class' => 'badge-pending'],
                                        'Refunded' => ['text' => 'คืนเงินแล้ว', 'class' => 'badge-refunded']
                                        
                                    ];
                                    $s = $statusMap[$b['status']] ?? ['text' => $b['status'], 'class' => 'badge-gray'];
                                    ?>
                                    <span class="status-badge <?= $s['class'] ?>"><?= $s['text'] ?></span>
                                </td>
                                
                                <td style="padding: 15px;">
                                    <a href="index.php?action=staff_booking_detail&id=<?= $b['booking_id'] ?>" class="link-action">
                                        <i class="fas fa-eye"></i> ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 50px; color: #999;">
                                <i class="fas fa-inbox fa-3x mb-3" style="color: #ddd;"></i><br>
                                ไม่มีรายการจองในขณะนี้
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
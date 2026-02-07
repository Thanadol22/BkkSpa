<div class="container-fluid">

    <div class="page-header-container">
        <a href="index.php?action=staff_refund_list" class="back-link">
            <i class="fas fa-arrow-left mr-2"></i> กลับไปหน้ารายการ
        </a>

        <div style="display: flex; justify-content: space-between; align-items: end;">
            <div>
                <h2 class="page-title">ประวัติการคืนเงิน</h2>
                <p class="page-subtitle">รายการที่ดำเนินการโอนคืนเสร็จสิ้นทั้งหมด</p>
            </div>
            
            
        </div>
    </div>

    <div class="custom-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="10%">Booking ID</th>
                        <th width="15%">วันที่โอนคืน</th>
                        <th width="25%">ผู้เรียน / คอร์ส</th>
                        <th width="20%">บัญชีปลายทาง</th>
                        <th width="15%">สถานะ</th>
                        <th width="15%" style="text-align: right;">หลักฐาน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 50px; color: #999;">
                                - ไม่พบประวัติการคืนเงิน -
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--primary-green, #28a745);">
                                    #<?= $row['booking_id'] ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($row['refund_date'])) ?>
                                    <br>
                                    <small style="color:#888;"><?= date('H:i', strtotime($row['refund_date'])) ?> น.</small>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                    <div style="font-size: 12px; color: #6c757d; margin-top: 2px;">
                                        <?= htmlspecialchars($row['course_name']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['refund_bank_name']) ?>
                                    <div style="font-size: 12px; color: #6c757d;">
                                        <?= htmlspecialchars($row['refund_bank_account']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-badge-success">
                                        <i class="fas fa-check-circle"></i> คืนเงินแล้ว
                                    </span>
                                </td>

                                <td style="text-align: right;">
                                    <?php if (!empty($row['refund_slip'])): ?>
                                        <a href="<?= htmlspecialchars($row['refund_slip']) ?>" target="_blank" class="btn-view-slip">
                                            <i class="fas fa-file-image"></i> ดูสลิป
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #ccc;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
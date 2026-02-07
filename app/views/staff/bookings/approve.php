<div class="course-form-container">

    <div style="margin-bottom: 30px; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <h2 style="font-size: 26px; font-weight: 700; color: #1a202c; margin: 0;">
                ตรวจสอบการชำระเงิน
            </h2>
            <div style="display:flex; align-items:center; gap:8px; margin-top:5px;">
                <span style="font-size: 14px; color: #718096;">Booking ID:</span>
                <span style="background:#edf2f7; color:#4a5568; padding:2px 8px; border-radius:4px; font-weight:600; font-size:13px;">
                    #<?php echo $bookingDetail['booking_id']; ?>
                </span>
            </div>
        </div>
        <a href="index.php?action=staff_booking_list" class="btn-back">
            <i class="fas fa-arrow-left mr-2"></i> กลับหน้ารายการ
        </a>
    </div>

    <div class="row">

        <div class="col-lg-8 mb-4">
            <div class="card-minimal">

                <div class="d-flex align-items-center justify-content-between divider-light">
                    <h5 class="m-0 font-weight-bold text-dark" style="font-size: 1.1rem;">
                        <i class="far fa-file-alt mr-2" style="color:var(--primary-green)"></i>รายละเอียดการจอง
                    </h5>

                    <?php
                    $st = $bookingDetail['status'];
                    $statusClass = 'badge-secondary';
                    $statusText = $st;
                    $statusIcon = '';

                    if ($st == 'Pending') {
                        $statusClass = 'badge-warning text-dark';
                        $statusText = 'รอตรวจสอบ';
                        $statusIcon = '<i class="fas fa-clock mr-1"></i>';
                    } elseif ($st == 'Confirmed') {
                        $statusClass = 'badge-success';
                        $statusText = 'อนุมัติแล้ว';
                        $statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
                    } elseif ($st == 'Rejected' || $st == 'Cancelled') {
                        $statusClass = 'badge-danger';
                        $statusText = ($st == 'Rejected') ? 'ไม่อนุมัติ' : 'ยกเลิก';
                        $statusIcon = '<i class="fas fa-times-circle mr-1"></i>';
                    }
                    ?>
                    <span class="badge <?php echo $statusClass; ?> px-3 py-2 rounded-pill" style="font-size: 0.9rem; font-weight:500;">
                        <?php echo $statusIcon . $statusText; ?>
                    </span>
                </div>

                <?php if (!empty($bookingDetail['reject_note'])): ?>
                    <div class="alert alert-danger border-0 rounded-lg mb-4 shadow-sm" style="background-color: #fff5f5; color: #c53030;">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle mt-1 mr-3" style="font-size:1.2rem;"></i>
                            <div>
                                <strong style="display:block; margin-bottom:4px;">เหตุผลที่ปฏิเสธ:</strong>
                                <span><?php echo nl2br(htmlspecialchars($bookingDetail['reject_note'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div style="font-size:1rem; font-weight:700; color:#2d3748; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #f7fafc;">
                            ข้อมูลผู้สมัคร
                        </div>

                        <div class="info-group">
                            <div class="info-label"><i class="far fa-user"></i> ชื่อ-นามสกุล</div>
                            <div class="info-value"><?php echo htmlspecialchars($bookingDetail['full_name']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label"><i class="fas fa-phone-alt" style="font-size:0.75rem;"></i> เบอร์โทรศัพท์</div>
                            <div class="info-value"><?php echo htmlspecialchars($bookingDetail['phone']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label"><i class="far fa-clock"></i> วันที่ทำรายการ</div>
                            <div class="info-value"><?php echo date("d/m/Y, H:i", strtotime($bookingDetail['booked_at'])); ?> น.</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div style="font-size:1.2rem; font-weight:700; color:#2d3748; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #f7fafc;">
                            หลักสูตรที่ลงทะเบียน
                        </div>
                        
                        <div class="course-card-embedded">
                            <div style="font-size:1.1rem; font-weight:700; color:#2c3e50; margin-bottom:15px; line-height:1.4;">
                                <?php echo htmlspecialchars($bookingDetail['course_name']); ?>
                            </div>

                            <div style="margin-bottom:15px; display:flex; align-items:center; color:#555;">
                                <i class="far fa-calendar-alt mr-2" style="color:var(--primary-green);"></i>
                                <span style="font-weight:500;">
                                    <?php echo date("d/m/Y", strtotime($bookingDetail['start_at'])); ?> - <?php echo date("d/m/Y", strtotime($bookingDetail['end_at'])); ?>
                                </span>
                            </div>

                            <div style="border-top:1px dashed #cbd5e0; margin-top:15px; padding-top:15px; display:flex; justify-content:space-between; align-items:flex-end;">
                                <span class="text-muted small font-weight-bold">ราคาหลักสูตร</span>
                                <div style="text-align:right; line-height:1;">
                                    <span class="course-price-highlight">
                                        <?php echo number_format($bookingDetail['price'], 0); ?>
                                    </span>
                                    <span style="font-size:0.85rem; color:#718096; font-weight:600;">THB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-minimal sticky-top" style="top: 20px; z-index: 10;">

                <h5 class="font-weight-bold mb-4 text-dark" style="font-size: 1.1rem; border-bottom: 2px solid #f0f2f5; padding-bottom: 15px;">
                    <i class="fas fa-receipt mr-2 text-warning"></i>หลักฐานการโอนเงิน
                </h5>

                <div class="text-center">
                    <?php if (!empty($bookingDetail['slips']) && is_array($bookingDetail['slips']) && count($bookingDetail['slips']) > 0): ?>

                        <div class="amount-box">
                            <span class="d-block text-muted small font-weight-bold mb-1 text-uppercase">ยอดเงินที่แจ้งโอน</span>
                            <span class="h2 font-weight-bold text-success d-block mb-0" style="font-family: 'Poppins', sans-serif;">
                                <?php echo number_format($bookingDetail['paid_amount'], 0); ?>
                            </span>
                            <span class="text-muted font-weight-bold small">THB</span>
                        </div>

                        <div class="slip-container mb-3">
                            <?php foreach ($bookingDetail['slips'] as $surl): ?>
                                <a href="<?php echo htmlspecialchars($surl); ?>" target="_blank" style="display:inline-block; margin:5px;">
                                    <img src="<?php echo htmlspecialchars($surl); ?>" class="slip-thumbnail" alt="Payment Slip">
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-4 small text-muted"><i class="fas fa-search-plus"></i> คลิกที่รูปเพื่อดูภาพขยาย</div>

                        <?php if ($bookingDetail['status'] == 'Pending'): ?>

                            <div class="action-buttons-container">
                                
                                <form action="index.php?action=verify_payment" method="POST" onsubmit="return confirm('ยืนยันอนุมัติยอดเงินนี้?');" class="action-item">
                                    <input type="hidden" name="booking_id" value="<?php echo $bookingDetail['booking_id']; ?>">
                                    <input type="hidden" name="status" value="Confirmed">

                                    <button type="submit" class="btn-verify-base btn-save">
                                        <i class="fas fa-check"></i> <span>อนุมัติ</span>
                                    </button>
                                </form>

                                <div class="action-item">
                                    <button type="button" class="btn-verify-base btn-reject" onclick="openRejectModal()">
                                        <i class="fas fa-times"></i> <span>ปฏิเสธ</span>
                                    </button>
                                </div>

                            </div>

                        <?php else: ?>
                            <div class="alert alert-light border text-center font-weight-bold m-0 rounded-lg">
                                <i class="fas fa-check-circle mr-1 text-success"></i> ดำเนินการเรียบร้อยแล้ว
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="py-5 text-center bg-light rounded border border-light">
                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted font-weight-bold mb-0">ยังไม่มีการแนบสลิป</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<div id="rejectModal" class="modal-overlay">
    <div class="modal-card">
        <form id="rejectForm" action="index.php?action=verify_payment" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo $bookingDetail['booking_id']; ?>">
            <input type="hidden" name="status" value="Rejected">

            <div class="modal-header">
                <div>
                    <h2 style="color: #e53e3e; margin:0; font-weight:700; display:flex; align-items:center;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>ปฏิเสธการจอง
                    </h2>
                    <p style="margin:5px 0 0 0; font-size:13px; color:#718096;">ระบุเหตุผลเพื่อแจ้งเตือนผู้สมัครทางอีเมล</p>
                </div>
                <button type="button" onclick="closeRejectModal()" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:#a0aec0;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group mb-3">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block; color:#4a5568;">เลือกสาเหตุหลัก</label>
                    <select id="reject_reason_select" name="reject_note" class="form-control-custom">
                        <option value="ยอดเงินไม่ครบถ้วน">ยอดเงินไม่ครบถ้วน</option>
                        <option value="หลักฐานการโอนไม่ถูกต้อง/ไม่ชัดเจน">หลักฐานการโอนไม่ถูกต้อง/ไม่ชัดเจน</option>
                        <option value="ชื่อผู้โอนไม่ตรงกับผู้สมัคร">ชื่อผู้โอนไม่ตรงกับผู้สมัคร</option>
                        <option value="other">อื่นๆ (ระบุเพิ่มเติม)</option>
                    </select>
                </div>

                <div class="form-group" id="otherReasonGroup" style="display:none;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block; color:#4a5568;">ระบุรายละเอียดเพิ่มเติม</label>
                    <textarea id="reject_note_custom" name="reject_note_custom" rows="3" 
                              class="form-control-custom"
                              style="resize:vertical;"
                              placeholder="พิมพ์สาเหตุอย่างละเอียดที่นี่..."></textarea>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-cancel" onclick="closeRejectModal()">ยกเลิก</button>
                <button type="submit" class="btn-modal-confirm">ยืนยันปฏิเสธ</button>
            </div>
        </form>
    </div>
</div>

<script>
    const rejectModal = document.getElementById('rejectModal');
    const reasonSelect = document.getElementById('reject_reason_select');
    const otherReasonGroup = document.getElementById('otherReasonGroup');
    const customNote = document.getElementById('reject_note_custom');

    function openRejectModal() {
        rejectModal.classList.add('show');
    }

    function closeRejectModal() {
        rejectModal.classList.remove('show');
        reasonSelect.value = "ยอดเงินไม่ครบถ้วน";
        otherReasonGroup.style.display = 'none';
    }

    reasonSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            otherReasonGroup.style.display = 'block';
            customNote.required = true;
            setTimeout(() => customNote.focus(), 100);
        } else {
            otherReasonGroup.style.display = 'none';
            customNote.required = false;
        }
    });

    window.onclick = function(event) {
        if (event.target == rejectModal) {
            closeRejectModal();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var active = document.activeElement;
        if (rejectModal && active && rejectModal.contains(active)) {
            active.blur();
        }
    });
</script>
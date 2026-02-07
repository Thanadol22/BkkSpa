<div class="container-fluid">

    <div class="section-container" style="background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 25px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 20px; color: var(--primary-green, #28a745); font-weight: bold;">
            <i class="fas fa-file-invoice-dollar mr-2"></i> จัดการรายการขอคืนเงิน
        </h3>

        <div style="display: flex; align-items: center;">
            
            <a href="index.php?action=staff_refund_history" class="btn-history">
                <i class="fas fa-history"></i> ประวัติการคืนเงิน
            </a>

            <span class="text-muted small">รอตรวจสอบ <?php echo count($refunds); ?> รายการ</span>
        </div>
    </div>

        <div class="table-responsive">
            <table class="staff-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #f0f0f0;">
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">Booking ID</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">ผู้เรียน / คอร์ส</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">ยอดเงินที่ชำระ</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">บัญชีปลายทาง</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">วันที่แจ้ง</th>
                        <th style="padding: 15px; text-align: left; color: #666; font-weight: 600;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($refunds)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 50px; color: #999;">
                                <i class="fas fa-check-circle fa-3x mb-3" style="color: #ddd;"></i><br>
                                ไม่มีรายการขอคืนเงินใหม่
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($refunds as $row): ?>
                            <tr style="border-bottom: 1px solid #f9f9f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">

                                <td style="padding: 15px; font-weight: 500; color: #333;">
                                    #<?= $row['booking_id'] ?>
                                </td>

                                <td style="padding: 15px;">
                                    <span style="font-weight: 600; color: #333; display: block;"><?= htmlspecialchars($row['full_name']) ?></span>
                                    <small style="color: #999; font-size: 12px;"><i class="fas fa-book"></i> <?= htmlspecialchars($row['course_name']) ?></small>
                                </td>

                                <td style="padding: 15px;">
                                    <div style="font-weight: 600; color: var(--primary-green, #28a745);">
                                        ฿<?= number_format($row['net_price'], 2) ?>
                                    </div>
                                    <?php if ($row['discount_percent'] > 0): ?>
                                        <div style="font-size: 11px; color: #dc3545; text-decoration: line-through;">
                                            ฿<?= number_format($row['original_price'], 2) ?>
                                        </div>
                                        <div style="font-size: 11px; color: #dc3545;">
                                            (ส่วนลด <?= $row['discount_percent'] ?>%)
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td style="padding: 15px;">
                                    <span style="display:block; color: var(--primary-green, #28a745); font-weight:500;">
                                        <?= htmlspecialchars($row['refund_bank_name']) ?>
                                    </span>
                                    <span style="color: #555;">
                                        <?= htmlspecialchars($row['refund_bank_account']) ?>
                                    </span>
                                </td>

                                <td style="padding: 15px; color: #555;">
                                    <?= date('d/m/Y H:i', strtotime($row['refund_request_at'] ?? $row['updated_at'])) ?>
                                </td>

                                <td style="padding: 15px;">
                                    <button onclick="openRefundModal('<?= $row['booking_id'] ?>', '<?= addslashes($row['full_name']) ?>', '<?= addslashes($row['refund_bank_name']) ?>', '<?= $row['refund_bank_account'] ?>')"
                                        class="link-action"
                                        style="background:none; border:none; cursor:pointer; color: var(--primary-green, #28a745); font-weight:600;">
                                        <i class="fas fa-check-circle"></i> อนุมัติคืนเงิน
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="staffRefundModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:30px; border-radius:15px; width:90%; max-width:450px; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h4 style="margin:0; color:#333;">บันทึกการโอนคืนเงิน</h4>
            <button onclick="closeRefundModal()" style="border:none; background:none; font-size:20px; cursor:pointer; color:#999;">&times;</button>
        </div>

        <form action="index.php?action=staff_submit_refund" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="booking_id" id="staff_booking_id">

            <div style="background:#f9f9f9; padding:15px; border-radius:10px; margin-bottom:20px;">
                <p style="margin:0 0 5px; color:#777; font-size:13px;">โอนคืนให้แก่:</p>
                <strong id="staff_user_name" style="font-size:16px; display:block; margin-bottom:10px;"></strong>

                <p style="margin:0 0 5px; color:#777; font-size:13px;">ข้อมูลบัญชี:</p>
                <div style="color: var(--primary-green, #28a745); font-weight:bold; font-size:15px;">
                    <i class="fas fa-university"></i> <span id="staff_bank_info"></span>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#555;">แนบสลิปการโอน</label>

                <div class="custom-file-group">
                    <label for="refund_slip_input" class="file-upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> เลือกรูปภาพ...
                    </label>

                    <input type="file" id="refund_slip_input" name="refund_slip" required accept="image/*" onchange="updateFileName(this)">

                    <span id="file-name-display">ยังไม่ได้เลือกไฟล์</span>
                </div>
            </div>

            <div style="text-align:right; margin-top: 30px;">
                <button type="button" onclick="closeRefundModal()" style="padding:10px 20px; background:#eee; border:none; border-radius:5px; cursor:pointer; margin-right:10px;">ยกเลิก</button>
                <button type="submit" style="padding:10px 20px; background:var(--primary-green, #28a745); color:#fff; border:none; border-radius:5px; cursor:pointer; font-weight:600;">
                    <i class="fas fa-save"></i> บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRefundModal(bid, uname, bank, acc) {
        document.getElementById('staffRefundModal').style.display = 'flex';
        document.getElementById('staff_booking_id').value = bid;
        document.getElementById('staff_user_name').innerText = uname;
        document.getElementById('staff_bank_info').innerText = bank + " - " + acc;
    }

    function closeRefundModal() {
        document.getElementById('staffRefundModal').style.display = 'none';
    }
    // ฟังก์ชันอัปเดตชื่อไฟล์เมื่อมีการเลือก
    function updateFileName(input) {
        var fileNameSpan = document.getElementById('file-name-display');
        if (input.files && input.files.length > 0) {
            // ดึงชื่อไฟล์มาแสดง
            fileNameSpan.textContent = input.files[0].name;
            fileNameSpan.style.color = "#28a745"; // เปลี่ยนสีเป็นเขียวเมื่อเลือกแล้ว
            fileNameSpan.style.fontStyle = "normal";
        } else {
            fileNameSpan.textContent = "ยังไม่ได้เลือกไฟล์";
            fileNameSpan.style.color = "#888";
        }
    }

    // (Optional) เพิ่มในฟังก์ชัน closeRefundModal เพื่อรีเซ็ตค่าตอนปิด Modal
    function closeRefundModal() {
        document.getElementById('staffRefundModal').style.display = 'none';

        // รีเซ็ตค่าไฟล์
        var input = document.getElementById('refund_slip_input');
        if (input) {
            input.value = "";
            document.getElementById('file-name-display').textContent = "ยังไม่ได้เลือกไฟล์";
            document.getElementById('file-name-display').style.color = "#888";
        }
    }
</script>
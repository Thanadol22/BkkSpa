<?php
// ส่วนคำนวณวันเดือนปีภาษาไทย
$startDate = "-";
$endDate = "-";
$capacityText = "-";
$capacity_remaining = $schedule['capacity'] ?? 0; // ดึงค่า capacity ที่เหลือจริง
$is_full = ($capacity_remaining <= 0);

if ($schedule) {
    $thMonths = [null, 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    // ฟังก์ชันแปลงวันที่
    $convertDate = function($date) use ($thMonths) {
        $d = date('j', strtotime($date));
        $m = $thMonths[date('n', strtotime($date))];
        $y = date('Y', strtotime($date)) + 543;
        return "$d $m $y";
    };

    $startDate = $convertDate($schedule['start_at']);
    $endDate = $convertDate($schedule['end_at']);
    
    // กำหนดข้อความ Capacity
    if ($is_full) {
        $capacityText = "ที่นั่งเต็มแล้ว";
    } else {
        $capacityText = "เหลือ {$capacity_remaining} ที่";
    }
} else {
    $is_full = true;
    $capacityText = "ไม่พบรอบเรียน";
}
?>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">

    <div class="booking-card">
        <h2 class="booking-header">ชำระเงิน</h2>

        <div class="booking-body">
            <div class="booking-left">
                <p class="qr-label">กรุณาชำระเงิน</p>
                <div class="qr-wrapper">
                    <img src="assets/images/courses/payment.jpg" alt="QR Code" class="qr-code-img">
                </div>
                <p class="qr-merchant-name">สถาบันวิชาชีพสปา กรุงเทพ</p>
            </div>

            <div class="booking-right">

                <div class="booking-summary-box">
                    <div class="summary-item">
                        <span class="s-label">ชื่อผู้สมัคร :</span>
                        <span class="s-value"><?= htmlspecialchars($user['full_name']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="s-label">สมัครเรียน :</span>
                        <span class="s-value"><?= htmlspecialchars($course['name']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="s-label">ระยะเวลาเรียน :</span>
                        <span class="s-value"><?= $course['duration_day'] ?> วัน</span>
                    </div>
                    <div class="summary-item">
                        <span class="s-label">วันที่เรียน :</span>
                        <span class="s-value">
                            <?= $startDate ?> - <?= $endDate ?>
                        </span>
                    </div>
                    
                    <div class="summary-item" style="font-weight: 600; color: <?= $is_full ? 'red' : '#007000' ?>;">
                        <span class="s-label">ที่นั่งคงเหลือ :</span>
                        <span class="s-value"><?= $capacityText ?></span>
                    </div>
                </div>

                <div class="booking-total-row" style="align-items: flex-start;">
                    <span style="margin-top: 5px;">ยอดชำระ</span>
                    <?php 
                    $finalPrice = $course['price'];
                    if (isset($activePromo) && $activePromo) {
                        $discount = $activePromo['discount'];
                        $finalPrice = $course['price'] * (1 - ($discount / 100));
                    ?>
                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                            <span style="font-size: 14px; text-decoration: line-through; color: #999;">฿<?= number_format($course['price'], 0) ?></span>
                            <span class="total-price" style="color: #e74c3c; font-size: 24px;">฿<?= number_format($finalPrice, 0) ?></span>
                            <div style="font-size: 12px; color: #e74c3c; font-weight: bold;">(ส่วนลด <?= intval($discount) ?>%)</div>
                        </div>
                    <?php } else { ?>
                        <span class="total-price">฿<?= number_format($course['price'], 0) ?></span>
                    <?php } ?>
                </div>

                <form action="index.php?action=process_enroll" method="POST" enctype="multipart/form-data" class="booking-upload-form">

                    <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                    <input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">
                    <input type="hidden" name="amount" value="<?= $finalPrice ?>">

                    <?php if ($is_full): ?>
                        <p style="color: red; text-align: center; padding: 15px 0;">**ไม่สามารถยืนยันการจองได้ เนื่องจากที่นั่งเต็ม**</p>
                    <?php else: ?>
                        <div class="upload-control-row">
                            <span id="file-name-display" class="file-text-left">กรุณาอัปโหลดสลิปโอนเงิน (1-3 รูป)</span>

                            <label for="slip_input" class="btn-upload-green">
                                อัปโหลด <i class="fas fa-upload"></i>
                            </label>

                            <input type="file" name="slip_files[]" id="slip_input" accept="image/*" required multiple onchange="handleFiles(this)">
                            <button type="submit" class="btn-confirm-booking">ยืนยัน</button>
                        </div>

                        <div id="slip-preview" style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;"></div>
                        <div id="upload-status" role="status" aria-live="polite" style="margin-top:10px; font-weight:600; color:#2d7a2d; display:none;"></div>
                    <?php endif; ?>
                    
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    (function addStyles(){
        const css = `
            .thumb-wrap { position:relative; width:80px; height:80px; border:1px solid #eee; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#fff; }
            .thumb-wrap img { max-width:100%; max-height:100%; }
            .thumb-overlay { position:absolute; inset:0; display:flex; align-items:flex-end; justify-content:center; pointer-events:none; }
            .thumb-badge { background:rgba(0,128,0,0.9); color:white; font-size:12px; padding:4px 6px; border-radius:0 0 6px 6px; width:100%; text-align:center; transform:translateY(100%); transition:transform .18s ease; }
            .thumb-success { transform:translateY(0); }
            #upload-status { transition: opacity .3s ease; }
            .btn-confirm-booking.disabled { opacity:0.6; cursor:not-allowed; }
        `;
        const s = document.createElement('style'); s.appendChild(document.createTextNode(css)); document.head.appendChild(s);
    })();

    function handleFiles(input) {
        const preview = document.getElementById('slip-preview');
        preview.innerHTML = '';
        const display = document.getElementById('file-name-display');
        const btn = document.querySelector('.btn-upload-green');
        const confirmBtn = document.querySelector('.btn-confirm-booking');
        const status = document.getElementById('upload-status');

        // Reset
        status.style.display = 'none';
        status.style.opacity = 1;

        if (!input.files || input.files.length === 0) {
            display.innerText = 'กรุณาอัปโหลดสลิปโอนเงิน (1-3 รูป)';
            btn.style.backgroundColor = '';
            btn.innerHTML = 'อัปโหลด <i class="fas fa-upload"></i>';
            confirmBtn.classList.add('disabled');
            confirmBtn.disabled = true;
            return;
        }

        if (input.files.length > 3) {
            alert('กรุณาอัปโหลดได้สูงสุด 3 รูปเท่านั้น');
            input.value = '';
            display.innerText = 'กรุณาอัปโหลดสลิปโอนเงิน (1-3 รูป)';
            btn.style.backgroundColor = '';
            btn.innerHTML = 'อัปโหลด <i class="fas fa-upload"></i>';
            confirmBtn.classList.add('disabled');
            confirmBtn.disabled = true;
            return;
        }

        // Don't render thumbnails — only show simple status and change button state
        display.innerText = input.files.length + ' รูปที่ถูกเลือก';
        btn.style.backgroundColor = '#28a745';
        btn.style.borderColor = '#28a745';
        btn.innerHTML = 'อัปโหลดสำเร็จ <i class="fas fa-check"></i>';

       
        // enable confirm button
        confirmBtn.classList.remove('disabled');
        confirmBtn.disabled = false;
        // fade out status after a moment
        setTimeout(()=> { status.style.opacity = 0; setTimeout(()=> status.style.display='none', 400); }, 1800);
    }
</script>
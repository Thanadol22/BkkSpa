<?php
// คำนวณวันที่เรียน (ถ้ามีรอบ)
$scheduleText = "รอการเปิดรอบ";
$capacityText = "ไม่ระบุ";
$is_full = true; // ตั้งค่าเริ่มต้นว่าเต็ม หรือไม่พบรอบเรียน
$capacity_remaining = 0; // จำนวนที่นั่งคงเหลือจริง

if ($schedule) {
    // [สำคัญ] ดึงค่า Capacity คงเหลือ
    $capacity_remaining = $schedule['capacity'] ?? 0;
    $is_full = ($capacity_remaining <= 0);

    // ฟังก์ชันแปลงวันที่เป็นภาษาไทยแบบสั้น
    function thaiDateShort($date) {
        $months = [null, 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $d = date('j', strtotime($date));
        $m = $months[date('n', strtotime($date))];
        $y = date('Y', strtotime($date)) + 543;
        return "$d $m $y";
    }

    $startDate = thaiDateShort($schedule['start_at']);
    $endDate = thaiDateShort($schedule['end_at']);
    $scheduleText = "$startDate - $endDate";
    
    // ตั้งค่า Capacity Text ตามที่เหลือ
    if ($is_full) {
        $capacityText = "ที่นั่งเต็มแล้ว";
    } else {
        $capacityText = "เหลือ $capacity_remaining ที่";
    }
} else {
    $is_full = true;
}
?>

<div class="page-banner" style="height: 200px;">
    
</div>

<div class="container" style="padding: 20px 20px;">

    
    
    <div class="course-detail-wrapper">
        <div class="detail-image">
            <img src="<?= htmlspecialchars($course['course_picture'] ?? 'assets/images/logo/banner2.png') ?>" alt="Course Image">
        </div>

        <div class="detail-info">
            
            

            <h2 class="detail-title"><?= htmlspecialchars($course['name']) ?></h2>

            <div class="detail-row">
                <span class="label">รายละเอียด :</span>
                <p class="desc-text">
                    <?= nl2br(htmlspecialchars($course['description'])) ?>
                </p>
            </div>

            <div class="meta-info">
                <div class="meta-item">
                    <i class="far fa-clock"></i> 
                    <strong>ระยะเวลา :</strong> <?= $scheduleText ?>
                </div>
                
                <div class="meta-item" style="color: <?= $is_full ? 'red' : 'green' ?>;">
                    <i class="fas fa-user-friends"></i> 
                    <strong>จำนวน :</strong> <?= $capacityText ?>
                </div>
                
                <div class="meta-item">
                    <i class="far fa-id-card"></i> 
                    <strong>ใบประกาศนียบัตร :</strong> หลักสูตร <?= htmlspecialchars($course['name']) ?> (<?= $course['duration_day'] * 10 ?> ชั่วโมง)
                </div>
            </div>

            <?php if (!empty($course['promo_discount']) && $course['promo_discount'] > 0): 
                $discount = floatval($course['promo_discount']);
                $finalPrice = $course['price'] * (1 - ($discount / 100));
            ?>
                <div class="price-row">
                    <span class="label">ราคาปกติ :</span>
                    <span class="price-val" style="text-decoration: line-through; color: #999; font-size: 1.2rem;">
                        <?= number_format($course['price'], 0) ?> บาท
                    </span>
                </div>
                <div class="price-row" style="margin-top: -10px; align-items: center;">
                    <span class="label" style="color: #e74c3c;">ราคาพิเศษ :</span>
                    <span class="price-val" style="color: #e74c3c; font-size: 2rem; font-weight: bold;">
                        <?= number_format($finalPrice, 0) ?> บาท
                        <span style="font-size: 1rem; background: #ffebee; color: #c62828; padding: 4px 10px; border-radius: 99px; vertical-align: middle; margin-left: 10px;">
                            ลด <?= intval($discount) ?>%
                        </span>
                    </span>
                </div>
            <?php else: ?>
                <div class="price-row">
                    <span class="label">ราคา :</span>
                    <span class="price-val"><?= number_format($course['price'], 0) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($is_full): ?>
                <button class="btn-apply-large" disabled style="background-color: #ccc; cursor: not-allowed; border:none;">
                    สมัคร
                </button>
            <?php else: ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button onclick="openBookingModal(<?= $course['course_id'] ?>)" class="btn-apply-large" style="border:none; cursor:pointer;">
                        สมัคร
                    </button>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn-apply-large">
                        สมัคร (เข้าสู่ระบบ)
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <hr class="divider">

    <div class="related-courses">
        <h3 class="related-title">หลักสูตรอื่นๆ</h3>
        <div class="related-grid">
            <?php foreach ($relatedCourses as $rel): ?>
                <a href="index.php?action=course_detail&id=<?= $rel['course_id'] ?>" class="related-card">
                    <img src="<?= htmlspecialchars($rel['course_picture'] ?? 'assets/images/logo/banner2.png') ?>" alt="Rel Course">
                    <div class="overlay-title"><?= htmlspecialchars($rel['name']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div id="bookingModal" class="modal-overlay" onclick="closeBookingModal(event)">
    <div class="modal-content-box">
        <button class="btn-close-modal" onclick="closeBookingModal('force')">&times;</button>
        <div id="modalBody">
            <div style="text-align:center; padding:50px; background:#fff; border-radius:20px;">
                <i class="fas fa-spinner fa-spin fa-3x" style="color:#769E48;"></i>
                <p style="margin-top:15px; color:#666;">กำลังโหลดฟอร์มชำระเงิน...</p>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันเปิด Modal และโหลดฟอร์มด้วย AJAX
function openBookingModal(courseId) {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // ห้ามเลื่อนหน้าหลัง

    // ดึงหน้า booking_form.php มาแสดง (cache-buster + ensure scripts execute)
    const url = 'index.php?action=booking_form&course_id=' + courseId + '&_=' + Date.now();
    fetch(url, { cache: 'no-store' })
        .then(response => response.text())
        .then(html => {
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = html;

            // If the fetched HTML contains <script> tags, re-insert them so browsers execute inline scripts
            const scripts = modalBody.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.body.appendChild(newScript);
                // remove the injected script element after it runs (for cleanliness)
                setTimeout(() => newScript.remove(), 2000);
            });

            // ปรับ CSS ของ Container ใน Modal (ถ้าจำเป็น)
            const loadedContainer = document.querySelector('#modalBody .container');
            if(loadedContainer) {
                loadedContainer.style.paddingTop = '0';
                loadedContainer.style.paddingBottom = '0';
            }
        })
        .catch(error => {
            document.getElementById('modalBody').innerHTML = '<p style="text-align:center; background:#fff; padding:20px;">เกิดข้อผิดพลาดในการโหลด</p>';
        });
}

// ฟังก์ชันปิด Modal
function closeBookingModal(event) {
    if (event === 'force' || event.target.id === 'bookingModal') {
        document.getElementById('bookingModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // คืนค่าการเลื่อนหน้า
        // รีเซ็ตเนื้อหา Modal
        document.getElementById('modalBody').innerHTML = '<div style="text-align:center; padding:50px; background:#fff; border-radius:20px;"><i class="fas fa-spinner fa-spin fa-3x" style="color:#769E48;"></i></div>';
    }
}
</script>
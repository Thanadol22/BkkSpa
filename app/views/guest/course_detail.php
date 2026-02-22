<?php
// คำนวณวันที่เรียน (ถ้ามีรอบ)
$scheduleText = "รอการเปิดรอบ";
$capacityText = "ไม่ระบุ";
$is_full = true; // ตั้งค่าเริ่มต้นว่าเต็ม หรือไม่พบรอบเรียน
$capacity_remaining = 0; // จำนวนที่นั่งคงเหลือจริง

if ($schedule) {
    // [สำคัญ] ดึงค่า Capacity คงเหลือ
    $capacity_remaining = $schedule['capacity'] ?? 0;
    
    // ตรวจสอบวันหมดเขต (ต้องสมัครก่อนวันเริ่มเรียน)
    $is_expired = ($schedule['start_at'] <= date('Y-m-d'));
    
    // ปุ่มจะเป็นสีเทา (is_full = true) ถ้าที่นั่งเต็ม หรือ หมดเขตแล้ว
    $is_full = ($capacity_remaining <= 0) || $is_expired;

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
    if ($capacity_remaining <= 0) {
        $capacityText = "ที่นั่งเต็มแล้ว";
    } elseif ($is_expired) {
        $capacityText = "ปิดรับสมัคร (เริ่มเรียนแล้ว)";
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
            
            <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 15px; color: #666;">
                <span style="font-weight: bold; font-size: 1.1rem; margin-right: 5px;"><?= number_format($ratingStats['rating'], 1) ?>/5</span>
                <div style="color: gold; margin-right: 10px;">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        if($i <= round($ratingStats['rating'])) {
                            echo '<i class="fas fa-star"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <a href="#reviews-section" style="color: #666; text-decoration: none;">
                    รีวิวจากผู้เรียน (<?= $ratingStats['count'] ?>) <i class="fas fa-angle-double-right"></i>
                </a>
            </div>

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
            <?php elseif (isset($existingBookingStatus) && $existingBookingStatus): ?>
                <button class="btn-apply-large" disabled style="background-color: #ccc; cursor: not-allowed; border:none; font-size: 1rem;">
                    สมัครแล้ว
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

    <!-- Review Section -->
    <div id="reviews-section" style="margin-top: 50px; padding: 0 10px;">
        <div style="display: flex; align-items: baseline; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
            <h3 style="font-size: 1.5rem; color: #333; margin: 0; margin-right: 15px;">รีวิวจากผู้เรียน (<?= $ratingStats['count'] ?>)</h3>
        </div>

        <div class="review-list">
            <?php if (empty($reviews)): ?>
                <p style="color: #999; text-align: center; padding: 20px;">ยังไม่มีรีวิวสำหรับหลักสูตรนี้</p>
            <?php else: ?>
                <?php foreach($reviews as $r): ?>
                <div class="review-item" style="display: flex; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #f9f9f9; padding-bottom: 20px;">
                    <div class="reviewer-avatar" style="flex-shrink: 0;">
                        <div style="width: 60px; height: 60px; background-color: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #b71c1c; overflow: hidden;">
                             <i class="far fa-user"></i>
                        </div>
                    </div>
                    <div class="review-content" style="flex-grow: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; margin-bottom: 5px;">
                            <h4 style="margin: 0; font-size: 18px; color: #333; margin-right: 10px; max-width: 100%;" class="text-truncate"><?= htmlspecialchars($r['full_name']) ?></h4>
                            <div class="stars" style="color: #ffc107 !important; font-size: 16px; display: flex; align-items: center; white-space: nowrap;">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $r['rating']) ? '<i class="fas fa-star mr-1"></i>' : '<i class="fas fa-star mr-1" style="color: #e0e0e0;"></i>';
                                }
                                ?>
                            </div>
                        </div>
                        <div style="font-size: 13px; color: #999; margin-bottom: 10px;">
                            <?= date('d M Y', strtotime($r['created_at'])) ?>
                        </div>
                        <p style="color: #333; line-height: 1.6; margin: 0; font-size: 15px;">
                            <?= nl2br(htmlspecialchars($r['comment'])) ?>
                        </p>
                        <?php if(!empty($r['review_image'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="<?= htmlspecialchars($r['review_image']) ?>" 
                                     style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid #eee; cursor: pointer;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if ($totalReviewPages > 1): ?>
                    <div style="text-align: center; margin-top: 30px; display: flex; justify-content: center; gap: 10px;">
                        <?php if ($currentReviewPage > 1): ?>
                            <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>&page=<?= $currentReviewPage - 1 ?>#reviews-section" class="btn-quick-green" style="background: #fff; color: #333; border: 1px solid #ddd; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500;">
                                <i class="fas fa-chevron-left"></i> ก่อนหน้า
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalReviewPages; $i++): ?>
                            <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>&page=<?= $i ?>#reviews-section" class="btn-quick-green" style="background: <?= $i == $currentReviewPage ? '#c49a7c' : '#fff' ?>; color: <?= $i == $currentReviewPage ? '#fff' : '#333' ?>; border: 1px solid <?= $i == $currentReviewPage ? '#c49a7c' : '#ddd' ?>; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500;">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentReviewPage < $totalReviewPages): ?>
                            <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>&page=<?= $currentReviewPage + 1 ?>#reviews-section" class="btn-quick-green" style="background: #fff; color: #333; border: 1px solid #ddd; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500;">
                                ถัดไป <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
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
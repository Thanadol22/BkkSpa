<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <?= $message ?? '' ?>

    <div class="profile-layout">
        <aside class="profile-sidebar">
            <div class="user-summary">
                <div class="user-meta">
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p>สมาชิกตั้งแต่ <?= date('Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <button class="nav-item active" onclick="openTab('tab-courses')" id="btn-courses">
                    <i class="fas fa-graduation-cap"></i> คอร์สเรียนของฉัน
                </button>
                <button class="nav-item" onclick="openTab('tab-reviews')" id="btn-reviews">
                    <i class="fas fa-star"></i> รีวิวคอร์สเรียน
                </button>
                <button class="nav-item" onclick="openTab('tab-profile')" id="btn-profile">
                    <i class="fas fa-user-cog"></i> ข้อมูลส่วนตัว
                </button>
                <a href="index.php?action=logout" class="nav-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </a>
            </nav>
        </aside>

        <main class="profile-content">

            <div id="tab-courses" class="tab-pane active">
                <h2 class="tab-title">คอร์สเรียนของฉัน</h2>
                <?php if (empty($myCourses)): ?>
                    <div class="empty-state">
                        <img src="assets/images/icons/empty-box.png" alt="No Data" style="width: 80px; opacity: 0.5;">
                        <p>ไม่พบรายการที่ลงเรียนไว้ในขณะนี้</p>
                        <a href="index.php?action=courses" class="btn-cta-orange btn-small">ดูหลักสูตรเปิดสอน</a>
                    </div>
                <?php else: ?>
                    <div class="my-course-list">
                        <?php foreach ($myCourses as $course): ?>
                            <div class="course-row">
                                <img src="<?= htmlspecialchars($course['course_picture'] ?? 'assets/images/logo/banner2.png') ?>" alt="Course" class="course-thumb">
                                <div class="course-info">
                                    <h4><?= htmlspecialchars($course['course_name']) ?></h4>
                                    <p style="font-size:13px; color:#777;"><i class="far fa-calendar-alt"></i> จองเมื่อ: <?= date('d/m/Y', strtotime($course['booked_at'])) ?></p>
                                    <p style="font-size:13px; color:#769E48;"><i class="far fa-clock"></i> รอบเรียน: <?= date('d/m/Y', strtotime($course['start_at'])) ?> - <?= date('d/m/Y', strtotime($course['end_at'])) ?></p>
                                </div>
                                <div class="course-status" style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                                    <?php
                                    $statusColor = 'gray';
                                    $statusText = $course['status'];
                                    if ($course['status'] == 'Pending') {
                                        $statusColor = '#FFC107';
                                        $statusText = 'รอตรวจสอบ';
                                    } elseif ($course['status'] == 'Confirmed') {
                                        $statusColor = '#28a745';
                                        $statusText = 'ยืนยันแล้ว';
                                    } elseif ($course['status'] == 'RefundPending') {
                                        $statusColor = '#17a2b8';
                                        $statusText = 'รอคืนเงิน';
                                    } elseif ($course['status'] == 'Refunded' || $course['status'] == 'Cancelled') {
                                        $statusColor = '#006400';
                                        $statusText = 'คืนเงินแล้ว';
                                    } elseif ($course['status'] == 'Rejected') {
                                        $statusColor = '#dc3545';
                                        $statusText = 'ยกเลิก';
                                    }
                                    ?>
                                    <span class="badge-status" style="background-color: <?= $statusColor ?>; color: #fff; padding: 5px 10px; border-radius: 20px;">
                                        <?= $statusText ?>
                                    </span>
                                    <?php if ($course['status'] == 'Confirmed' && !empty($course['confirmed_at'])):
                                        $diff = (new DateTime())->diff(new DateTime($course['confirmed_at']))->days;

                                        // เช็คเงื่อนไข 3 วัน
                                        if ($diff <= 3): ?>

                                            <form action="index.php?action=request_refund" method="POST" onsubmit="return confirm('ยืนยันการยกเลิกคอร์สนี้และขอเงินคืน?\n\nระบบจะดำเนินการโดยใช้บัญชีธนาคารที่คุณระบุไว้ในข้อมูลส่วนตัว');" style="margin-top: 5px;">
                                                <input type="hidden" name="booking_id" value="<?= $course['booking_id'] ?>">

                                                <button type="submit" style="background-color: #dc3545; color: #fff; padding: 5px 12px; border-radius: 20px; border: none; font-size: 14px; cursor: pointer; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                                    <i class="fas fa-undo-alt"></i> ยกเลิก/ขอเงินคืน
                                                </button>
                                            </form>

                                            <small style="font-size: 10px; color: #dc3545; margin-top: 2px;">(ภายใน 3 วัน)</small>

                                    <?php endif;
                                    endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-reviews" class="tab-pane" style="display: none;">
                <h2 class="tab-title">รีวิวคอร์สเรียน</h2>

                <?php
                // กรองเฉพาะคอร์สที่เรียนจบแล้ว (end_at < now) และ สถานะ Confirmed
                $reviewableCourses = array_filter($myCourses, function ($c) {
                    return $c['status'] == 'Confirmed' && strtotime($c['end_at']) < time();
                });

                if (empty($reviewableCourses)): ?>
                    <div class="empty-state">
                        <img src="assets/images/icons/empty-box.png" alt="No Data" style="width: 80px; opacity: 0.5;">
                        <p>คุณยังไม่มีคอร์สที่เรียนจบแล้ว</p>
                        <p style="font-size:12px; color:#777;">(สามารถรีวิวได้หลังจากจบคอร์ส)</p>
                    </div>
                <?php else: ?>
                    <div class="my-course-list">
                        <?php foreach ($reviewableCourses as $course):
                            $hasReviewed = !empty($course['review_c_id']); // ตรวจสอบว่าเคยรีวิวหรือยัง
                        ?>
                            <div class="course-row">
                                <img src="<?= htmlspecialchars($course['course_picture'] ?? 'assets/images/logo/banner2.png') ?>" alt="Course" class="course-thumb">
                                <div class="course-info">
                                    <h4><?= htmlspecialchars($course['course_name']) ?></h4>
                                    <p style="font-size:13px; color:#769E48;">
                                        <i class="fas fa-check-circle"></i> เรียนจบเมื่อ: <?= date('d/m/Y', strtotime($course['end_at'])) ?>
                                    </p>
                                    <?php if ($hasReviewed && !empty($course['comment'])): ?>
                                        <p style="font-size:13px; color:#555; font-style: italic; margin-top:5px;">"<?= htmlspecialchars($course['comment']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                                <div class="course-status" style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                                    <?php if ($hasReviewed): ?>
                                        <div style="color:#ffc700; font-size:18px;">
                                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $course['rating']) ? '★' : '☆'; ?>
                                        </div>
                                        <span class="badge-status" style="background-color: #6c757d; color: #fff; padding: 5px 15px; border-radius: 20px;">
                                            <i class="fas fa-check"></i> รีวิวแล้ว
                                        </span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary" style="border-radius: 20px; font-size:14px; padding: 8px 20px;"
                                            onclick="openReviewModal('<?= $course['booking_id'] ?>', '<?= $course['master_course_id'] ?? '' ?>', '<?= addslashes($course['course_name']) ?>')">
                                            <i class="fas fa-star"></i> เขียนรีวิว
                                        </button>


                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-profile" class="tab-pane" style="display: none;">
                <h2 class="tab-title">ข้อมูลส่วนตัว</h2>
                <form method="post" action="index.php?action=profile">
                    <h4 class="form-section-title">ข้อมูลทั่วไป</h4>
                    <div class="form-group">
                        <label class="form-label">ชื่อ - นามสกุล</label>
                        <input type="text" name="full_name" class="input-field" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" class="input-field" value="<?= htmlspecialchars($user['phone']) ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="input-field" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <h4 class="form-section-title mt-4">ข้อมูลการชำระเงิน</h4>
                    <div class="form-group">
                        <label class="form-label">เลือกธนาคาร</label>
                        <select name="bank_name" class="input-field">
                            <option value="">-- กรุณาเลือกธนาคาร --</option>
                            <?php
                            $banks = ["ธนาคารกสิกรไทย (KBANK)", "ธนาคารไทยพาณิชย์ (SCB)", "ธนาคารกรุงเทพ (BBL)", "ธนาคารกรุงไทย (KTB)", "ธนาคารกรุงศรีอยุธยา (BAY)", "ธนาคารทหารไทยธนชาต (TTB)", "ธนาคารออมสิน (GSB)", "ธนาคารเพื่อการเกษตรฯ (BAAC)"];
                            foreach ($banks as $bank) {
                                $selected = ($user['bank_name'] == $bank) ? 'selected' : '';
                                echo "<option value='$bank' $selected>$bank</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">เลขที่บัญชี</label>
                        <input type="text" name="bank_account" class="input-field" value="<?= htmlspecialchars($user['bank_account']) ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <h4 class="form-section-title mt-4">เปลี่ยนรหัสผ่าน</h4>
                    <div class="form-group"><label class="form-label">รหัสผ่านปัจจุบัน</label><input type="password" name="current_password" class="input-field"></div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">รหัสผ่านใหม่</label><input type="password" name="new_password" class="input-field"></div>
                        <div class="form-group"><label class="form-label">ยืนยันรหัสผ่านใหม่</label><input type="password" name="confirm_password" class="input-field"></div>
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn-save">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>


<div id="reviewModal" class="modal-overlay">
    <div class="modal-box">

        <button type="button" class="close-modal-btn" onclick="closeReviewModal()">
            <i class="fas fa-times"></i>
        </button>

        <h5 class="modal-title">เขียนรีวิวคอร์สเรียน</h5>

        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            คอร์ส: <strong id="review_course_name" style="color: #e59a45;"></strong>
        </p>

        <form action="index.php?action=submit_review" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="booking_id" id="review_booking_id">
            <input type="hidden" name="course_id" id="review_course_id">

            <div class="form-group mb-2">
                <label style="text-align: center;">ความพึงพอใจ</label>
                <div class="rate-container">
                    <div class="rate">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5">5</label>
                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4">4</label>
                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3">3</label>
                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2">2</label>
                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1">1</label>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label>ความคิดเห็น</label>
                <textarea name="comment" class="form-control" rows="4" placeholder="บอกเล่าประสบการณ์เรียนของคุณ..."></textarea>
            </div>

            <div class="form-group mb-3">
                <label>แนบรูปภาพ (ถ้ามี)</label>

                <div class="custom-file-upload">
                    <label for="review_image_real" class="file-upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> เลือกรูปภาพ...
                    </label>

                    <input type="file" name="review_image" id="review_image_real" accept="image/*" onchange="updateFileName(this)">

                    <span id="file-name-display">ยังไม่ได้เลือกไฟล์</span>
                </div>
            </div>

            <button type="submit" class="btn-submit-review">
                <i class="fas fa-paper-plane"></i> ส่งรีวิว
            </button>
        </form>
    </div>
</div>

<script>
    function openRefundModal(id, name, price, bank, acc) {
        document.getElementById('refundModal').style.display = 'flex';
        document.getElementById('modal_booking_id').value = id;
        document.getElementById('modal_course_name').innerText = name;
        document.getElementById('modal_bank_name').value = bank;
        document.getElementById('modal_bank_acc').value = acc;
    }

    function openReviewModal(bId, cId, cName) {
        document.getElementById('reviewModal').style.display = 'flex';
        document.getElementById('review_booking_id').value = bId;
        document.getElementById('review_course_id').value = cId;
        document.getElementById('review_course_name').innerText = cName;
    }

    function openTab(tabName) {
        document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName).style.display = 'block';
        if (tabName === 'tab-courses') document.getElementById('btn-courses').classList.add('active');
        if (tabName === 'tab-reviews') document.getElementById('btn-reviews').classList.add('active');
        if (tabName === 'tab-profile') document.getElementById('btn-profile').classList.add('active');
    }

    function openReviewModal(bId, cId, cName) {
        const modal = document.getElementById('reviewModal');
        modal.style.display = 'flex';
        // รอแป๊บนึงค่อยปรับ opacity ให้ค่อยๆ ปรากฏ (Fade In)
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);

        document.getElementById('review_booking_id').value = bId;
        document.getElementById('review_course_id').value = cId;
        document.getElementById('review_course_name').innerText = cName;
    }
    // ฟังก์ชันสำหรับอัปเดตชื่อไฟล์ที่แสดง เมื่อมีการเลือกไฟล์
    function updateFileName(input) {
        const fileNameDisplay = document.getElementById('file-name-display');
        if (input.files && input.files.length > 0) {
            // ถ้ามีไฟล์ถูกเลือก ให้แสดงชื่อไฟล์
            fileNameDisplay.innerText = input.files[0].name;
            fileNameDisplay.style.color = '#28a745'; // เปลี่ยนสีข้อความให้ดู active (สีเขียว)
            fileNameDisplay.style.fontStyle = 'normal';
        } else {
            // ถ้าไม่ได้เลือก หรือยกเลิก ให้แสดงข้อความเดิม
            fileNameDisplay.innerText = 'ยังไม่ได้เลือกไฟล์ ';
            fileNameDisplay.style.color = '#888';
            fileNameDisplay.style.fontStyle = 'italic';
        }
    }

    function closeReviewModal() {
        const modal = document.getElementById('reviewModal');
        modal.style.opacity = '0'; // ค่อยๆ จางหาย
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('review_image_real').value = '';
            updateFileName(document.getElementById('review_image_real'));
        }, 300); // รอ Animation จบแล้วซ่อน
    }
</script>
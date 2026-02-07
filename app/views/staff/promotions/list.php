<div class="section-container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="margin:0; font-size:20px; color:var(--primary-green);">
            <i class="fas fa-percent"></i> จัดการโปรโมชั่น
        </h3>
        <div>
            <a href="index.php?action=staff_promotion_product_create" class="btn-quick-green mr-2" style="background-color: #17a2b8;">
                <i class="fas fa-box"></i> เพิ่มโปรฯ สินค้า
            </a>
            <a href="index.php?action=staff_promotion_course_create" class="btn-quick-green">
                <i class="fas fa-graduation-cap"></i> เพิ่มโปรฯ หลักสูตร
            </a>
        </div>
    </div>

    <h5 class="mb-3 text-muted"><i class="fas fa-box-open"></i> โปรโมชั่นสินค้า</h5>
    <div class="table-responsive mb-5">
        <table class="staff-table">
            <thead>
                <tr>
                    <th style="width: 80px;">รูปปกโปรฯ</th>
                    <th>สินค้า</th>
                    <th style="text-align: center;">ส่วนลด</th>
                    <th>ระยะเวลา</th>
                    <th style="text-align: center;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($productPromos)): ?>
                    <?php foreach($productPromos as $p): 
                        $img = !empty($p['promotion_p_picture']) ? $p['promotion_p_picture'] : $p['original_picture'];
                        
                        // คำนวณสถานะ
                        $currentTime = time(); 
                        $startTime = strtotime($p['start_at']);
                        $endTime = strtotime($p['end_at']);
                        
                        $statusBadge = '';
                        $statusText = '';

                        if ($p['visible'] == 0) {
                            // ปิดใช้งาน
                            $statusBadge = 'background-color: #6c757d; color: white;';
                            $statusText = 'ปิดใช้งาน';
                        } elseif ($currentTime < $startTime) {
                            // ยังไม่ถึงเวลาเริ่ม
                            $statusBadge = 'background-color: #ffc107; color: #212529;';
                            $statusText = 'รอเริ่มรายการ';
                        } elseif ($currentTime > $endTime) {
                            // หมดเวลาแล้ว
                            $statusBadge = 'background-color: #dc3545; color: white;';
                            $statusText = 'หมดอายุ';
                        } else {
                            // ใช้งานปกติ
                            $statusBadge = 'background-color: #28a745; color: white;';
                            $statusText = 'ใช้งานอยู่';
                        }
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($img ?? 'assets/images/no-image.jpg') ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($p['item_name']) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-danger" style="font-size: 14px;">-<?= intval($p['discount']) ?>%</span>
                        </td>
                        <td style="font-size: 13px;">
                            <div>เริ่ม: <?= date('d/m/Y H:i', $startTime) ?></div>
                            <div class="text-muted">สิ้นสุด: <?= date('d/m/Y H:i', $endTime) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <span class="status-badge" style="<?= $statusBadge ?> padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                                <?= $statusText ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลโปรโมชั่นสินค้า</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h5 class="mb-3 text-muted"><i class="fas fa-book-reader"></i> โปรโมชั่นหลักสูตร</h5>
    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th style="width: 80px;">รูปปกโปรฯ</th>
                    <th>หลักสูตร</th>
                    <th style="text-align: center;">ส่วนลด</th>
                    <th>ระยะเวลา</th>
                    <th style="text-align: center;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($coursePromos)): ?>
                    <?php foreach($coursePromos as $c): 
                        $img = !empty($c['promotion_p_picture']) ? $c['promotion_p_picture'] : $c['original_picture'];
                        
                        // คำนวณสถานะ
                        $currentTime = time(); 
                        $startTime = strtotime($c['start_at']);
                        $endTime = strtotime($c['end_at']);
                        
                        $statusBadge = '';
                        $statusText = '';

                        if ($c['visible'] == 0) {
                            $statusBadge = 'background-color: #6c757d; color: white;';
                            $statusText = 'ปิดใช้งาน';
                        } elseif ($currentTime < $startTime) {
                            $statusBadge = 'background-color: #ffc107; color: #212529;';
                            $statusText = 'รอเริ่มรายการ';
                        } elseif ($currentTime > $endTime) {
                            $statusBadge = 'background-color: #dc3545; color: white;';
                            $statusText = 'หมดอายุ';
                        } else {
                            $statusBadge = 'background-color: #28a745; color: white;';
                            $statusText = 'ใช้งานอยู่';
                        }
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($img ?? 'assets/images/no-image.jpg') ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($c['item_name']) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-danger" style="font-size: 14px;">-<?= intval($c['discount']) ?>%</span>
                        </td>
                        <td style="font-size: 13px;">
                            <div>เริ่ม: <?= date('d/m/Y H:i', $startTime) ?></div>
                            <div class="text-muted">สิ้นสุด: <?= date('d/m/Y H:i', $endTime) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <span class="status-badge" style="<?= $statusBadge ?> padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                                <?= $statusText ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลโปรโมชั่นหลักสูตร</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
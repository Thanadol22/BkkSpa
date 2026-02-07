<div class="page-banner"></div>

<div class="container course-page-container">
    
    <?php if (!empty($data['courses_by_type'])): ?>
        
        <div class="filter-menu-container">
            <ul class="course-filter">
                <li class="filter-item active" onclick="filterCourses('all', this)">ทั้งหมด</li>
                <?php foreach (array_keys($data['courses_by_type']) as $type): ?>
                    <li class="filter-item" onclick="filterCourses('<?= htmlspecialchars($type) ?>', this)">
                        <?= htmlspecialchars($type) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="filter-border"></div>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding: 80px; color:#999;">
            <h3><i class="fas fa-exclamation-circle"></i> ไม่พบข้อมูลหลักสูตรในขณะนี้</h3>
        </div>
    <?php endif; ?>

</div> <?php if (!empty($data['courses_by_type'])): ?>
    <?php foreach ($data['courses_by_type'] as $type => $courses): ?>
        
        <div class="course-section" data-category="<?= htmlspecialchars($type) ?>">
            
            <div class="container">
                
                <div class="course-category-header">
                    <h2><?= htmlspecialchars($type) ?></h2>
                    <div class="category-divider"></div>
                </div>

                <div class="course-card-grid">
                    <?php foreach ($courses as $course): ?>
                        
                        <?php 
                            // ตรวจสอบ Capacity
                            $capacity_remaining = $course['capacity'] ?? 0;
                            $is_full = ($capacity_remaining <= 0);
                            $img = !empty($course['course_picture']) ? $course['course_picture'] : 'assets/images/logo/banner2.png';
                        ?>

                        <div class="bsa-card <?= $is_full ? 'card-full' : '' ?>">
                            <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>" class="bsa-card-img-link">
                                <div class="bsa-card-img">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($course['name']) ?>">
                                    
                                    <div class="course-badge">
                                        <i class="far fa-clock"></i> <?= $course['duration_day'] ?> วัน
                                    </div>

                                    <?php if (!empty($course['promo_discount']) && $course['promo_discount'] > 0): ?>
                                        <div class="course-badge" style="top: 15px; left: 15px; background: #e74c3c; color: white;">
                                            <i class="fas fa-tags"></i> โปรโมชั่น
                                        </div>
                                    <?php endif; ?>

                                    <div class="course-capacity-info" style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 10px; border-radius: 5px; font-size: 13px;">
                                        <?php if(isset($course['capacity'])): ?>
                                            <?php if ($is_full): ?>
                                                <i class="fas fa-ban"></i> เต็มแล้ว
                                            <?php else: ?>
                                                <i class="fas fa-user-friends"></i> เหลือ <?= $capacity_remaining ?> ที่
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle"></i> ตรวจสอบ
                                        <?php endif; ?>
                                    </div>
                                    
                                </div>
                            </a>

                            <div class="bsa-card-body">
                                <h3 class="bsa-card-title">
                                    <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </a>
                                </h3>
                                
                                <div class="bsa-card-price">
                                    <?php if (!empty($course['promo_discount']) && $course['promo_discount'] > 0): ?>
                                        <?php 
                                            // คำนวณราคาลด
                                            $discount = floatval($course['promo_discount']);
                                            $finalPrice = $course['price'] * (1 - ($discount / 100));
                                        ?>
                                        <div style="font-size: 0.85em; color: #aaa; text-decoration: line-through; margin-bottom: -5px; text-align: center;">
                                            ฿<?= number_format($course['price'], 0) ?>
                                        </div>
                                        <div style="color: #e74c3c; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                            ฿<?= number_format($finalPrice, 0) ?>
                                            <span style="font-size: 0.7em; background: #ffebee; color: #c62828; padding: 2px 6px; border-radius: 99px; font-weight: bold;">
                                                -<?= intval($discount) ?>%
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        ฿<?= number_format($course['price'], 0) ?>
                                        <span class="price-label">ราคา/ท่าน</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bsa-card-footer">
                                    <?php if ($is_full): ?>
                                        <button class="btn-apply btn-disabled" disabled style="background-color: #ccc; cursor: not-allowed;">สมัคร</button>
                                    <?php else: ?>
                                        <a href="index.php?action=course_detail&id=<?= $course['course_id'] ?>" class="btn-apply">สมัคร</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div> </div> <?php endforeach; ?>
<?php endif; ?>

<script>
function filterCourses(category, element) {
    // จัดการ Class active
    document.querySelectorAll('.filter-item').forEach(item => item.classList.remove('active'));
    if (element) {
        element.classList.add('active');
    }

    // ซ่อน/แสดง Section
    let sections = document.querySelectorAll('.course-section');
    
    sections.forEach(section => {
        if (category === 'all' || section.getAttribute('data-category') === category) {
            section.style.display = 'block'; // ใช้ block เพื่อให้เต็มจอ
        } else {
            section.style.display = 'none';
        }
    });
}

// Check URL param on load
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    if (category) {
        const btn = Array.from(document.querySelectorAll('.filter-item')).find(el => el.innerText.trim() === category);
        if (btn) {
            filterCourses(category, btn);
        }
    }
});
</script>
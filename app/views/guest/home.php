<?php
// ส่วนดึงข้อมูลรีวิว (ใส่ไว้บนสุดของไฟล์ home.php)
if (!isset($pdo)) {
    // กรณีที่ตัวแปร $pdo ยังไม่ถูกประกาศ (ปกติ index.php จะประกาศให้แล้ว)
    global $pdo;
}

// 1. ดึง 6 รีวิวล่าสุด (แก้ไขชื่อตารางเป็น review_course)
$sqlReviews = "SELECT r.*, u.full_name, c.name AS course_name 
               FROM review_course r 
               JOIN user u ON r.user_id = u.user_id 
               JOIN course c ON r.course_id = c.course_id 
               ORDER BY r.created_at DESC 
               LIMIT 6";
$stmtRev = $pdo->prepare($sqlReviews);
$stmtRev->execute();
$reviews = $stmtRev->fetchAll(PDO::FETCH_ASSOC);

// 2. คำนวณคะแนนเฉลี่ย (แก้ไขชื่อตารางเป็น review_course)
$sqlStats = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM review_course";
$stmtStats = $pdo->query($sqlStats);
$ratingStats = $stmtStats->fetch(PDO::FETCH_ASSOC);
?>

<header class="hero">
    <div class="hero-content">
        <h2 class="hero-subtitle">สถาบันวิชาชีพสปา กรุงเทพ</h2>
        <h1 class="hero-title">เปิดสอน<br><span>นวดไทย</span> และ สปาครบวงจร</h1>
        <ul class="hero-list">
            <li>สอนโดยอาจารย์ผู้เชี่ยวชาญ มากประสบการณ์</li>
            <li>เรียนจบได้รับใบประกาศนียบัตร รับรองโดยกระทรวงฯ</li>
            <li>มีงานรองรับทั้งในและต่างประเทศ</li>
        </ul>
    </div>
</header>

<?php if (!empty($homepagePromotions)): ?>
<section class="promotion-section" style="padding: 40px 0; background-color: #fff5f5;">
    <div class="container" style="text-align: center;">
        <h2 style="margin-bottom: 30px; font-size: 2rem; color: #8b5e3c;">โปรโมชั่นแนะนำ</h2>
        <div class="promotion-grid" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
            <?php foreach ($homepagePromotions as $promo): 
                // Fix for special characters/spaces in filenames
                $picPath = $promo['picture'];
                $picUrl = implode('/', array_map('rawurlencode', explode('/', $picPath)));
            ?>
                <div class="promo-card" style="flex: 0 1 350px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; transition: transform 0.3s ease;">
                    <img src="<?= $picUrl ?>" alt="Promotion" style="width: 100%; height: auto; display: block;">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="highlights-section">
    <div class="container">
        <div class="highlights-grid">
            <div class="highlight-item">
                <div class="highlight-icon">
                    <img src="assets/images/logo/Massage.png" alt="Services">
                </div>

                <p class="highlight-desc">สอนกลุ่มเล็ก นวดเป็นแน่นอนผู้สอนมีประสบการณ์เกิน 10 ปี</p>
            </div>
            <div class="highlight-item">
                <div class="highlight-icon">
                    <img src="assets/images/logo/Aroma.png" alt="Treatments">
                </div>

                <p class="highlight-desc">เรียนจบแล้วสามารถกลับมา ทบทวนซ้ำฟรี</p>
            </div>
            <div class="highlight-item">
                <div class="highlight-icon">
                    <img src="assets/images/logo/Calender.png" alt="Memberships">
                </div>

                <p class="highlight-desc">ใบประกาศนียบัตรรับรองหลักสูตร</p>
            </div>
        </div>
       
    </div>
</section>

<section class="courses-section">
    <div class="container">

        <!-- Item 1: สปาเพื่อสุขภาพ -->
        <div class="feature-item">
            <div class="feature-img-box">
                <img src="assets/images/courses/allcourse-1.png" alt="สปาเพื่อสุขภาพ">
            </div>
            <div class="feature-content">
                <div class="feature-icon"><i class="far fa-heart"></i></div>
                <h3 class="feature-title">สปาเพื่อสุขภาพ</h3>
                <p class="feature-desc">
                    เน้นการนวดแผนไทยและการยืดกล้ามเนื้อเพื่อฟื้นฟูร่างกายลดอาการปวดเมื่อยเหมาะสำหรับผู้ที่ต้องการส่งเสริมสุขภาพโดยรวม
                </p>
                <a href="index.php?action=courses&category=สปาเพื่อสุขภาพ" class="btn-cta-orange btn-small">สมัคร</a>
            </div>
        </div>

        <!-- Item 2: สปาเพื่อความงาม (สลับฝั่ง) -->
        <div class="feature-item reverse">
            <div class="feature-img-box">
                <img src="assets/images/courses/allcourse-6.png" alt="สปาเพื่อความงาม">
               
            </div>
            <div class="feature-content">
                <div class="feature-icon"><i class="far fa-gem"></i></div>
                <h3 class="feature-title">สปาเพื่อความงาม</h3>
                <p class="feature-desc">
                    มุ่งเน้นการดูแลผิวพรรณ เช่น นวดหน้า พอกหน้าและเทคนิคเพื่อความงามต่างๆเหมาะกับสายงานเสริมสวยและดูแลผิว
                </p>
                <a href="index.php?action=courses&category=สปาเพื่อความงาม" class="btn-cta-orange btn-small">สมัคร</a>
            </div>
        </div>

        <!-- Item 3: สปาขั้นสูง -->
        <div class="feature-item">
            <div class="feature-img-box">
                <img src="assets/images/courses/allcourse-9.png" alt="สปาขั้นสูง">
            </div>
            <div class="feature-content">
                <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                <h3 class="feature-title">สปาขั้นสูง</h3>
                <p class="feature-desc">
                    รวมเทคนิคพิเศษ เช่น การครอบแก้ว การใช้หินร้อนและการนวดเฉพาะทางเหมาะ
                    สำหรับผู้มีพื้นฐานและต้องการยกระดับทักษะ
                </p>
                <a href="index.php?action=courses&category=สปาขั้นสูง" class="btn-cta-orange btn-small">สมัคร</a>
            </div>
        </div>

    </div>
</section>




<section class="reviews-section">
    <div class="container" style="position: relative;">

        <div class="section-title-wrapper">
            <span class="section-title-bg">ความคิดเห็นจากผู้เรียน</span>
        </div>

        <?php if (!empty($ratingStats) && $ratingStats['total_reviews'] > 0): ?>
        <?php endif; ?>

        <button class="nav-btn nav-prev" onclick="scrollReview(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="nav-btn nav-next" onclick="scrollReview(1)"><i class="fas fa-chevron-right"></i></button>

        <div class="reviews-scroll-container" id="reviewContainer">
            <?php if (empty($reviews)): ?>
                <div class="text-center w-100 py-5 text-muted">
                    <p>ยังไม่มีความคิดเห็นในตอนนี้</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $row): ?>
                    <div class="review-card">
                        <div class="quote-icon">❞</div>

                        <div class="review-header">
                            <div class="review-avatar">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="review-info">
                                <h5><?= htmlspecialchars($row['full_name'] ?? 'ผู้ใช้งาน') ?></h5>
                                <div class="review-date"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                            </div>
                        </div>

                        <div class="review-body">
                            <?= htmlspecialchars($row['comment']) ?>
                            <div style="font-size:11px; color:#aaa; margin-top:5px;">
                                (คอร์ส: <?= htmlspecialchars($row['course_name']) ?>)
                            </div>
                        </div>

                        <?php if (!empty($row['review_image']) && file_exists($row['review_image'])): ?>
                            <a href="<?= $row['review_image'] ?>" target="_blank" style="margin-bottom:10px; display:block;">
                                <img src="<?= $row['review_image'] ?>" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
                            </a>
                        <?php endif; ?>

                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $row['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star" style="color:#ddd;"></i>'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>



    </div>
</section>

<script>
    function scrollReview(direction) {
        const container = document.getElementById('reviewContainer');
        const scrollAmount = 370; // ความกว้างการ์ด + gap
        container.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
</script>
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

<section class="highlights-section">
    <div class="container">
        <div class="highlights-grid">
            <div class="highlight-item">
                <div class="highlight-circle">
                    <img src="assets/images/logo/Massage.png" alt="Icon">
                    <p>สอนกลุ่มเล็ก นวดเป็นแน่นอนผู้สอนมีประสบการณ์<br>เกิน 10 ปี</p>
                </div>
            </div>
            <div class="highlight-item">
                <div class="highlight-circle">
                    <img src="assets/images/logo/Aroma.png" alt="Icon">
                    <p>เรียนจบแล้วสามารถกลับมา ทบทวนซ้ำฟรี</p>
                </div>
            </div>
            <div class="highlight-item">
                <div class="highlight-circle">
                    <img src="assets/images/logo/Calender.png" alt="Icon">
                    <p>ใบประกาศนียบัตรรับรองหลักสูตร</p>
                </div>
            </div>
        </div>
        <a href="#" class="btn-cta-orange">สมัครเรียน!!</a>
    </div>
</section>

<section class="courses-section">
    <div class="container">
        <h2 class="section-title">หลักสูตรที่เปิดสอน</h2>
        <div class="courses-grid">
            <div class="course-item">
                <div class="course-img"><img src="assets/images/courses/allcourse-1.png" alt="Health"></div>
                <p class="course-name">สปาเพื่อสุขภาพ</p>
            </div>
            <div class="course-item">
                <div class="course-img"><img src="assets/images/courses/allcourse-6.png" alt="Beauty"></div>
                <p class="course-name">สปาเพื่อความงาม</p>
            </div>
            <div class="course-item">
                <div class="course-img"><img src="assets/images/courses/allcourse-9.png" alt="Advanced"></div>
                <p class="course-name">สปาขั้นสูง</p>
            </div>
        </div>
    </div>
</section>

<section class="online-course-section">
    <div class="online-content">
        <h2 class="online-header">Online Course</h2>
        <h3 class="online-sub">หลักสูตรการนวดด้วยน้ำมันหอมระเหย</h3>
        <div class="online-features-grid">
            <div class="feature-item"><i class="fas fa-check-circle"></i> เรียนซ้ำได้ตลอดชีพ</div>
            <div class="feature-item"><i class="fas fa-check-circle"></i> สอนทุกขั้นตอนอย่างละเอียด</div>
            <div class="feature-item"><i class="fas fa-check-circle"></i> ภาพคมชัด ระดับ HD</div>
            <div class="feature-item"><i class="fas fa-check-circle"></i> มีใบประกาศนียบัตร</div>
        </div>
        <a href="#" class="btn-cta-orange btn-small">สมัครเรียนออนไลน์คลิก!</a>
    </div>
    <div class="online-image">
        <img src="assets/images/courses/image.png" alt="Online">
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
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }
</script>
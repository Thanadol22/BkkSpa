<?php
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Member';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Bangkok Spa Academy' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php"><img src="assets/images/logo/logo.png" alt="BSA Logo"></a>
        </div>

        <div class="menu-toggle" id="mobile-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>

        <ul class="nav-links" id="nav-links">
            <li><a href="index.php">หน้าแรก</a></li>
            <li><a href="index.php?action=courses">หลักสูตร</a></li>
            <li><a href="index.php?action=products">ผลิตภัณฑ์สปา</a></li>
            <li><a href="index.php?action=gallery">แกลเลอรี</a></li>
            <li><a href="index.php?action=about">เกี่ยวกับเรา</a></li>
            <li><a href="index.php?action=contact">ติดต่อเรา</a></li>


            <li class="mobile-only-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?action=profile" class="mobile-link">
                        <i class="fas fa-user"></i> ข้อมูลส่วนตัว
                    </a>
                    <a href="index.php?action=logout" class="mobile-link" style="color: #d9534f;">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                <?php else: ?>
                    <a href="index.php?action=login" class="mobile-link" style="color: #769E48;">เข้าสู่ระบบ</a>
                    <a href="index.php?action=register" class="mobile-link">สมัครสมาชิก</a>
                <?php endif; ?>
            </li>
        </ul>

        <div class="nav-actions desktop-only">
            <?php if ($isLoggedIn): ?>
                <div class="user-profile">
                    <a href="index.php?action=profile" class="nav-profile-link">
                        ข้อมูลส่วนตัว
                    </a>

                    <span>
                        <i class="fas fa-user-circle"></i> สวัสดี, <?= htmlspecialchars($username) ?>
                    </span>

                    <a href="index.php?action=logout" class="btn-nav-logout">ออก</a>
                </div>
            <?php else: ?>
                <a href="index.php?action=login" class="btn-nav-login">เข้าสู่ระบบ</a>
                <a href="index.php?action=register" class="btn-nav-register">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <?= $content; ?>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-col brand-col">
                <img src="assets/images/logo/logo-white.png" alt="Logo White" class="footer-logo-img">
                <h3 class="footer-org-name">สถาบันวิชาชีพสปา<br>กรุงเทพ</h3>
                <p class="footer-slogan">“ภูมิปัญญาไทย มาตรฐานสากล”</p>
            </div>
            <div class="footer-col address-col">
                <i class="fas fa-map-marker-alt address-icon"></i>
                <p class="address-text">
                    124 ซอยลาดพร้าว 64 แยก 1 <br>
                    เขตวังทองหลาง เขตวังทองหลาง <br>
                    กรุงเทพมหานคร 10310
                </p>
            </div>
            <div class="footer-col contact-col">
                <h4 class="footer-title">ติดต่อสอบถาม</h4>
                <div class="contact-item"><i class="fas fa-phone-alt"></i> <span>086-322-1922</span></div>
                <div class="contact-item"><i class="fas fa-envelope"></i> <span>nadia13th@hotmail.com</span></div>
            </div>
            <div class="footer-col social-col">
                <h4 class="footer-title">ติดตามเรา</h4>
                <div class="footer-social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-line"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const menuToggle = document.querySelector('#mobile-menu');
        const navLinks = document.querySelector('#nav-links');

        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('is-active'); // เปลี่ยนรูปปุ่มเป็นกากบาท
            navLinks.classList.toggle('active'); // แสดง/ซ่อนเมนู
        });
    </script>

    <script>
        // Start Background Mail Worker
        window.addEventListener('load', function() {
            setTimeout(() => {
                fetch('process_mail_queue.php')
                    .then(r => r.json())
                    .then(d => { if(d.processed > 0) console.log('Mail sent:', d.processed); })
                    .catch(e => console.warn('Mail worker silent fail'));
            }, 2000);
        });
    </script>
</body>

</html>
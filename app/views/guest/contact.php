<div class="page-banner" style="background-image: url('assets/images/logo/banner6.png'); margin-bottom: 0;">
</div>
<div class="contact-page-wrapper">
    <!-- Top Section: Info & Form -->
    <div class="container">
        <div class="contact-content">
            
            <!-- Left Side: Info -->
            <div class="contact-info">


            <h2 class="contact-heading">ตามหาเราได้ที่</h2>
                
                <div class="info-item">
                    <div class="icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-text">
                        <h4>ที่อยู่</h4>
                        <p>124 ซอยลาดพร้าว 64 แยก 1 แขวงวังทองหลาง<br>เขตวังทองหลาง กรุงเทพมหานคร 10310</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon-circle"><i class="fas fa-phone-alt"></i></div>
                    <div class="info-text">
                        <h4>เบอร์ติดต่อ</h4>
                        <p>086-322-1922</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon-circle"><i class="far fa-envelope"></i></div>
                    <div class="info-text">
                        <h4>อีเมล์</h4>
                        <p>nadia13th@hotmail.com</p>
                    </div>
                </div>

                <div class="follow-us">
                    <h4>ติดตามเรา</h4>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/BSABangkok/" class="social-btn fb"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.youtube.com/channel/UCYaxWe0tJ0N6WX6pMyqyAbA" class="social-btn yt"><i class="fab fa-youtube"></i></a>
                        <a href="https://line.me/ti/p/~@108toots" class="social-btn line"><i class="fab fa-line"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="guest-contact-form-card">
                <h3>ส่งข้อความ</h3>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=contact" method="POST">
                    <div class="form-group">
                        <label for="name">ชื่อ นามสกุล</label>
                        <input type="text" id="name" name="name" required placeholder="">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">อีเมล์</label>
                        <input type="email" id="email" name="email" required placeholder="">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">ข้อความ</label>
                        <textarea id="message" name="message" rows="3" required placeholder=""></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">ส่งข้อความ</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Map -->
    <div style="padding-bottom: 60px; width: 95%; margin: 0 auto;">
        <div class="map-container-card">
            <!-- Updated to correct Place ID and Coordinates-->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3874.8861962304!2d100.59363611482298!3d13.790423490321288!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e29ddd2d450949%3A0xb87b0ecec2114141!2sBangkok%20Spa%20Academy%20-%20%E0%B8%AA%E0%B8%96%E0%B8%B2%E0%B8%9A%E0%B8%B1%E0%B8%99%E0%B8%A7%E0%B8%B4%E0%B8%8A%E0%B8%B2%E0%B8%8A%E0%B8%B5%E0%B8%9E%E0%B8%AA%E0%B8%9B%E0%B8%B2%E0%B8%81%E0%B8%A3%E0%B8%B8%E0%B8%87%E0%B9%80%E0%B8%97%E0%B8%9E!5e0!3m2!1sth!2sth!4v1700000000000!5m2!1sth!2sth"
                width="100%" 
                height="320" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>

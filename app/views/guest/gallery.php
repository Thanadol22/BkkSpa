<div class="page-banner" style="background-image: url('assets/images/logo/banner9.png'); margin-bottom: 0;">
</div>

<?php
// PHP Logic to find images
// Assuming index.php runs from public/, so paths are relative to public/ or use absolute paths.
// But glob() works best with physical paths.
$baseDir = dirname(__DIR__, 3); // Go up from app/views/guest to root
$publicImageDir = $baseDir . '/public/assets/images/logo/';
$webImageDir = 'assets/images/logo/';

$images = [];
if (is_dir($publicImageDir)) {
    // Find all files matching gallery*.*
    // Using glob with specific extensions to avoid non-image files
    $files = glob($publicImageDir . 'gallery*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    if ($files) {
        foreach ($files as $f) {
            $images[] = basename($f);
        }
    }
}

// Sort naturally so gallery1, gallery2, gallery10 order correctly
natsort($images);
$images = array_values($images); // Reindex

$totalImages = count($images);
$centerIndex = floor($totalImages / 2);
?>

<div class="gallery-section-wrapper" style="background: radial-gradient(circle, #e0d5b5 0%, #b8a67e 100%); overflow: hidden; padding: 60px 0;">
    <div class="text-center mb-5">
        <h2 style="color: #5d4037; text-shadow: 1px 1px 0px rgba(255,255,255,0.5);">แกลเลอรี</h2>
        <p style="color: #5d4037;">รวมภาพบรรยากาศและกิจกรรมต่างๆ</p>
    </div>

    <div class="coverflow-scene">
        <button class="nav-arrow prev" onclick="moveGallery(-1)"><i class="fas fa-chevron-left"></i></button>
        
        <div class="coverflow-list" id="galleryList">
            <?php if ($totalImages > 0): ?>
                <?php foreach ($images as $index => $file): ?>
                    <div class="coverflow-item" onclick="selectItem(<?= $index ?>)">
                        <div class="polaroid-frame">
                            <img src="<?= $webImageDir . $file ?>" alt="<?= $file ?>">
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:white; z-index:100; font-size:1.2rem;">ยังไม่มีรูปภาพในแกลเลอรี</p>
            <?php endif; ?>
        </div>

        <button class="nav-arrow next" onclick="moveGallery(1)"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
    (function() {
        // Dynamic Init from PHP
        let currentIndex = <?= $centerIndex ?>; 
        let items = [];
        
        function initGallery() {
            items = document.querySelectorAll('.coverflow-item');
            if(items.length > 0) {
                renderGallery();
            }
        }

        function renderGallery() {
            items.forEach((item, index) => {
                // Reset styles
                item.style.zIndex = "";
                item.style.opacity = "";
                item.style.transform = "";

                const offset = index - currentIndex;
                const absOffset = Math.abs(offset);

                if (offset === 0) {
                    // Center item
                    item.style.zIndex = 100;
                    item.style.transform = `translateX(0) translateZ(50px) rotateY(0deg)`;
                    item.style.opacity = 1;
                } else {
                    // Side items
                    const direction = offset > 0 ? 1 : -1;
                    
                    // Spacing
                    const translateX = 140 * offset; 
                    
                    // Rotation
                    const rotateY = -60 * direction; 
                    
                    // Depth
                    const translateZ = -150 * absOffset;
                    
                    item.style.zIndex = 100 - absOffset;
                    item.style.transform = `translateX(${translateX}px) translateZ(${translateZ}px) rotateY(${rotateY}deg)`;
                    
                    // Fade out far items
                    if (absOffset > 2) {
                        item.style.opacity = 0;
                        item.style.pointerEvents = 'none';
                    } else {
                        item.style.opacity = 1;
                        item.style.pointerEvents = 'auto';
                    }
                }
            });
        }

        window.moveGallery = function(direction) {
            currentIndex += direction;
            const total = items.length;
            
            // Boundary checks
            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex >= total) currentIndex = total - 1;

            renderGallery();
        }

        window.selectItem = function(index) {
            currentIndex = index;
            renderGallery();
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGallery);
        } else {
            initGallery();
        }
    })();
</script>

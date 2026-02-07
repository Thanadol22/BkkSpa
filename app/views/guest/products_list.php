<?php
// 1. จัดเตรียมข้อมูล: แยกสินค้าตามประเภท
$products_by_type = [];
$type_translation = [
    // 'ค่าในฐานข้อมูล' => 'คำที่อยากให้แสดงหน้าเว็บ',
    'Body Scrub'       => 'สครับขัดผิว (Body Scrub)',
    'Body Mask'        => 'มาสก์พอกตัว (Body Mask)',
    'Body Massage Oil' => 'น้ำมันนวดตัว (Massage Oil)',
    'consumable'       => 'ผลิตภัณฑ์สิ้นเปลือง',
    'equipment'        => 'อุปกรณ์สปา'
];

if (!empty($products)) {
    foreach ($products as $p) {
        $raw_type = $p['product_type'] ?? 'other';
        $type_name = $type_translation[$raw_type] ?? ucfirst($raw_type);
        $products_by_type[$type_name][] = $p;
    }
}
?>

<div class="page-banner" style="background-image: url('assets/images/logo/banner4.png');">
</div>

<div class="container course-page-container pt-5">

    <?php if (!empty($products_by_type)): ?>

        <div class="filter-menu-container">
            <ul class="course-filter">
                <li class="filter-item active" onclick="filterProducts('all', this)">ทั้งหมด</li>
                <?php foreach (array_keys($products_by_type) as $type): ?>
                    <li class="filter-item" onclick="filterProducts('<?= htmlspecialchars($type) ?>', this)">
                        <?= htmlspecialchars($type) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="filter-border"></div>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding: 80px; color:#999;">
            <h3><i class="fas fa-box-open"></i> ไม่พบข้อมูลสินค้าในขณะนี้</h3>
        </div>
    <?php endif; ?>

</div> <?php if (!empty($products_by_type)): ?>
    <?php foreach ($products_by_type as $type => $items): ?>

        <div class="product-section" data-category="<?= htmlspecialchars($type) ?>">

            <div class="container">

                <div class="course-category-header">
                    <h2><?= htmlspecialchars($type) ?></h2>
                    <div class="category-divider"></div>
                </div>

                <div class="course-card-grid">
                    <?php foreach ($items as $p): ?>

                        <?php
                        $stock = $p['stock'] ?? 0;
                        $is_out_of_stock = ($stock <= 0);
                        $img = !empty($p['product_picture']) ? $p['product_picture'] : 'assets/images/no-image.jpg';
                        ?>

                        <div class="bsa-card <?= $is_out_of_stock ? 'card-full' : '' ?>">

                            <div class="bsa-card-img">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">


                                <div class="course-capacity-info" style="position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.6); color: white; padding: 5px 10px; border-radius: 5px; font-size: 13px;">
                                    <?php if ($is_out_of_stock): ?>
                                        <i class="fas fa-times-circle text-danger"></i> สินค้าหมด
                                    <?php else: ?>
                                        <i class="fas fa-box-open"></i> เหลือ <?= number_format($stock) ?> ชิ้น
                                    <?php endif; ?>
                                </div>

                            </div>


                            <div class="bsa-card-body">
                                <h3 class="bsa-card-title">
                                    <?= htmlspecialchars($p['name']) ?>
                                </h3>

                                <div class="text-muted small mb-3">
                                    <?= nl2br(htmlspecialchars($p['description'])) ?>
                                </div>

                                <div class="bsa-card-price">
                                    ฿<?= number_format($p['price'], 0) ?>
                                    <span class="price-label">ราคา/ชิ้น</span>
                                </div>


                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div> <?php endforeach; ?>
<?php endif; ?>

<script>
    function filterProducts(category, element) {
        // จัดการ Class active ที่ปุ่ม
        document.querySelectorAll('.filter-item').forEach(item => item.classList.remove('active'));
        element.classList.add('active');

        // ซ่อน/แสดง Section สินค้า
        let sections = document.querySelectorAll('.product-section');

        sections.forEach(section => {
            if (category === 'all' || section.getAttribute('data-category') === category) {
                section.style.display = 'block'; // แสดงผล (block เพื่อให้เต็มจอ)
            } else {
                section.style.display = 'none';
            }
        });
    }
</script>
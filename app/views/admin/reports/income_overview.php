<?php
// Mapping ชื่อปุ่ม Filter ให้เป็นไทย
$filterMap = [
    'daily' => 'รายวัน',
    'monthly' => 'รายเดือน',
    'yearly' => 'รายปี',
    'custom' => 'กำหนดเอง'
];
$currentFilterName = $filterMap[$filter] ?? 'รายเดือน';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">รายงานสรุปรายได้ </h2>
        <p style="color:#666; font-size:14px; margin-top:5px;">
            ข้อมูลแบบ <strong style="color:#16a34a;"><?= $currentFilterName ?></strong>
        </p>
    </div>

    <div class="filter-group" style="display: flex; gap: 10px; align-items: center;">
        <form action="index.php" method="GET" style="display: flex; gap: 10px; align-items: center; margin: 0;">
            <input type="hidden" name="action" value="admin_reports">
            <input type="hidden" name="filter" value="<?= $filter ?>">
            
            <?php if ($filter == 'daily'): ?>
                <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>" class="form-control" style="width: auto; padding: 5px 10px;" onchange="this.form.submit()">
            <?php elseif ($filter == 'yearly'): ?>
                <select name="year" class="form-control" style="width: auto; padding: 5px 10px;" onchange="this.form.submit()">
                    <?php 
                    $currentYear = date('Y');
                    for($y = $currentYear; $y >= $currentYear - 5; $y--): 
                    ?>
                        <option value="<?= $y ?>" <?= ($selected_year == $y) ? 'selected' : '' ?>><?= $y + 543 ?></option>
                    <?php endfor; ?>
                </select>
            <?php elseif ($filter == 'custom'): ?>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date_custom) ?>" class="form-control" style="width: auto; padding: 5px 10px;">
                <span>ถึง</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date_custom) ?>" class="form-control" style="width: auto; padding: 5px 10px;">
                <button type="submit" class="btn-filter" style="padding: 5px 15px; background: #16a34a; color: white; border: none;">ค้นหา</button>
            <?php else: // monthly ?>
                <input type="month" name="month" value="<?= htmlspecialchars($selected_month) ?>" class="form-control" style="width: auto; padding: 5px 10px;" onchange="this.form.submit()">
            <?php endif; ?>
        </form>

        <div style="border-left: 1px solid #ddd; height: 30px; margin: 0 5px;"></div>

        <a href="index.php?action=admin_reports&filter=daily" class="btn-filter <?= ($filter == 'daily') ? 'active' : '' ?>">รายวัน</a>
        <a href="index.php?action=admin_reports&filter=monthly" class="btn-filter <?= ($filter == 'monthly') ? 'active' : '' ?>">รายเดือน</a>
        <a href="index.php?action=admin_reports&filter=yearly" class="btn-filter <?= ($filter == 'yearly') ? 'active' : '' ?>">รายปี</a>
        <a href="index.php?action=admin_reports&filter=custom" class="btn-filter <?= ($filter == 'custom') ? 'active' : '' ?>">กำหนดเอง</a>
    </div>
</div>

<div class="dashboard-stats-grid" style="grid-template-columns: repeat(4, 1fr); gap:15px; margin-bottom:25px;">
    <div class="stat-card" style="padding:15px; border-left: 4px solid #f59e0b;">
        <div>
            <p style="font-size:12px; color:#666;">รายได้รวมทั้งหมด</p>
            <h3 style="font-size:22px; color:#333; margin:5px 0;"><?= number_format($grandTotal) ?></h3>
            <span style="font-size:11px; color:#f59e0b;">บาท </span>
        </div>
    </div>
    <div class="stat-card" style="padding:15px; border-left: 4px solid #3b82f6;">
        <div>
            <p style="font-size:12px; color:#666;">จากคอร์สเรียน</p>
            <h3 style="font-size:20px; color:#333; margin:5px 0;"><?= number_format($totalCourse) ?></h3>
            <span style="font-size:11px; color:#3b82f6;"><?= number_format($pctCourse, 1) ?>% ของทั้งหมด</span>
        </div>
    </div>
    <div class="stat-card" style="padding:15px; border-left: 4px solid #10b981;">
        <div>
            <p style="font-size:12px; color:#666;">จากสินค้า</p>
            <h3 style="font-size:20px; color:#333; margin:5px 0;"><?= number_format($totalProduct) ?></h3>
            <span style="font-size:11px; color:#10b981;"><?= number_format($pctProduct, 1) ?>% ของทั้งหมด</span>
        </div>
    </div>
    
</div>

<div class="report-grid-container">

    <div class="report-card">
        <h3 class="report-card-title">สินค้าและคอร์สยอดนิยม</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <div>
                <h4 style="font-size:13px; color:#16a34a; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px;">
                    <i class="fas fa-certificate"></i> คอร์สเรียนยอดฮิต
                </h4>
                <?php if (empty($topCourses)): ?>
                    <p style="color:#999; font-size:13px;">ไม่มีข้อมูลในช่วงเวลานี้</p>
                <?php else: ?>
                    <?php
                    $maxC = max(array_column($topCourses, 'total'));
                    foreach ($topCourses as $item):
                        $w = ($item['total'] / $maxC) * 100;
                    ?>
                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; color:#333; margin-bottom:4px;">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <span style="color:#16a34a;"><?= number_format($item['total']) ?> ครั้ง</span>
                            </div>
                            <div class="green-bar-track">
                                <div class="green-bar-fill" style="width: <?= $w ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <h4 style="font-size:13px; color:#16a34a; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px;">
                    <i class="fas fa-box-open"></i> สินค้าขายดี
                </h4>
                <?php if (empty($topProducts)): ?>
                    <p style="color:#999; font-size:13px;">ไม่มีข้อมูลในช่วงเวลานี้</p>
                <?php else: ?>
                    <?php
                    $maxP = max(array_column($topProducts, 'total'));
                    foreach ($topProducts as $item):
                        $w = ($item['total'] / $maxP) * 100;
                    ?>
                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:600; color:#333; margin-bottom:4px;">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <span style="color:#16a34a;"><?= number_format($item['total']) ?> ชิ้น</span>
                            </div>
                            <div class="green-bar-track">
                                <div class="green-bar-fill" style="width: <?= $w ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-card">
        <h3 class="report-card-title">แนวโน้มรายได้รวม</h3>
        <div class="v-chart-wrapper" style="height: 220px;">
            <?php
            $maxRev = 0;
            foreach ($reportData as $d) {
                if ($d['total'] > $maxRev) $maxRev = $d['total'];
            }
            if ($maxRev == 0) $maxRev = 1;

            foreach ($reportData as $key => $row):
                $h = ($row['total'] / $maxRev) * 100;
            ?>
                <div class="v-bar-col" style="flex:1;">
                    <div class="v-bar-green" style="height: <?= $h ?>%; position:relative; min-height:2px;" title="<?= number_format($row['total']) ?> บาท">
                        <?php if ($h > 20): ?>
                            <span style="position:absolute; top:-20px; left:50%; transform:translateX(-50%); font-size:10px; color:#16a34a; font-weight:bold;">
                                <?= number_format($row['total'] / 1000) ?>k
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="v-bar-label" style="font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%;">
                        <?= $row['label'] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">

        <div class="report-card" style="text-align:center;">
            <h3 class="report-card-title">สัดส่วนรายได้</h3>
            <div style="position:relative; width:180px; height:180px; margin:0 auto; border-radius:50%; background: conic-gradient(#16a34a 0% <?= $pctCourse ?>%, #dcfce7 <?= $pctCourse ?>% 100%);"></div>

            <div style="margin-top:20px; text-align:left; padding:0 20px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
                    <span><i class="fas fa-circle" style="color:#16a34a; font-size:10px;"></i> คอร์สเรียน</span>
                    <strong><?= number_format($pctCourse, 1) ?>%</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span><i class="fas fa-circle" style="color:#dcfce7; font-size:10px;"></i> สินค้า</span>
                    <strong><?= number_format($pctProduct, 1) ?>%</strong>
                </div>
            </div>
        </div>

        <div class="report-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 class="report-card-title" style="margin:0;">ตารางรายละเอียด</h3>
                <a href="index.php?action=admin_report_pdf&filter=<?= $filter ?>&date=<?= $selected_date ?>&month=<?= $selected_month ?>&year=<?= $selected_year ?>" target="_blank" class="btn-filter" style="font-size:12px; padding:5px 10px; border:1px solid #ddd;">
                    <i class="fas fa-print"></i> พิมพ์ PDF
                </a>
            </div>

            <div style="overflow-y:auto; max-height:300px;">
                <table class="admin-table" style="width:100%; font-size:13px;">
                    <thead>
                        <tr style="position:sticky; top:0; background:#f9fafb; z-index:1;">
                            <th>ช่วงเวลา</th>
                            <th style="text-align:right;">คอร์ส (บาท)</th>
                            <th style="text-align:right;">สินค้า (บาท)</th>
                            <th style="text-align:right;">รวมสุทธิ (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($reportData) as $row): // กลับด้านให้ล่าสุดอยู่บน 
                        ?>
                            <tr>
                                <td><?= $row['label'] ?></td>
                                <td style="text-align:right; color:#3b82f6;"><?= number_format($row['course']) ?></td>
                                <td style="text-align:right; color:#10b981;"><?= number_format($row['product']) ?></td>
                                <td style="text-align:right; font-weight:bold;"><?= number_format($row['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f0fdf4; font-weight:bold;">
                            <td>รวมทั้งหมด</td>
                            <td style="text-align:right;"><?= number_format($totalCourse) ?></td>
                            <td style="text-align:right;"><?= number_format($totalProduct) ?></td>
                            <td style="text-align:right; color:#16a34a;"><?= number_format($grandTotal) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="report-card">
        <h3 class="report-card-title">สัดส่วนรายได้แยกตามประเภท</h3>

        <?php if (empty($revenueByType) || $grandTotal == 0): ?>
            <p style="color:#999; text-align:center; padding: 30px;">ไม่มีข้อมูลรายได้ในช่วงเวลานี้</p>
        <?php else: ?>
            <?php
            // 1. เตรียมข้อมูลสำหรับสร้าง Conic Gradient และ Legend
            $gradientParts = [];
            $legendItems = [];
            $currentPercent = 0;
            // ชุดสีที่จะวนใช้
            $colors = ['var(--chart-color-1)', 'var(--chart-color-2)', 'var(--chart-color-3)', 'var(--chart-color-4)', 'var(--chart-color-5)', 'var(--chart-color-6)'];
            $colorIndex = 0;

            foreach ($revenueByType as $type => $amount) {
                $pct = ($amount / $grandTotal) * 100;
                $endPercent = $currentPercent + $pct;
                $colorCss = $colors[$colorIndex % count($colors)];

                // สร้างส่วนของ Gradient string: "สี เริ่ม% จบ%"
                $gradientParts[] = "$colorCss $currentPercent% $endPercent%";

                // เก็บข้อมูลสำหรับสร้าง Legend
                $legendItems[] = [
                    'label' => $type ? htmlspecialchars($type) : 'ไม่ระบุประเภท',
                    'amount' => $amount,
                    'pct' => $pct,
                    'color_class' => 'bg-c' . (($colorIndex % count($colors)) + 1)
                ];

                $currentPercent = $endPercent;
                $colorIndex++;
            }
            $gradientString = implode(', ', $gradientParts);
            ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
                <div style="display: flex; justify-content: center;">
                    <div style="position:relative; width:200px; height:200px; border-radius:50%; 
                         background: conic-gradient(<?= $gradientString ?>);
                         box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:100px; height:100px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                            <span style="font-size:12px; color:#666;">รวมสุทธิ</span>
                            <strong style="font-size:16px; color:#333;"><?= number_format($grandTotal) ?></strong>
                        </div>
                    </div>
                </div>

                <div>
                    <?php foreach ($legendItems as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:13px; border-bottom:1px solid #f3f4f6; padding-bottom:8px;">
                            <div style="display:flex; align-items:center;">
                                <span class="<?= $item['color_class'] ?>" style="width:12px; height:12px; border-radius:3px; margin-right:10px;"></span>
                                <span><?= $item['label'] ?></span>
                            </div>
                            <div style="text-align:right;">
                                <strong style="color:#333;"><?= number_format($item['amount']) ?> บ.</strong>
                                <span style="color:#666; font-size:11px; margin-left:5px;">(<?= number_format($item['pct'], 1) ?>%)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <div class="report-card">
        <h3 class="report-card-title">สร้างรายได้สูงสุดตามรายชื่อ </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

            <div>
                <h4 style="font-size:13px; color:#3b82f6; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px;">
                    <i class="fas fa-graduation-cap"></i> รายได้จากคอร์สเรียน (10 อันดับแรก)
                </h4>
                <?php if (empty($revByCourse)): ?>
                    <p style="color:#999; font-size:13px;">ไม่มีข้อมูล</p>
                <?php else: ?>
                    <?php
                    // หาค่ามากสุดเพื่อคำนวณความกว้างกราฟ
                    $maxRc = max(array_column($revByCourse, 'total'));
                    if ($maxRc == 0) $maxRc = 1;
                    foreach ($revByCourse as $item):
                        $w = ($item['total'] / $maxRc) * 100;
                    ?>
                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:500; color:#333; margin-bottom:4px;">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:70%;"><?= htmlspecialchars($item['name']) ?></span>
                                <span style="color:#3b82f6; font-weight:600;"><?= number_format($item['total']) ?> บ.</span>
                            </div>
                            <div style="background:#eff6ff; height:10px; border-radius:5px; overflow:hidden;">
                                <div style="width: <?= $w ?>%; background:#3b82f6; height:100%; border-radius:5px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <h4 style="font-size:13px; color:#10b981; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px;">
                    <i class="fas fa-pump-soap"></i> รายได้จากผลิตภัณฑ์ (10 อันดับแรก)
                </h4>
                <?php if (empty($revByProduct)): ?>
                    <p style="color:#999; font-size:13px;">ไม่มีข้อมูล</p>
                <?php else: ?>
                    <?php
                    $maxRp = max(array_column($revByProduct, 'total'));
                    if ($maxRp == 0) $maxRp = 1;
                    foreach ($revByProduct as $item):
                        $w = ($item['total'] / $maxRp) * 100;
                    ?>
                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:500; color:#333; margin-bottom:4px;">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:70%;"><?= htmlspecialchars($item['name']) ?></span>
                                <span style="color:#10b981; font-weight:600;"><?= number_format($item['total']) ?> บ.</span>
                            </div>
                            <div style="background:#ecfdf5; height:10px; border-radius:5px; overflow:hidden;">
                                <div style="width: <?= $w ?>%; background:#10b981; height:100%; border-radius:5px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
</div>
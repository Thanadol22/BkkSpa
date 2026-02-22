<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานสรุปรายได้ - Bangkok Spa Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS สำหรับ PDF/Print */
        @page {
            /* กำหนด margin เป็น 0 เพื่อซ่อน Header/Footer ของ Browser */
            margin: 0;
            size: A4;
        }
        
        @media print {
            body {
                margin-top: 15mm;
                margin-bottom: 15mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            .report-section {
                margin-top: 20px;
                padding-top: 10px; /* เพิ่ม padding เพื่อดันเนื้อหาลงมา */
                page-break-inside: auto;
            }
            /* บังคับไม่ให้ table head ถูกตัด */
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }
        }

        body {
            font-family: 'Sarabun', sans-serif;
            padding: 40px;
            color: #333;
            background: #fff;
            line-height: 1.5;
        }

        .header-pdf {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .report-section {
            margin-top: 20px;
            margin-bottom: 20px;
            /* เอา break-inside: avoid ออกเพื่อให้เนื้อหาแบ่งหน้าได้ถ้าจำเป็น */
        }
        
        /* ป้องกันหัวข้อหลุดไปอยู่ท้ายหน้าคนเดียว */
        h3 {
            break-after: avoid;
            page-break-after: avoid;
        }

        h3 {
            border-left: 5px solid #16a34a;
            padding-left: 10px;
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
            background: #f9f9f9;
            padding: 5px 10px;
        }

        h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .table-w100 {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-w100 th {
            background: #f0fdf4;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #16a34a;
            font-weight: bold;
        }

        .table-w100 td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .table-w100 th.text-right,
        .table-w100 td.text-right,
        .text-right {
            text-align: right !important;
        }

        .font-bold {
            font-weight: bold;
        }

        /* กราฟแท่งแนวนอน */
        .bar-row {
            margin-bottom: 8px;
            font-size: 12px;
        }

        .bar-label-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .bar-track {
            background: #f3f4f6;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            width: 100%;
        }

        .bar-fill {
            height: 100%;
            border-radius: 4px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* กราฟวงกลม */
        .pie-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin: 20px 0;
        }

        .pie-chart {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 1px solid #eee;
            margin: 0 auto;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .legend-list {
            font-size: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .legend-color {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 8px;
            display: inline-block;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Grid 2 Columns */
        .grid-2 {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-spacing: 20px 0;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header-pdf">
        <h1 style="margin:0; font-size:24px;">รายงานสรุปรายได้</h1>
        <p style="margin:5px 0;">Bangkok Spa Academy</p>
        <p style="margin:5px 0; font-size:14px; color:#666;">ข้อมูล ณ วันที่: <?= date('d/m/Y H:i') ?></p>
        <p style="margin:5px 0; font-size:14px; color:#16a34a; font-weight:bold;">
            รูปแบบรายงาน: <?= isset($_GET['filter']) && $_GET['filter'] == 'daily' ? 'รายวัน' : (isset($_GET['filter']) && $_GET['filter'] == 'yearly' ? 'รายปี' : 'รายเดือน') ?>
        </p>
    </div>

    <div class="report-section">
        <h3>1. สรุปยอดรวม </h3>
        <div class="pie-container">
            <div class="pie-chart" style="background: conic-gradient(#16a34a 0% <?= $pctCourse ?>%, #dcfce7 <?= $pctCourse ?>% 100%);"></div>

            <div style="flex: 1; max-width: 300px;">
                <p style="font-size:18px; font-weight:bold; margin-bottom:10px; border-bottom:1px solid #ddd; padding-bottom:5px;">
                    สุทธิ: <?= number_format($grandTotal) ?> บาท
                </p>
                <div style="margin-bottom:5px; display:flex; align-items:center;">
                    <span style="display:inline-block; width:12px; height:12px; background:#16a34a; margin-right:8px; border-radius:2px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span> 
                    คอร์สเรียน: <strong><?= number_format($pctCourse, 1) ?>%</strong> (<?= number_format($totalCourse) ?> บ.)
                </div>
                <div style="display:flex; align-items:center;">
                    <span style="display:inline-block; width:12px; height:12px; background:#dcfce7; border:1px solid #999; margin-right:8px; border-radius:2px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></span> 
                    สินค้า: <strong><?= number_format($pctProduct, 1) ?>%</strong> (<?= number_format($totalProduct) ?> บ.)
                </div>
             </div>
        </div>
    </div>

    <div class="report-section">
        <h3>2. สัดส่วนรายได้แยกตามประเภท</h3>
        <?php if (empty($revenueByType) || $grandTotal == 0): ?>
            <p style="text-align:center; color:#999;">- ไม่มีข้อมูล -</p>
        <?php else: ?>
            <?php
            // เตรียมข้อมูล Gradient
            $gradientParts = [];
            $legendItems = [];
            $currentPercent = 0;
            $colors = ['#16a34a', '#3b82f6', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899']; // Hex colors for PDF
            $colorIndex = 0;

            foreach ($revenueByType as $type => $amount) {
                $pct = ($amount / $grandTotal) * 100;
                $endPercent = $currentPercent + $pct;
                $colorCss = $colors[$colorIndex % count($colors)];

                $gradientParts[] = "$colorCss $currentPercent% $endPercent%";

                $legendItems[] = [
                    'label' => $type ? $type : 'ไม่ระบุ',
                    'amount' => $amount,
                    'pct' => $pct,
                    'color' => $colorCss
                ];

                $currentPercent = $endPercent;
                $colorIndex++;
            }
            $gradientString = implode(', ', $gradientParts);
            ?>
            <div class="pie-container">
                <div class="pie-chart" style="width: 140px; height: 140px; background: conic-gradient(<?= $gradientString ?>);"></div>
                <div class="legend-list" style="width: 60%;">
                    <table class="table-w100" style="border:none;">
                        <?php foreach ($legendItems as $item): ?>
                            <tr>
                                <td style="border:none; padding:4px;">
                                    <span class="legend-color" style="background:<?= $item['color'] ?>;"></span>
                                    <?= htmlspecialchars($item['label']) ?>
                                </td>
                                <td style="border:none; padding:4px; text-align:right;">
                                    <strong><?= number_format($item['amount']) ?></strong> (<?= number_format($item['pct'], 1) ?>%)
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="report-section">
        <h3>3. รายได้สูงสุดตามรายชื่อ</h3>
        <div class="grid-2">
            <div class="col">
                <h4 style="color:#3b82f6;">คอร์สเรียนทำเงินสูงสุด</h4>
                <?php if (empty($revByCourse)): ?>
                    <p style="color:#999; font-size:12px;">- ไม่มีข้อมูล -</p>
                <?php else: ?>
                    <?php
                    $max = max(array_column($revByCourse, 'total'));
                    if ($max == 0) $max = 1;
                    ?>
                    <?php foreach ($revByCourse as $item): $w = ($item['total'] / $max) * 100; ?>
                        <div class="bar-row">
                            <div class="bar-label-row">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <strong><?= number_format($item['total']) ?></strong>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $w ?>%; background:#3b82f6;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col">
                <h4 style="color:#10b981;">สินค้าทำเงินสูงสุด</h4>
                <?php if (empty($revByProduct)): ?>
                    <p style="color:#999; font-size:12px;">- ไม่มีข้อมูล -</p>
                <?php else: ?>
                    <?php
                    $max = max(array_column($revByProduct, 'total'));
                    if ($max == 0) $max = 1;
                    ?>
                    <?php foreach ($revByProduct as $item): $w = ($item['total'] / $max) * 100; ?>
                        <div class="bar-row">
                            <div class="bar-label-row">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <strong><?= number_format($item['total']) ?></strong>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $w ?>%; background:#10b981;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-section">
        <h3>4. ตารางรายละเอียด</h3>
        <table class="table-w100">
            <thead>
                <tr>
                    <th>ช่วงเวลา</th>
                    <th class="text-right">คอร์ส (บาท)</th>
                    <th class="text-right">สินค้า (บาท)</th>
                    <th class="text-right">รวมสุทธิ (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reportData)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">ไม่พบข้อมูล</td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_reverse($reportData) as $row): ?>
                        <tr>
                            <td><?= $row['label'] ?></td>
                            <td class="text-right"><?= number_format($row['course']) ?></td>
                            <td class="text-right"><?= number_format($row['product']) ?></td>
                            <td class="text-right font-bold"><?= number_format($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;">
                    <td class="font-bold">รวมทั้งหมด</td>
                    <td class="text-right font-bold"><?= number_format($totalCourse) ?></td>
                    <td class="text-right font-bold"><?= number_format($totalProduct) ?></td>
                    <td class="text-right font-bold" style="color:#16a34a; font-size:14px;">
                        <?= number_format($grandTotal) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>

</html>
<?php
// ฟังก์ชันแปลงวันที่เป็นภาษาไทย
function DateThai($strDate) {
    if(!$strDate) return "-";
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strDay $strMonthThai $strYear";
}
function DateThaiTime($strDate) {
    if(!$strDate) return "-";
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strDay $strMonthThai $strYear $strHour:$strMinute น.";
}
function DayThai($strDate) {
    if(!$strDate) return "-";
    $days = ['Sunday'=>'อา.','Monday'=>'จ.','Tuesday'=>'อ.','Wednesday'=>'พ.','Thursday'=>'พฤ.','Friday'=>'ศ.','Saturday'=>'ส.'];
    return $days[date('l', strtotime($strDate))];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายงานประวัติการเข้าเรียน - Bangkok Spa Academy</title>
    <!-- Google Fonts: Kanit & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/staff.css?v=<?php echo time(); ?>">
</head>

<body class="print-layout">

    <button onclick="window.print()" class="print-btn"><i class="fas fa-print"></i> พิมพ์รายงาน / PDF</button>

    <div class="header">
        <div class="logo">Bangkok Spa Academy</div>
        <div class="report-title">รายงานประวัติการเข้าเรียน </div>
        <div>พิมพ์เมื่อ: <?php echo DateThaiTime(date("Y-m-d H:i:s")); ?></div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">หลักสูตร:</td>
            <td><strong><?php echo htmlspecialchars($reportData['course']['course_name']); ?></strong></td>
            <td class="info-label">วันที่เริ่ม:</td>
            <td><?php echo DateThai($reportData['course']['start_at']); ?></td>
        </tr>
        <tr>
            <td class="info-label">รหัสรอบเรียน:</td>
            <td>Schedule #<?php echo $schedule_id; ?></td>
            <td class="info-label">วันที่สิ้นสุด:</td>
            <td><?php echo DateThai($reportData['course']['end_at']); ?></td>
        </tr>
    </table>

    <table class="attendance-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%" class="text-left">ชื่อ-นามสกุล</th>
                <?php foreach ($reportData['dates'] as $date): ?>
                    <th>
                        <?php 
                            $d = strtotime($date);
                            $day = date("j", $d);
                            $month = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."][date("n", $d)];
                            echo "$day $month"; 
                        ?><br>
                        <small style="font-weight:normal;"><?php echo DayThai($date); ?></small>
                    </th>
                <?php endforeach; ?>
                <th width="10%">สรุป (วัน)</th>
                <th width="10%">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reportData['students']) > 0): ?>
                <?php $i = 1;
                foreach ($reportData['students'] as $stu): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td class="text-left"><?php echo htmlspecialchars($stu['full_name']); ?></td>

                        <?php
                        $presentCount = 0;
                        $totalDays = count($reportData['dates']);
                        ?>

                        <?php foreach ($reportData['dates'] as $date): ?>
                            <?php
                            $key = $stu['user_id'] . '_' . $date;
                            $status = $reportData['attendanceMap'][$key] ?? null;
                            ?>
                            <td>
                                <?php if ($status == 1): $presentCount++; ?>
                                    <span class="status-present"><i class="fas fa-check"></i></span>
                                <?php elseif ($status === '0'): ?>
                                    <span class="status-absent"><i class="fas fa-times"></i></span>
                                <?php else: ?>
                                    <span class="status-none">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <strong><?php echo $presentCount; ?></strong> / <?php echo $totalDays; ?>
                        </td>
                        <td>
                            <?php
                            // เกณฑ์ใหม่: ต้องเข้าเรียนอย่างน้อย 1 วันถึงจะผ่าน
                            if ($presentCount >= 1) {
                                echo '<span class="status-present">ผ่าน</span>';
                            } else {
                                echo '<span class="status-absent">ไม่ผ่าน</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo count($reportData['dates']) + 4; ?>" style="padding:30px; text-align:center; color:#999;">
                        ไม่มีรายชื่อนักเรียนในรอบเรียนนี้
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <p>ลงชื่อเจ้าหน้าที่: ..............................................................</p>
        <p>(..............................................................)</p>
    </div>

</body>

</html>
<div class="dashboard-stats-grid">
    <div class="stat-card card-border-primary">
        <div class="stat-content">
            <h3 class="stat-title">พนักงานทั้งหมด</h3>
            <div class="stat-value text-primary">
                <?= number_format($staff_count ?? 0) ?>
            </div>
            <p class="stat-desc">บุคลากรในระบบ</p>
        </div>
        <div class="stat-icon-right icon-bg-primary">
            <i class="fas fa-user-tie"></i>
        </div>
    </div>

    <div class="stat-card card-border-purple">
        <div class="stat-content">
            <h3 class="stat-title">สมาชิกทั้งหมด</h3>
            <div class="stat-value text-purple">
                <?= number_format($member_count ?? 0) ?>
            </div>
            <p class="stat-desc">ลูกค้าที่ลงทะเบียน</p>
        </div>
        <div class="stat-icon-right icon-bg-purple">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <div class="stat-card card-border-success">
        <div class="stat-content">
            <h3 class="stat-title">ยอดขายวันนี้</h3>
            <div class="stat-value text-success">
                <?= number_format($daily_sales ?? 0) ?>
            </div>
            <p class="stat-desc">บาท (THB)</p>
        </div>
        <div class="stat-icon-right icon-bg-success">
            <i class="fas fa-coins"></i>
        </div>
    </div>
</div>

<div class="section-container" style="padding: 25px; margin-bottom: 30px;">
    <h3 style="margin-top: 0; font-size: 18px; color: var(--primary-color); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
        <i class="fas fa-bolt"></i> เมนูด่วน (Quick Actions)
    </h3>

    <div class="action-buttons-grid">
        <a href="index.php?action=admin_manage_staff" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-user-tie"></i></div>
            <span>จัดการพนักงาน</span>
        </a>
        <a href="index.php?action=admin_reports" class="btn-action-card">
            <div class="action-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <span>ดูรายงานสรุป</span>
        </a>
       
    </div>
</div>

<div class="section-container" style="padding: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="margin: 0; font-size: 18px; color: var(--primary-color);">ภาพรวมรายได้ (6 เดือนล่าสุด)</h3>
            <p style="font-size:13px; color:#666; margin:5px 0 0 0;">ยอดรวมจากการขายสินค้าและคอร์สเรียน</p>
        </div>
        <a href="index.php?action=admin_reports" style="font-size: 14px; color: #16a34a; text-decoration: none; border:1px solid #16a34a; padding:5px 15px; border-radius:20px;">
            ดูรายงานละเอียด <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="v-chart-wrapper" style="height: 200px; padding-top: 20px;">
        <?php 
            // หาค่าสูงสุดเพื่อคำนวณความสูงกราฟ
            $maxVal = 0;
            foreach ($chartData as $d) {
                if ($d['total'] > $maxVal) $maxVal = $d['total'];
            }
            if ($maxVal == 0) $maxVal = 1; // ป้องกันการหารด้วยศูนย์

            foreach ($chartData as $d): 
                $h = ($d['total'] / $maxVal) * 100;
        ?>
        <div class="v-bar-col" style="flex:1;">
            <div class="v-bar-green" style="height: <?= $h ?>%; position:relative; min-height:2px;" title="<?= number_format($d['total']) ?> บาท">
                <?php if($h > 15): ?>
                <span style="position:absolute; top:-20px; left:50%; transform:translateX(-50%); font-size:11px; color:#16a34a; font-weight:bold;">
                    <?= number_format($d['total']/1000) ?>k
                </span>
                <?php endif; ?>
            </div>
            <div class="v-bar-label" style="font-size:12px; color:#666; margin-top:10px;">
                <?= $d['label'] ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
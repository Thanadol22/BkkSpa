<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผู้ดูแลระบบ - Bangkok Spa Academy</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/admin.css">

    <!-- Flatpickr สำหรับปฏิทินภาษาไทย -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
</head>
<body>
    <div class="app-wrapper">
        <div class="admin-mobile-header">
            <h3>BSA Admin</h3>
            <i class="fas fa-bars admin-menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')" style="cursor: pointer; padding: 10px;"></i>
        </div>
        
        <aside class="sidebar admin-sidebar-container" onclick="/* ปิดเมนูเมื่อคลิกพื้นหลัง */ if(window.innerWidth <= 992 && !event.target.closest('.sidebar-content')) this.classList.remove('active')">
            <div class="sidebar-content">
            <div class="sidebar-header">
                <h3>Bangkok Spa Academy</h3>
                <span class="role-badge">ระบบผู้ดูแลระบบ</span>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-label">ภาพรวม</span>
                    <a href="index.php?action=admin_dashboard" class="nav-item <?= ($_GET['action'] == 'admin_dashboard') ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-label">การจัดการ</span>
                    <a href="index.php?action=admin_manage_staff" class="nav-item <?= (strpos($_GET['action'], 'admin_staff') !== false || $_GET['action'] == 'admin_manage_staff') ? 'active' : '' ?>">
                        <i class="fas fa-user-tie"></i>
                        <span>พนักงาน</span>
                    </a>
                    <a href="index.php?action=admin_manage_member" class="nav-item <?= (strpos($_GET['action'], 'admin_member') !== false || $_GET['action'] == 'admin_manage_member') ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>สมาชิก</span>
                    </a>
                    </div>

                <div class="nav-section">
                    <span class="nav-label">รายงาน</span>
                    <a href="index.php?action=admin_reports" class="nav-item <?= ($_GET['action'] == 'admin_reports') ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>รายงานสรุป</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="index.php?action=logout" class="nav-item logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </div>
        </aside>

        <main class="main-content">
           

            <div class="content-body">
                <?php 
                    if (isset($content_view) && file_exists($content_view)) {
                        include $content_view;
                    } else {
                        echo "<div class='error-box'>ไม่พบไฟล์เนื้อหา (View Not Found)</div>";
                    }
                ?>
            </div>
        </main>
    </div>
</body>

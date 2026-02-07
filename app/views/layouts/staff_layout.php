<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบหลังบ้าน - Bangkok Spa Academy</title>

    <link rel="stylesheet" href="assets/css/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php
    // รับค่า action ปัจจุบัน ถ้าไม่มีให้เป็นค่าว่าง (เพื่อป้องกัน Error)
    $act = $_GET['action'] ?? '';
    ?>

    <div class="staff-wrapper">
        <aside class="staff-sidebar">
            <div class="sidebar-header">
                <h3>Bangkok Spa Academy</h3>
                <span class="role-badge"><?= htmlspecialchars($_SESSION['full_name'] ?? 'พนักงาน') ?></span>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="index.php?action=staff_dashboard" class="<?= ($act == 'staff_dashboard') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> หน้าหลัก
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_members" class="<?= ($act == 'staff_members') ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> จัดการสมาชิก
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_courses" class="<?= ($act == 'staff_courses') ? 'active' : '' ?>">
                        <i class="fas fa-book-open"></i> จัดการหลักสูตร
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_product_list" class="<?= ($act == 'staff_product_list') ? 'active' : '' ?>">
                        <i class="fas fa-box"></i> จัดการสินค้า
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_booking_list" class="<?= ($act == 'staff_booking_list' || $act == 'staff_booking_detail') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i> รายการจอง
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_refund_list" class="<?= ($act == 'staff_refund_list' || $act == 'staff_refund_history') ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar"></i> จัดการขอคืนเงิน
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_attendance_checkin" class="<?= ($act == 'staff_attendance_checkin') ? 'active' : '' ?>">
                        <i class="fas fa-user-check"></i> เช็คชื่อเข้าเรียน
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_sale_list" class="<?= ($act == 'staff_sale_list') ? 'active' : '' ?>">
                        <i class="fas fa-dollar-sign"></i> ยอดขายรายวัน
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_promotion_list" class="<?= ($act == 'staff_promotion_list') ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn"></i> โปรโมชั่น
                    </a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <ul class="sidebar-menu">
                    <li>
                        <a href="index.php?action=staff_profile" class="<?= ($act == 'staff_profile') ? 'active' : '' ?>">
                            <i class="fas fa-user-cog"></i> ข้อมูลส่วนตัว
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=logout" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="staff-content">
            <?php include $content_view; ?>
        </main>
    </div>

  
</body>

</html>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบหลังบ้าน - Bangkok Spa Academy</title>

    <link rel="stylesheet" href="assets/css/staff.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* CSS Badge เพิ่มเติม (Inline เพื่อความชัวร์เรื่อง Cache) */
        .sidebar-notification-badge {
            background-color: #ff4d4d; /* สีแดงสว่าง */
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            padding: 2px 6px;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.4);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(255, 77, 77, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 77, 77, 0);
            }
        }
    </style>
</head>

<body>
    <?php
    // รับค่า action ปัจจุบัน ถ้าไม่มีให้เป็นค่าว่าง (เพื่อป้องกัน Error)
    $act = $_GET['action'] ?? '';

    // [ADD] Query Pending Counts for Badges
    $badge_bookings = 0;
    $badge_refunds = 0;
    if (isset($pdo)) {
        try {
             $badge_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE status = 'Pending'")->fetchColumn();
             $badge_refunds = $pdo->query("SELECT COUNT(*) FROM booking WHERE status = 'RefundPending'")->fetchColumn();
        } catch (Exception $e) {}
    }
    ?>

    <div class="staff-wrapper">
        <div class="staff-mobile-header">
            <h3>BSA Staff</h3>
            <i class="fas fa-bars staff-menu-toggle" onclick="document.querySelector('.staff-sidebar').classList.toggle('active')" style="cursor: pointer; padding: 10px;"></i>
        </div>
        <aside class="staff-sidebar" onclick="if(window.innerWidth <= 992 && !event.target.closest('.staff-sidebar-content')) this.classList.remove('active')">
            <div class="staff-sidebar-content">
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
                    <a href="index.php?action=staff_pos" class="<?= ($act == 'staff_pos') ? 'active' : '' ?>">
                        <i class="fas fa-cash-register"></i> ขายหน้าร้าน 
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_booking_list" class="<?= ($act == 'staff_booking_list' || $act == 'staff_booking_detail') ? 'active' : '' ?>" style="display: flex; align-items: center;">
                        <i class="fas fa-calendar-check"></i> <span style="flex:1;">รายการจอง</span>
                        <?php if($badge_bookings > 0): ?>
                            <span class="sidebar-notification-badge"><?= $badge_bookings ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=staff_refund_list" class="<?= ($act == 'staff_refund_list' || $act == 'staff_refund_history') ? 'active' : '' ?>" style="display: flex; align-items: center;">
                        <i class="fas fa-file-invoice-dollar"></i> <span style="flex:1;">จัดการขอคืนเงิน</span>
                        <?php if($badge_refunds > 0): ?>
                            <span class="sidebar-notification-badge"><?= $badge_refunds ?></span>
                        <?php endif; ?>
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
            </div> <!-- End staff-sidebar-content -->
        </aside>

        <main class="staff-content">
            <?php include $content_view; ?>
        </main>
    </div>

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

        // [New] Global Session Alert (SweetAlert2)
        <?php if (!empty($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '<?= addslashes($_SESSION['success']) ?>',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'ตกลง'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'ขออภัย',
                text: '<?= addslashes($_SESSION['error']) ?>',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'ตกลง'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
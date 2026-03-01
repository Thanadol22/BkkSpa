<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตั้งค่า Timezone เป็นไทย
date_default_timezone_set('Asia/Bangkok');

// ---------------------------------------------------
// 1. ตั้งค่า Path และเชื่อมต่อฐานข้อมูล
// ---------------------------------------------------
define('BASE_PATH', dirname(__DIR__));

// Load .env keys
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\''); // Remove quotes
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
require APP_PATH . '/config/database.php';

// [เพิ่มโค้ดนี้ในส่วนต้นของ index.php]

try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage());
}

// รับค่า action จาก URL (ค่าเริ่มต้นคือ 'home')
$action = $_GET['action'] ?? $_GET['url'] ?? 'home';
// [สำคัญ] เรียกใช้ Model และสร้าง Instance

require_once APP_PATH . '/models/Course.php';
require_once APP_PATH . '/models/Booking.php';

$courseModel = new Course($pdo);
$bookingModel = new Booking($pdo);


// ===================================================
// PART 2: AUTHENTICATION & MODEL INITIALIZATION
// ===================================================

// --- A) LOGOUT (ออกจากระบบ) ---

if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// =========================================================
// ประมวลผลการเข้าสู่ระบบ (doLogin)
// =========================================================

if ($action === 'doLogin') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            // 1. ดึงข้อมูล User (ยังไม่เช็ค is_active เพื่อให้แยก Error ได้)
            $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :u LIMIT 1");
            $stmt->execute(['u' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. ตรวจสอบรหัสผ่าน (รองรับทั้ง Hash และ Plain Text เพื่อ Migration)
            $isPasswordCorrect = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $isPasswordCorrect = true;
                } elseif ($user['password'] === $password) {
                    // ถ้ายังเป็น Plain Text ให้ผ่าน แล้ว Hash เก็บลง DB ทันที
                    $isPasswordCorrect = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
                    $upd->execute([$newHash, $user['user_id']]);
                }
            }

            if ($isPasswordCorrect) {

                // 3. ตรวจสอบสถานะ Active (ถ้าเป็น 0 ห้ามเข้า)
                if ($user['is_active'] == 0) {
                    $_SESSION['error'] = 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
                    header('Location: index.php?action=login');
                    exit;
                }

                // 4. สร้าง Session ID ใหม่เพื่อความปลอดภัย
                session_regenerate_id(true);

                // 5. บันทึกค่าลง Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['full_name'] = $user['full_name'];

                // 6. Redirect ตาม Role
                switch ($user['role_id']) {
                    case 1: // Admin
                        header('Location: index.php?action=admin_dashboard');
                        break;
                    case 2: // Staff
                        header('Location: index.php?action=staff_dashboard');
                        break;
                    case 3: // Member
                    default:
                        header('Location: index.php?action=home');
                        break;
                }
                exit;
            } else {
                // กรณี Username ไม่เจอ หรือ Password ไม่ตรง
                $_SESSION['error'] = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง';
                header('Location: index.php?action=login');
                exit;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อระบบ';
            header('Location: index.php?action=login');
            exit;
        }
    } else {
        header('Location: index.php?action=login');
        exit;
    }
}



// --- C) DO REGISTER (บันทึกข้อมูลสมัครสมาชิก) ---

if ($action === 'doRegister' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $bank_name = trim($_POST['bank_name']);
    $bank_acc  = trim($_POST['bank_account']);
    $email     = trim($_POST['email']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['error'] = "รหัสผ่านที่ยืนยันไม่ตรงกัน";
        header('Location: index.php?action=register');
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว";
        header('Location: index.php?action=register');
        exit;
    }

    $stmtEmail = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetch()) {
        $_SESSION['error'] = "อีเมลนี้ถูกใช้งานแล้ว";
        header('Location: index.php?action=register');
        exit;
    }

    $sql = "INSERT INTO user (role_id, full_name, phone, bank_name, bank_account, email, username, password)
            VALUES (3, ?, ?, ?, ?, ?, ?, ?)";

    try {
        // Hash Password ก่อนบันทึก
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare($sql)->execute([$full_name, $phone, $bank_name, $bank_acc, $email, $username, $hashed_password]);
        
        $_SESSION['success'] = "สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ";
        header('Location: index.php?action=login');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header('Location: index.php?action=register');
        exit;
    }
}



// --- D) DO RESET PASSWORD (เปลี่ยนรหัสผ่าน) ---

if ($action === 'doResetPassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password'] ?? '';
    $stmt = $pdo->prepare("SELECT user_id, password FROM user WHERE username = :u LIMIT 1");
    $stmt->execute(['u' => $username]);
    $user = $stmt->fetch();

    $isCurrentPassCorrect = false;
    if ($user) {
        if (password_verify($current_pass, $user['password'])) {
            $isCurrentPassCorrect = true;
        } elseif ($user['password'] === $current_pass) {
            $isCurrentPassCorrect = true;
        }
    }

    if (!$isCurrentPassCorrect) {
        $_SESSION['error'] = "ข้อมูลไม่ถูกต้อง (ชื่อผู้ใช้งานหรือรหัสผ่านเดิมผิด)";
        header('Location: index.php?action=resetPassword');
        exit;
    }

    // Hash New Password
    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?")->execute([$new_hash, $user['user_id']]);
    $_SESSION['success'] = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว กรุณาเข้าสู่ระบบใหม่";
    header('Location: index.php?action=login');

    exit;
}

// ===================================================
// PART 3: ROUTING & VIEW RENDERING (แสดงผลหน้าเว็บ)
// ===================================================

// กรณีหน้า Auth (Login/Register/Reset) -> ไม่ใช้ Main Layout
if (in_array($action, ['login', 'register', 'resetPassword'])) {
    if ($action == 'login') include VIEW_PATH . '/guest/login.php';
    if ($action == 'register') include VIEW_PATH . '/guest/register.php';
    if ($action == 'resetPassword') include VIEW_PATH . '/guest/reset_password.php';
    exit;
}

// กรณีหน้าเนื้อหาหลัก (Home/Courses/etc.) -> ใช้ Main Layout

// 1. เตรียมข้อมูลรีวิว (สำหรับหน้า Home เท่านั้น)

$reviews = [];
if ($action == 'home' || $action == '') {
    try {
        $sql = "SELECT r.*, u.full_name, u.username
                FROM review_course r JOIN user u ON r.user_id = u.user_id
                ORDER BY r.created_at DESC LIMIT 3";
        $reviews = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
    }
}

// 2. เริ่มเก็บ Output ลง Buffer (เพื่อส่งไป Layout)

ob_start();
switch ($action) {
    case 'courses':
        // [ Logic ดึงข้อมูลคอร์ส หน้า Guest ]
        // Join กับ promotion_course เพื่อดึงส่วนลด (เอาส่วนลดมากที่สุดถ้ามีซ้อนกัน)
        $sql = "SELECT c.*, 
                   (SELECT capacity FROM course_schedule s WHERE s.course_id = c.course_id AND s.start_at > CURDATE() ORDER BY s.start_at ASC LIMIT 1) AS capacity,
                   (SELECT schedule_id FROM course_schedule s WHERE s.course_id = c.course_id AND s.start_at > CURDATE() ORDER BY s.start_at ASC LIMIT 1) AS next_schedule_id,
                   MAX(p.discount) as promo_discount
                FROM course c
                LEFT JOIN promotion_course p ON c.course_id = p.course_id 
                    AND p.visible = 1 
                    AND CURDATE() BETWEEN DATE(p.start_at) AND DATE(p.end_at)
                WHERE c.is_active = 1 
                GROUP BY c.course_id
                ORDER BY c.course_type DESC, c.course_id ASC";
        try {
            $stmt = $pdo->query($sql);
            $allCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $allCourses = [];
        }

        // [เพิ่ม] ดึงข้อมูลการจองของผู้ใช้ (ถ้าล็อกอิน) - เช็คตาม schedule_id
        $userBookingMap = [];
        if (isset($_SESSION['user_id'])) {
            // [แก้ไข] เพิ่ม 'Refunded' ในรายการที่ไม่นับเป็นการจอง (เพื่อให้สมัครใหม่ได้)
            $chkSql = "SELECT b.schedule_id, b.status 
                       FROM booking b 
                       WHERE b.user_id = ? AND b.status NOT IN ('Rejected', 'Cancelled', 'Refunded')";
            $chkStmt = $pdo->prepare($chkSql);
            $chkStmt->execute([$_SESSION['user_id']]);
            $rows = $chkStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $r) {
                // map status by schedule_id
                $userBookingMap[$r['schedule_id']] = $r['status'];
            }
        }

        $coursesByType = [];
        foreach ($allCourses as $course) {
            // map booking status เฉพาะรอบที่กำลังแสดง (next_schedule_id)
            $sid = $course['next_schedule_id'];
            $course['booking_status'] = ($sid && isset($userBookingMap[$sid])) ? $userBookingMap[$sid] : null;

            $type = $course['course_type'];
            if (!isset($coursesByType[$type])) {
                $coursesByType[$type] = [];
            }
            $coursesByType[$type][] = $course;
        }
        $data['courses_by_type'] = $coursesByType;
        $title = "หลักสูตร - Bangkok Spa Academy";
        include VIEW_PATH . '/guest/courses_list.php';
        break;

    case 'course_detail':
        // [ Logic ดูรายละเอียดคอร์ส หน้า Guest ]
        $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($course_id > 0) {
            $sql = "SELECT c.*, MAX(p.discount) as promo_discount 
                    FROM course c 
                    LEFT JOIN promotion_course p ON c.course_id = p.course_id 
                        AND p.visible = 1 
                        AND CURDATE() BETWEEN DATE(p.start_at) AND DATE(p.end_at)
                    WHERE c.course_id = ? AND c.is_active = 1
                    GROUP BY c.course_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$course) {
                header('Location: index.php?action=courses');
                exit;
            }
            $sqlSch = "SELECT * FROM course_schedule WHERE course_id = ? AND start_at > CURDATE() ORDER BY start_at ASC LIMIT 1";
            $stmtSch = $pdo->prepare($sqlSch);
            $stmtSch->execute([$course_id]);
            $schedule = $stmtSch->fetch(PDO::FETCH_ASSOC);
            $sqlRel = "SELECT * FROM course WHERE course_id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4";
            $stmtRel = $pdo->prepare($sqlRel);
            $stmtRel->execute([$course_id]);
            $relatedCourses = $stmtRel->fetchAll(PDO::FETCH_ASSOC);
            
            // Check existing booking (เฉพาะรอบเรียนที่กำลังจะแสดงนี้)
            $existingBookingStatus = null;
            if (isset($_SESSION['user_id']) && $schedule) {
                // [แก้ไข] เพิ่ม 'Refunded' เพื่อให้สมัครใหม่ได้เมื่อถูกคืนเงินแล้ว
                $chkSql = "SELECT b.status FROM booking b 
                           WHERE b.user_id = ? AND b.schedule_id = ? 
                           AND b.status NOT IN ('Rejected', 'Cancelled', 'Refunded') 
                           LIMIT 1";
                $chkStmt = $pdo->prepare($chkSql);
                $chkStmt->execute([$_SESSION['user_id'], $schedule['schedule_id']]);
                $exRes = $chkStmt->fetch(PDO::FETCH_ASSOC);
                if ($exRes) {
                    $existingBookingStatus = $exRes['status'];
                }
            }
            
            // [NEW] Rating & Reviews Logic
            require_once APP_PATH . '/models/Review.php';
            $reviewModel = new Review($pdo);
            $ratingStats = $reviewModel->getCourseRatingStats($course_id);
            
            // Pagination for reviews
            $reviewsPerPage = 5;
            $currentReviewPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($currentReviewPage - 1) * $reviewsPerPage;
            
            $sqlLimit = "SELECT r.*, u.full_name
                         FROM review_course r
                         JOIN user u ON r.user_id = u.user_id
                         WHERE r.course_id = :course_id
                         ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
            $stmtLimit = $pdo->prepare($sqlLimit);
            $stmtLimit->bindValue(':course_id', $course_id, PDO::PARAM_INT);
            $stmtLimit->bindValue(':limit', $reviewsPerPage, PDO::PARAM_INT);
            $stmtLimit->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmtLimit->execute();
            $reviews = $stmtLimit->fetchAll(PDO::FETCH_ASSOC);
            
            $totalReviews = $ratingStats['count'];
            $totalReviewPages = ceil($totalReviews / $reviewsPerPage);
            
            $title = $course['name'] . " - Bangkok Spa Academy";
            include VIEW_PATH . '/guest/course_detail.php';
        } else {
            header('Location: index.php?action=courses');
            exit;
        }
        break;


    case 'products':
        // 1. เรียก Model
        require_once APP_PATH . '/models/Product.php';
        $productModel = new Product($pdo);
        // เลือกใช้ getActiveProductsWithPromo เพื่อดึงส่วนลดมาด้วย
        $products = $productModel->getActiveProductsWithPromo();
        $title = 'สินค้าและผลิตภัณฑ์ | Bangkok Spa Academy';

        // 2. เรียกแค่ไฟล์เนื้อหา (ไม่ต้อง ob_start, ไม่ต้องเรียก layout)
        // เดี๋ยวโค้ดท้ายไฟล์มันจะดึงหน้านี้ไปใส่ใน Layout ให้เองครับ
        include VIEW_PATH . '/guest/products_list.php';
        break;
    case 'gallery':
        $title = "แกลเลอรี - Bangkok Spa Academy";
        ob_start();
        include VIEW_PATH . '/guest/gallery.php';
        $content = ob_get_clean();
        include "../app/views/layouts/main_layout.php";
        exit;
        break;

    case 'contact':
        $title = "ติดต่อเรา - Bangkok Spa Academy";

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/helpers/EmailHelper.php';

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (!empty($name) && !empty($email) && !empty($message)) {
                $subject = "ข้อความใหม่จากหน้าติดต่อเรา: " . $name;
                $body = "<h3>ข้อความจากผู้เยี่ยมชมเว็บไซต์</h3>";
                $body .= "<p><strong>ชื่อ:</strong> " . htmlspecialchars($name) . "</p>";
                $body .= "<p><strong>อีเมล:</strong> " . htmlspecialchars($email) . "</p>";
                $body .= "<p><strong>ข้อความ:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";

                // Use email from .env
                $adminEmail = $_ENV['SMTP_FROM_EMAIL'] ;
                
                if (sendEmail($adminEmail, 'Admin', $subject, $body)) {
                        $_SESSION['success'] = "ขอบคุณสำหรับข้อความ เราจะติดต่อกลับโดยเร็วที่สุด";
                } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งข้อความ โปรดลองอีกครั้ง";
                }
            } else {
                $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
            }

            header("Location: index.php?action=contact");
            exit;
        }

        ob_start();
        include VIEW_PATH . '/guest/contact.php';
        $content = ob_get_clean();
        include "../app/views/layouts/main_layout.php";
        exit;
        break;

    case 'about':
        $title = "เกี่ยวกับเรา - Bangkok Spa Academy";

        // 1. เริ่มคำสั่ง Buffer (เพื่อเก็บ HTML ลงตัวแปร)
        ob_start();
        include VIEW_PATH . '/guest/about_us.php';
        $content = ob_get_clean(); // เอาเนื้อหาใส่ตัวแปร $content

        // 2. เรียก Layout
        include "../app/views/layouts/main_layout.php";
        exit;
        break;

    // =========================================================
    // CUSTOMER LOGIC
    // =========================================================

    case 'profile':
        // [ Logic หน้า Profile ]
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $message = '';

        // ส่วน Update User (ถ้ามีโค้ดเดิมอยู่ ให้คงไว้ หรือข้ามไปถ้าไม่ได้แปะมา)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
            // ... ใส่ Logic อัปเดตข้อมูลส่วนตัวของคุณตรงนี้ ...
        }

        // 1. ดึงข้อมูล User
        $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. ดึงข้อมูลคอร์ส + รีวิว (SQL ชุดสมบูรณ์)
        $myCourses = [];
        try {
            $sqlCourses = "SELECT 
                                b.booking_id, b.status, b.booked_at, b.confirmed_at,
                                b.refund_bank_name, b.refund_bank_account, b.refund_slip,
                                c.name AS course_name, c.course_picture, c.price,
                                s.start_at, s.end_at,

                                /* [จุดสำคัญ] ดึง ID และตั้งชื่อใหม่เป็น master_course_id เพื่อแก้ปัญหาชื่อซ้ำ */
                                s.course_id AS master_course_id,

                                /* ข้อมูลรีวิว (เพื่อเช็คว่ารีวิวไปหรือยัง) */
                                r.review_c_id, r.rating, r.comment

                           FROM booking b
                           JOIN course_schedule s ON b.schedule_id = s.schedule_id
                           JOIN course c ON s.course_id = c.course_id
                           
                           /* JOIN ตารางรีวิว (ชื่อตาราง review_course) */
                           LEFT JOIN review_course r ON b.booking_id = r.booking_id
                           
                           WHERE b.user_id = ? 
                           ORDER BY b.booked_at DESC";

            $stmtC = $pdo->prepare($sqlCourses);
            $stmtC->execute([$user_id]);
            $myCourses = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $myCourses = []; // ถ้า Error ให้เป็น Array ว่าง
        }

        $title = "ข้อมูลส่วนตัว - Bangkok Spa Academy";
        include VIEW_PATH . '/member/profile.php';
        break;

    case 'booking_form':
        // [ Logic แสดงฟอร์มจอง (AJAX/Modal) ]
        while (ob_get_level()) {
            ob_end_clean();
        }
        if (!isset($_SESSION['user_id'])) {
            echo '<div style="padding:20px; text-align:center; color:red;">กรุณาเข้าสู่ระบบก่อนสมัครเรียน</div>';
            exit;
        }
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
        $stmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ? AND is_active = 1");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        $sqlSch = "SELECT * FROM course_schedule WHERE course_id = ? AND start_at > CURDATE() ORDER BY start_at ASC LIMIT 1";
        $stmtSch = $pdo->prepare($sqlSch);
        $stmtSch->execute([$course_id]);
        $schedule = $stmtSch->fetch(PDO::FETCH_ASSOC);
        $stmtUser = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        // [New] Fetch Active Promotion for Display
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $activePromo = $promoModel->getActiveCoursePromotion($course_id);

        if ($course && $schedule) {
            include VIEW_PATH . '/member/booking_form.php';
        } else {
            echo '<div style="padding:20px; text-align:center;">ไม่พบข้อมูลคอร์ส หรือรอบเรียน</div>';
        }
        exit;
        break;
    /* -------------------------------------------------------------------------
   SYSTEM: REFUND & CANCELLATION (INTEGRATED EMAIL)
   ------------------------------------------------------------------------- */

    // 1. ผู้เรียน: ส่งคำร้องขอคืนเงิน (Request Refund) - ส่งหา Staff ทุกคน
    case 'request_refund':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        // โหลด Helper
        require_once __DIR__ . '/../app/helpers/EmailHelper.php';

        $booking_id = $_POST['booking_id'];
        $user_id = $_SESSION['user_id'];

        // 1. ดึงข้อมูลการจอง + ข้อมูลบัญชีผู้เรียน + ข้อมูลคอร์ส
        $sql = "SELECT b.*, u.bank_name, u.bank_account, u.full_name, u.email, 
                       c.name as course_name, c.price as original_price, c.course_id, p.amount as paid_amount
                FROM booking b 
                JOIN user u ON b.user_id = u.user_id 
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                LEFT JOIN payment p ON b.booking_id = p.booking_id
                WHERE b.booking_id = :bid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['bid' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบความถูกต้อง
        if (!$booking || $booking['user_id'] != $user_id) {
            echo "<script>alert('ไม่พบรายการจอง'); window.history.back();</script>";
            exit;
        }

        if (empty($booking['bank_name']) || empty($booking['bank_account'])) {
            echo "<script>alert('กรุณากรอกข้อมูลธนาคารในหน้าข้อมูลส่วนตัวให้ครบถ้วนก่อนครับ'); window.location='index.php?action=profile&tab=profile';</script>";
            exit;
        }

        // ตรวจสอบกฎ 3 วัน
        if ($booking['status'] == 'Confirmed') {
            $confirmedDate = new DateTime($booking['confirmed_at']);
            $diff = (new DateTime())->diff($confirmedDate)->days;
            if ($diff > 3) {
                echo "<script>alert('เกินกำหนดระยะเวลา 3 วัน ไม่สามารถขอคืนเงินได้'); window.history.back();</script>";
                exit;
            }
        }

        try {
            // 2. อัปเดตสถานะการจอง
            $sql = "UPDATE booking SET 
                    status = 'RefundPending',
                    refund_bank_name = :bn,
                    refund_bank_account = :ba,
                    refund_request_at = NOW() 
                    WHERE booking_id = :bid";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'bn' => $booking['bank_name'],
                'ba' => $booking['bank_account'],
                'bid' => $booking_id
            ]);

            // คำนวณยอดเงินที่คืน (Net Amount) จากยอดที่จ่ายจริง
            $net_refund_amount = $booking['paid_amount'] ?? $booking['original_price'];
            $refund_amount_text = number_format($net_refund_amount, 2);

            // 3. เตรียมเนื้อหาอีเมล
            $subject = "แจ้งเตือน: คำขอคืนเงินใหม่ (Booking #$booking_id)";
            $bodyHtml = "
                <h3>มีการขอยกเลิกคอร์สและขอคืนเงิน</h3>
                <p><strong>รหัสการจอง:</strong> #$booking_id</p>
                <p><strong>ผู้เรียน:</strong> {$booking['full_name']}</p>
                <p><strong>หลักสูตร:</strong> {$booking['course_name']}</p>
                <p><strong>ยอดเงินที่ต้องคืน:</strong> {$refund_amount_text} บาท</p>
                <p><strong>วันเวลาที่แจ้ง:</strong> " . date('d/m/Y H:i') . "</p>
                <hr>
                <p><strong>ข้อมูลสำหรับโอนคืน:</strong></p>
                <p>ธนาคาร: {$booking['bank_name']}</p>
                <p>เลขบัญชี: {$booking['bank_account']}</p>
                <br>
                <p><a href='http://localhost/BangkokSpa/index.php?action=staff_refund_list'>คลิกเพื่อตรวจสอบรายการ</a></p>
            ";

            // 4. [ส่วนที่แก้ไข] ดึงอีเมลพนักงานทุกคน (Role=2, Active=1) และวนลูปส่ง
            $sqlStaff = "SELECT email, full_name FROM user WHERE role_id = 2 AND is_active = 1";
            $stmtStaff = $pdo->prepare($sqlStaff);
            $stmtStaff->execute();
            $allStaffs = $stmtStaff->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allStaffs as $staff) {
                if (!empty($staff['email'])) {
                    // ส่งหาทีละคน (ใช้ @ เพื่อไม่ให้ error ขัดจังหวะถ้ารายชื่อใดมีปัญหา)
                    @sendEmail($staff['email'], $staff['full_name'], $subject, $bodyHtml);
                }
            }

            // 5. เด้งกลับหน้า Profile
            header('Location: index.php?action=profile&msg=refund_success');
            exit;
        } catch (Exception $e) {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "'); window.history.back();</script>";
        }
        break;


    case 'staff_submit_refund':
        // โหลดไฟล์ Helper
        require_once __DIR__ . '/../app/helpers/EmailHelper.php';
       
        // [เพิ่ม] โหลด Model Course เพื่อใช้ฟังก์ชันจัดการ Capacity
        require_once APP_PATH . '/../app/models/Course.php'; 

        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
            die("Access Denied");
        }

        $booking_id = $_POST['booking_id'];

        // --- 1. จัดการอัปโหลดไฟล์สลิปคืนเงิน ---
        $slipPath = null;
        if (!empty($_FILES['refund_slip']['name'])) {
            $targetDir = "assets/uploads/slips/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_refund_" . $booking_id . "." . pathinfo($_FILES['refund_slip']['name'], PATHINFO_EXTENSION);

            if (move_uploaded_file($_FILES['refund_slip']['tmp_name'], $targetDir . $fileName)) {
                $slipPath = $targetDir . $fileName; 
            }
        }

        if (!$slipPath) {
            echo "<script>alert('กรุณาแนบหลักฐานการโอนเงิน'); window.history.back();</script>";
            exit;
        }

        try {
            $pdo->beginTransaction();

            // ====================================================
            // [ส่วนที่เพิ่มใหม่] คืนค่า Capacity (+1 ที่นั่ง)
            // ====================================================
            
            // 1. ดึง schedule_id ออกมาจาก booking นี้ก่อน
            $stmtGetSch = $pdo->prepare("SELECT schedule_id FROM booking WHERE booking_id = :bid");
            $stmtGetSch->execute(['bid' => $booking_id]);
            $bookingData = $stmtGetSch->fetch(PDO::FETCH_ASSOC);

            if ($bookingData) {
                $schedule_id = $bookingData['schedule_id'];

                // 2. เรียก Model และสั่งบวก 1 (คืนที่นั่ง)
                // ตรวจสอบให้แน่ใจว่า CourseModel รับ $pdo ใน constructor
                $courseModel = new Course($pdo); 
                
                // ส่งค่า 1 เข้าไปเพื่อเพิ่มที่นั่งกลับคืน
                $courseModel->updateScheduleCapacity($schedule_id, 1);
            }
            // ====================================================

            // --- 2. อัปเดตฐานข้อมูล (เปลี่ยนสถานะเป็น Cancelled ตามที่ตกลงเพื่อให้อนุญาตจองใหม่ได้) ---
            $sql = "UPDATE booking SET 
                    status = 'Cancelled',
                    refund_slip = :slip,
                    refund_date = NOW()
                    WHERE booking_id = :bid";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['slip' => $slipPath, 'bid' => $booking_id]);

            // --- 3. ดึงข้อมูลผู้เรียน (เพื่อส่งเมลแจ้งเตือน) ---
            $sqlInfo = "SELECT b.*, u.email, u.full_name, c.name as course_name, c.price as original_price, c.course_id, p.amount as paid_amount
                        FROM booking b
                        JOIN user u ON b.user_id = u.user_id
                        JOIN course_schedule s ON b.schedule_id = s.schedule_id
                        JOIN course c ON s.course_id = c.course_id
                        LEFT JOIN payment p ON b.booking_id = p.booking_id
                        WHERE b.booking_id = :bid";
            $stmtInfo = $pdo->prepare($sqlInfo);
            $stmtInfo->execute(['bid' => $booking_id]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            // คำนวณยอดเงินที่คืนจริงจากยอดที่จ่ายจริง
            $net_refund_amount = $info['paid_amount'] ?? $info['original_price'];
            $refund_amount_text = number_format($net_refund_amount, 2);

            $pdo->commit();

            // --- 4. ส่งอีเมลแบบฝังรูป (Embedded Image) ---
            $subject = "แจ้งผลการคืนเงิน: Booking #$booking_id";
            $bodyHtml = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h3 style='color: #28a745;'>การขอคืนเงินของท่านได้รับการอนุมัติแล้ว</h3>
                    <p>เรียนคุณ {$info['full_name']},</p>
                    <p>ทางสถาบันได้ทำการโอนเงินคืนสำหรับคอร์ส <strong>{$info['course_name']}</strong> เรียบร้อยแล้ว</p>
                    <p><strong>จำนวนเงิน:</strong> {$refund_amount_text} บาท</p>
                    <p><strong>วันที่โอน:</strong> " . date('d/m/Y H:i') . "</p>
                    <hr>
                    <p><strong>หลักฐานการโอนเงิน:</strong></p>
                    <div style='margin: 15px 0; border: 1px solid #ddd; padding: 10px; display: inline-block; background-color: #f9f9f9;'>
                        <img src='cid:refund_slip_cid' style='max-width: 100%; height: auto; max-height: 500px;' alt='Refund Slip'>
                    </div>
                    <hr>
                    <small>Bangkok Spa Academy</small>
                </div>
            ";

            @sendEmail($info['email'], $info['full_name'], $subject, $bodyHtml, $slipPath);

            echo "<script>alert('บันทึกการคืนเงินเรียบร้อยแล้ว (คืนที่นั่งให้ระบบแล้ว)'); window.location='index.php?action=staff_refund_list';</script>";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
        }
        break;
    // 3. แก้ไข Case เปิดหน้า Staff Refund List (ให้ใช้ Layout)
    case 'staff_refund_list':
        // ตรวจสอบสิทธิ์ (role_id 2 = Staff)
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php');
            exit;
        }

        // ดึงรายการรอคืนเงิน (เพิ่ม c.price, c.course_id, p.amount)
        $sql = "SELECT b.*, u.full_name, c.name as course_name, c.course_id, c.price as original_price, p.amount as paid_amount
                FROM booking b 
                JOIN user u ON b.user_id = u.user_id 
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                LEFT JOIN payment p ON b.booking_id = p.booking_id
                WHERE b.status = 'RefundPending' 
                ORDER BY b.booked_at ASC";
        $stmt = $pdo->query($sql);
        $refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // คำนวณราคาสุทธิ (หักส่วนลด)
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);

        foreach ($refunds as &$row) {
            $bookingDate = $row['booked_at'];
            $courseId = $row['course_id'];
            
            // หาโปรโมชั่น ณ วันที่ลูกค้ากดจอง (ไม่สนใจว่าตอนนี้โปรจะหมดอายุหรือปิดไปแล้ว)
            $activePromo = $promoModel->getPromotionAtDate($courseId, $bookingDate);
            
            // ค่าเริ่มต้น
            $row['discount_percent'] = 0;
            $row['discount_amount'] = 0;
            $row['net_price'] = $row['paid_amount'] ?? $row['original_price'];

            if ($activePromo) {
                $row['discount_percent'] = intval($activePromo['discount']);
                $row['discount_amount'] = ($row['original_price'] * $activePromo['discount']) / 100;
                $row['net_price'] = $row['original_price'] - $row['discount_amount'];
            }
        }
        unset($row); // ป้องกันบั๊ก loop reference

        // กำหนดชื่อหน้า
        $title = "จัดการรายการขอคืนเงิน - Staff Panel";

        // กำหนดไฟล์เนื้อหาที่จะไปแสดงใน Layout (ชี้ไปที่ไฟล์ staff_refund.php ของคุณ)
        // ** ตรวจสอบ Path ให้ถูกต้องตามโครงสร้างจริงของคุณ **
        $content_view = APP_PATH . '/views/staff/bookings/staff_refund.php';

        // เรียกใช้ Layout หลักของ Staff
        require_once APP_PATH . '/views/layouts/staff_layout.php';
        exit;
        break;

    // 4. หน้าประวัติการคืนเงิน (Refund History)
    case 'staff_refund_history':
        // ตรวจสอบสิทธิ์พนักงาน
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php');
            exit;
        }

        // ดึงรายการที่คืนเงินสำเร็จแล้ว (Status = Refunded)
        // เรียงตามวันที่โอนคืนล่าสุดขึ้นก่อน
        $sql = "SELECT b.*, u.full_name, c.name as course_name, c.price
                FROM booking b
                JOIN user u ON b.user_id = u.user_id
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                WHERE b.status = 'Refunded'
                ORDER BY b.refund_date DESC";

        $stmt = $pdo->query($sql);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $title = "ประวัติการคืนเงิน - Staff Panel";

        // กำหนดไฟล์ View (สร้างไฟล์นี้ในขั้นตอนถัดไป)
        $content_view = APP_PATH . '/views/staff/bookings/staff_refund_history.php';

        // เรียกใช้ Layout หลัก
        require_once APP_PATH . '/views/layouts/staff_layout.php';
        exit;
        break;

    // =========================================================
    // Review Logic
    // =========================================================
    case 'submit_review':
        // 1. ตรวจสอบว่ามีการ POST มาจริงหรือไม่
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Error: ไม่ใช่การส่งข้อมูลแบบ POST");
        }

        // 2. ตรวจสอบ Session
        if (!isset($_SESSION['user_id'])) {
            die("Error: คุณยังไม่ได้เข้าสู่ระบบ (Session user_id not found)");
        }

        // 3. รับค่าและตรวจสอบค่าว่าง
        $booking_id = $_POST['booking_id'] ?? null;
        $course_id  = $_POST['course_id'] ?? null;
        $rating     = $_POST['rating'] ?? null;
        $comment    = $_POST['comment'] ?? '';
        $user_id    = $_SESSION['user_id'];

        if (!$booking_id || !$course_id || !$rating) {
            die("Error: ข้อมูลไม่ครบถ้วน (booking_id, course_id หรือ rating หายไป) <br> รับค่าได้: booking_id=$booking_id, course_id=$course_id, rating=$rating");
        }

        // 4. จัดการรูปภาพ (เพิ่มการเช็ค Error)
        $imagePath = null;
        if (!empty($_FILES['review_image']['name'])) {
            $targetDir = "assets/uploads/reviews/";

            // สร้าง Folder ถ้ายังไม่มี
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    die("Error: ไม่สามารถสร้างโฟลเดอร์ $targetDir ได้ (ติด permission)");
                }
            }

            $fileName = time() . '_' . basename($_FILES['review_image']['name']);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['review_image']['tmp_name'], $targetFilePath)) {
                $imagePath = $targetFilePath;
            } else {
                die("Error: อัปโหลดรูปภาพล้มเหลว (move_uploaded_file failed) เช็ค Permission โฟลเดอร์");
            }
        }

        // 5. บันทึกลงฐานข้อมูล (เพิ่ม try-catch และ print error)
        try {
            // เรียก Model
            $result = $bookingModel->submitReview($booking_id, $user_id, $course_id, $rating, $comment, $imagePath);

            if ($result) {
                echo "<script>alert('บันทึกรีวิวเรียบร้อยแล้ว!'); window.location='index.php?action=profile';</script>";
            } else {
                die("Error: บันทึกลงฐานข้อมูลไม่สำเร็จ (Execute return false)");
            }
        } catch (Exception $e) {
            die("Error Exception: " . $e->getMessage());
        }
        break;




    // 1. ประมวลผลการจอง (บันทึกข้อมูล + อัปโหลดสลิป) - [ตัด Capacity ทันที]
    case 'process_enroll':
        require_once APP_PATH . '/helpers/EmailHelper.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // [สำคัญ] แก้ไข: แปลงค่า schedule_id ให้เป็น integer ทันที
        $schedule_id = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : null;

        $course_id = $_POST['course_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $slip_path = null;

        // ตรวจสอบ Input หลังแปลงค่า (ถ้า intval() ทำให้ค่าเป็น 0 หรือว่าง จะถูกดักจับ)
        if (!$schedule_id || !$course_id || !$amount) {
            echo "<script>alert('ข้อผิดพลาด: ข้อมูลหลักสูตรไม่ครบถ้วน (Schedule ID อาจเป็น 0)'); window.history.back();</script>";
            exit;
        }

        try {
            $pdo->beginTransaction();

            // ตรวจสอบ Capacity และดึง course_id ที่ถูกต้อง
            $stmt = $pdo->prepare("SELECT capacity, course_id, start_at FROM course_schedule WHERE schedule_id = ?");
            $stmt->execute([$schedule_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            // [ตรวจสอบเงื่อนไขเวลา] ต้องสมัครก่อนวันเริ่มเรียน (Start Date > Today)
            if ($schedule && $schedule['start_at'] <= date('Y-m-d')) {
                throw new Exception("ขออภัย หมดเขตรับสมัครแล้ว (ต้องสมัครก่อนวันเริ่มเรียน)");
            }

            // [New] Recalculate Amount (Security Check)
            $stmtCourse = $pdo->prepare("SELECT price FROM course WHERE course_id = ?");
            $stmtCourse->execute([$course_id]);
            $courseData = $stmtCourse->fetch(PDO::FETCH_ASSOC);
            
            if ($courseData) {
                require_once APP_PATH . '/models/Promotion.php';
                $promoModel = new Promotion($pdo);
                $activePromo = $promoModel->getActiveCoursePromotion($course_id);
                
                $finalAmount = $courseData['price'];
                if ($activePromo) {
                     $discountVal = ($courseData['price'] * $activePromo['discount']) / 100;
                     $finalAmount = $courseData['price'] - $discountVal;
                }
                
                // Override user input amount
                $amount = $finalAmount;
            }

            // [ตรวจสอบอีกครั้ง] ถ้า capacity เป็น 0 หรือ schedule ไม่พบ
            if (!$schedule || $schedule['capacity'] <= 0) {
                throw new Exception("ขออภัย ที่นั่งเต็มแล้ว หรือไม่พบรอบเรียนนี้");
            }

            // [เพิ่ม] ตรวจสอบการสมัครซ้ำ (Duplicate Booking Check)
            // เช็คเฉพาะรอบเรียนนี้ (schedule_id) อนุญาตให้สมัคร Course เดิมในรอบอื่นได้
            $chkBooking = $pdo->prepare("SELECT b.status FROM booking b 
                                         WHERE b.user_id = ? AND b.schedule_id = ? 
                                         AND b.status NOT IN ('Rejected', 'Cancelled', 'Refunded') 
                                         LIMIT 1");
            $chkBooking->execute([$user_id, $schedule_id]);
            $existing = $chkBooking->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // เพิ่มข้อความแสดงสถานะให้ชัดเจนขึ้น
                throw new Exception("คุณได้สมัครหลักสูตรนี้ไปแล้ว (สถานะ: " . $existing['status'] . ") สามารถสมัครใหม่ได้เมื่อถูกปฏิเสธ, ยกเลิก หรือคืนเงินเท่านั้น");
            }

            // Support multiple slip images (1-3)
            if (!isset($_FILES['slip_files']) || empty(array_filter((array)$_FILES['slip_files']['name']))) {
                throw new Exception("กรุณาแนบสลิปโอนเงิน");
            }

            $target_dir = "assets/uploads/slips/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $slip_paths = [];
            $fileCount = count($_FILES['slip_files']['name']);
            if ($fileCount > 3) {
                throw new Exception("กรุณาแนบสลิปได้ไม่เกิน 3 รูป");
            }

            for ($i = 0; $i < $fileCount; $i++) {
                $name = $_FILES['slip_files']['name'][$i];
                if (empty($name)) continue;
                $tmp = $_FILES['slip_files']['tmp_name'][$i];
                if (!is_uploaded_file($tmp)) continue;
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = "slip_{$user_id}_" . time() . "_{$i}." . $ext;
                $path = $target_dir . $new_name;
                if (!move_uploaded_file($tmp, $path)) {
                    throw new Exception("ไม่สามารถอัปโหลดไฟล์สลิปได้");
                }
                $slip_paths[] = $path;
            }

            if (count($slip_paths) === 0) {
                throw new Exception("กรุณาแนบสลิปโอนเงิน");
            }

            // Insert Records (ใช้ $schedule_id ที่ถูกแปลงแล้ว)
            $sqlBooking = "INSERT INTO booking (user_id, schedule_id, status) VALUES (?, ?, 'Pending')";
            $pdo->prepare($sqlBooking)->execute([$user_id, $schedule_id]);
            $booking_id = $pdo->lastInsertId();
            $first_slip = $slip_paths[0];
            $sqlPay = "INSERT INTO payment (booking_id, status, amount, slip_url) VALUES (?, 'Pending', ?, ?)";
            $pdo->prepare($sqlPay)->execute([$booking_id, $amount, $first_slip]);

            $sqlSlip = "INSERT INTO booking_slip (booking_id, user_id, course_id, price, slip_url) VALUES (?, ?, ?, ?, ?)";
            $stmtSlip = $pdo->prepare($sqlSlip);
            foreach ($slip_paths as $sp) {
                $stmtSlip->execute([$booking_id, $user_id, $course_id, $amount, $sp]);
            }

            // [สำคัญ] ตัด Capacity ทันที และตรวจสอบผลลัพธ์ของการตัด
            $capacity_updated = $courseModel->updateScheduleCapacity($schedule_id, -1);

            if (!$capacity_updated) {
                // โยน Exception ที่แสดงว่าการอัปเดต SQL ล้มเหลว (เพราะ Capacity พอแล้ว แต่ยังตัดไม่ได้)
                throw new Exception("ไม่สามารถตัดจำนวนที่นั่งได้ (เงื่อนไข SQL ไม่ผ่าน) กรุณาลองใหม่อีกครั้ง");
            }

            $pdo->commit();

            // --- เริ่มส่งอีเมลแจ้งพนักงาน ---
            // 1. ดึงข้อมูลคอร์สที่จองไป
            $sqlCourse = "SELECT c.name as course_name FROM course c WHERE c.course_id = ?";
            $stmtCourse = $pdo->prepare($sqlCourse);
            $stmtCourse->execute([$course_id]);
            $courseInfo = $stmtCourse->fetch(PDO::FETCH_ASSOC);
            $courseName = $courseInfo['course_name'] ?? 'คอร์สเรียน';

            // 2. ดึงอีเมลพนักงาน Role 1 (Admin) และ Role 2 (Staff)
            $stmtStaff = $pdo->prepare("SELECT email, full_name FROM user WHERE role_id IN (1, 2) AND is_active = 1");
            $stmtStaff->execute();
            $staffs = $stmtStaff->fetchAll(PDO::FETCH_ASSOC);

            // 3. สร้างอีเมล
            $subject = "🔔 มีรายการจองใหม่: " . $courseName;
            $body = "
                <h3>เรียน เจ้าหน้าที่</h3>
                <p>มีรายการจองคอร์สใหม่เข้ามาในระบบ กรุณาตรวจสอบสลิปและอนุมัติ</p>
                <div style='background:#f5f5f5; padding:15px; border-radius:10px; border:1px solid #ddd;'>
                    <strong>รายละเอียดการจอง #{$booking_id}</strong><br>
                    👤 ลูกค้า: " . htmlspecialchars($_SESSION['full_name']) . "<br>
                    📘 คอร์ส: " . htmlspecialchars($courseName) . "<br>
                    💰 จำนวนเงิน: " . number_format($amount) . " บาท<br>
                    🕐 เวลา: " . date('d/m/Y H:i') . "
                </div>
                <p style='margin-top:20px;'>
                    <a href='http://localhost/BangkokSpa/public/index.php?action=staff_booking_detail&id={$booking_id}' style='background:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>ตรวจสอบรายการ</a>
                </p>
            ";

            // 4. ส่งอีเมลหาพนักงานทั้งหมด
            foreach ($staffs as $staff) {
                if (!empty($staff['email'])) {
                    sendEmail($staff['email'], $staff['full_name'], $subject, $body);
                }
            }
            // --- จบส่วนส่งอีเมล ---

            echo "<script>alert('สมัครเรียนสำเร็จ! กรุณารอเจ้าหน้าที่ตรวจสอบสลิป'); window.location='index.php?action=profile';</script>";
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            if (isset($slip_paths) && is_array($slip_paths)) {
                foreach ($slip_paths as $p) {
                    if (file_exists($p)) @unlink($p);
                }
            } elseif (isset($slip_path) && file_exists($slip_path)) {
                @unlink($slip_path);
            }
            echo "<script>alert('การจองล้มเหลว: " . $e->getMessage() . "'); window.history.back();</script>";
            exit;
        }
        break;


    case 'staff_booking_update_status':

        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        $booking_id = $_POST['booking_id'] ?? null;
        $new_status = $_POST['status'] ?? null;
        if (!$booking_id || !$new_status) {
            exit;
        }
        $booking = $bookingModel->getBookingById($booking_id);
        if (!$booking) {
            exit;
        }
        $old_status = $booking['status'];
        $schedule_id = $booking['schedule_id'];
        $delta = 0;
        try {
            $pdo->beginTransaction();
            // คืน Capacity เมื่อเปลี่ยนสถานะไปสู่ Cancelled/Rejected
            if (($new_status === 'Cancelled' || $new_status === 'Rejected') && ($old_status !== 'Cancelled' && $old_status !== 'Rejected')) {
                $delta = 1;
            }
            $bookingModel->updateBookingStatus($booking_id, $new_status);
            if ($delta !== 0) {
                $courseModel->updateScheduleCapacity($schedule_id, $delta);
            }

            // อัปเดตข้อมูลผู้อนุมัติในตาราง payment ถ้าสถานะเป็น Confirmed
            if ($new_status === 'Confirmed') {
                $stmtPayment = $pdo->prepare("UPDATE payment SET verified_by = ?, verified_at = NOW() WHERE booking_id = ?");
                $stmtPayment->execute([$_SESSION['user_id'], $booking_id]);
            }

            $pdo->commit();
            header('Location: index.php?action=staff_booking_detail&id=' . $booking_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ: " . $e->getMessage() . "'); window.history.back();</script>";
            exit;
        }

        break;

    case 'staff_dashboard':
        // 1. ตรวจสอบสิทธิ์ (Auth Check)
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        // 2. เคลียร์ Output Buffer (ป้องกัน Header Error)
        while (ob_get_level()) {
            ob_end_clean();
        }

        // 3. คำนวณสถิติ Dashboard (Updated Logic)
        $stats = [];

        // 3.1 นับรายการจองใหม่ (รอตรวจสอบ)
        $sqlBooking = "SELECT COUNT(*) FROM booking WHERE status = 'Pending'";
        $stats['new_bookings'] = $pdo->query($sqlBooking)->fetchColumn();

        // 3.2 นับยอดขายวันนี้
        $sqlSales = "SELECT SUM(total_amount) FROM sale WHERE DATE(sold_at) = CURDATE()";
        $sales = $pdo->query($sqlSales)->fetchColumn();
        $stats['daily_sales'] = $sales ? $sales : 0;

        // 3.3 นับโปรโมชั่นที่ใช้งานอยู่ (Active)
        // ต้องนับทั้ง "สินค้า" และ "หลักสูตร" ที่ visible=1 และอยู่ในช่วงเวลา
        $sqlActiveProd = "SELECT COUNT(*) FROM promotion_product 
                          WHERE visible = 1 AND NOW() BETWEEN start_at AND end_at";
        $cntProd = $pdo->query($sqlActiveProd)->fetchColumn();

        $sqlActiveCourse = "SELECT COUNT(*) FROM promotion_course 
                            WHERE visible = 1 AND NOW() BETWEEN start_at AND end_at";
        $cntCourse = $pdo->query($sqlActiveCourse)->fetchColumn();

        $stats['active_promotions'] = $cntProd + $cntCourse;


        // 4. ดึงรายการจองล่าสุด (Recent Bookings)
        $sqlRecent = "SELECT b.booking_id, b.status, b.booked_at, u.full_name, c.name as course_name 
                      FROM booking b 
                      JOIN user u ON b.user_id = u.user_id 
                      JOIN course_schedule s ON b.schedule_id = s.schedule_id 
                      JOIN course c ON s.course_id = c.course_id 
                      ORDER BY b.booking_id DESC LIMIT 5";
        $recent_bookings = $pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC);
        $sqlRefund = "SELECT COUNT(*) FROM booking WHERE status = 'RefundPending'";
        $stats['refund_pending'] = $pdo->query($sqlRefund)->fetchColumn();
        // 5. แสดงผลหน้าจอ (View)
        $page_header = "ภาพรวมระบบ (Dashboard)";
        $content_view = VIEW_PATH . '/staff/dashboard.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;
    // ==========================================
    // STAFF: จัดการสมาชิก (MEMBERS)
    // ==========================================

    // 1. แสดงรายชื่อสมาชิก (Role = 3)
    case 'staff_members':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        // ดึงเฉพาะ role_id = 3 (Member)
        $sql = "SELECT * FROM user WHERE role_id = 3 ORDER BY created_at DESC";
        $members = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $page_header = "จัดการสมาชิก";
        $content_view = VIEW_PATH . '/staff/members/list.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 2. แก้ไขข้อมูลสมาชิก
    case 'staff_member_edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // รับค่าจากฟอร์ม
            $full_name = $_POST['full_name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $bank_name = $_POST['bank_name'];
            $bank_account = $_POST['bank_account'];
            $is_active = $_POST['is_active'];
            $new_password = $_POST['new_password'] ?? '';

            if (!empty($new_password)) {
                // อัปเดตข้อมูลพร้อมรหัสผ่านใหม่
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE user SET full_name=?, phone=?, email=?, bank_name=?, bank_account=?, is_active=?, password=? WHERE user_id=? AND role_id=3";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$full_name, $phone, $email, $bank_name, $bank_account, $is_active, $hashed_password, $id]);
            } else {
                // อัปเดตข้อมูล (เฉพาะ Role 3) ไม่เปลี่ยนรหัสผ่าน
                $sql = "UPDATE user SET full_name=?, phone=?, email=?, bank_name=?, bank_account=?, is_active=? WHERE user_id=? AND role_id=3";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$full_name, $phone, $email, $bank_name, $bank_account, $is_active, $id]);
            }

            if ($result) {
                echo "<script>alert('บันทึกข้อมูลสำเร็จ'); window.location='index.php?action=staff_members';</script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาด'); window.history.back();</script>";
            }
            exit;
        }

        // ดึงข้อมูลเก่ามาแสดง
        $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ? AND role_id = 3");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            echo "<script>alert('ไม่พบข้อมูลสมาชิก'); window.location='index.php?action=staff_members';</script>";
            exit;
        }

        $page_header = "แก้ไขข้อมูลสมาชิก";
        $content_view = VIEW_PATH . '/staff/members/edit.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 3. ลบสมาชิก (โดยการ Delete)
    case 'staff_member_delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // ลบข้อมูล (Role 3 เท่านั้น เพื่อความปลอดภัย)
        $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND role_id = 3");
        if ($stmt->execute([$id])) {
            echo "<script>alert('ลบสมาชิกเรียบร้อยแล้ว'); window.location='index.php?action=staff_members';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการลบ'); window.location='index.php?action=staff_members';</script>";
        }
        exit;
        break;

    // ==========================================
    // STAFF: จัดการหลักสูตร (COURSES)
    // ==========================================

    // 4. แสดงรายการหลักสูตรทั้งหมด
    case 'staff_courses':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $sql = "SELECT * FROM course ORDER BY is_active DESC, course_id DESC";
        $courses = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $page_header = "จัดการหลักสูตร";
        $content_view = VIEW_PATH . '/staff/courses/list.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 5. เพิ่มหลักสูตรใหม่ (Create)
    case 'staff_course_create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $desc = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration_day'];
            $type = $_POST['course_type'];

            // อัปโหลดรูปภาพ
            $img_path = "";
            if (!empty($_FILES['course_picture']['name'])) {
                $ext = pathinfo($_FILES['course_picture']['name'], PATHINFO_EXTENSION);
                $new_name = "course_" . time() . "." . $ext;
                $target = "assets/images/courses/" . $new_name;
                if (!is_dir("assets/images/courses/")) mkdir("assets/images/courses/", 0777, true);
                if (move_uploaded_file($_FILES['course_picture']['tmp_name'], $target)) {
                    $img_path = $target;
                }
            }

            $sql = "INSERT INTO course (name, description, price, duration_day, course_type, course_picture, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $pdo->prepare($sql)->execute([$name, $desc, $price, $duration, $type, $img_path]);

            echo "<script>alert('เพิ่มหลักสูตรเรียบร้อย'); window.location='index.php?action=staff_courses';</script>";
            exit;
        }

        $page_header = "เพิ่มหลักสูตรใหม่";
        $content_view = VIEW_PATH . '/staff/courses/create.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 6. แก้ไขหลักสูตร (Edit)
    case 'staff_course_edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $desc = $_POST['description'];
            $price = $_POST['price'];
            $duration = $_POST['duration_day'];
            $type = $_POST['course_type'];
            $is_active = $_POST['is_active'];
            $old_img = $_POST['old_picture'];

            $img_path = $old_img;
            if (!empty($_FILES['course_picture']['name'])) {
                $ext = pathinfo($_FILES['course_picture']['name'], PATHINFO_EXTENSION);
                $new_name = "course_" . time() . "." . $ext;
                $target = "assets/images/courses/" . $new_name;
                if (!is_dir("assets/images/courses/")) mkdir("assets/images/courses/", 0777, true);
                if (move_uploaded_file($_FILES['course_picture']['tmp_name'], $target)) {
                    $img_path = $target;
                }
            }

            $sql = "UPDATE course SET name=?, description=?, price=?, duration_day=?, course_type=?, is_active=?, course_picture=? WHERE course_id=?";
            $pdo->prepare($sql)->execute([$name, $desc, $price, $duration, $type, $is_active, $img_path, $id]);

            echo "<script>alert('บันทึกการแก้ไขเรียบร้อย'); window.location='index.php?action=staff_courses';</script>";
            exit;
        }

        // ดึงข้อมูลเก่า
        $stmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $page_header = "แก้ไขหลักสูตร";
        $content_view = VIEW_PATH . '/staff/courses/edit.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 7. ลบหลักสูตร (ปิดการใช้งาน)
    case 'staff_course_delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        $id = $_GET['id'];
        $pdo->prepare("UPDATE course SET is_active = 0 WHERE course_id = ?")->execute([$id]);

        echo "<script>alert('ปิดการใช้งานหลักสูตรเรียบร้อย'); window.location='index.php?action=staff_courses';</script>";
        exit;
        break;

    // 8. หน้าฟอร์มเปิดรอบเรียน (Open Schedule Form)
    case 'staff_course_schedule':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $stmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            echo "<script>alert('ไม่พบข้อมูลหลักสูตร'); window.location='index.php?action=staff_courses';</script>";
            exit;
        }

        $page_header = "เปิดรอบเรียนใหม่";
        $content_view = VIEW_PATH . '/staff/courses/schedule.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 9. บันทึกการเปิดรอบเรียน (Process Open Schedule)
    case 'staff_course_open_schedule':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id = $_POST['course_id'];
            $start_at = $_POST['start_at'];
            $end_at = $_POST['end_at'];
            $capacity = $_POST['capacity'];

            // อัปเดตรูปภาพปกคอร์ส (ถ้ามีการแนบมา)
            if (!empty($_FILES['course_picture']['name'])) {
                $ext = pathinfo($_FILES['course_picture']['name'], PATHINFO_EXTENSION);
                $new_name = "course_" . time() . "." . $ext;
                $target = "assets/images/courses/" . $new_name;
                if (!is_dir("assets/images/courses/")) mkdir("assets/images/courses/", 0777, true);
                if (move_uploaded_file($_FILES['course_picture']['tmp_name'], $target)) {
                    $pdo->prepare("UPDATE course SET course_picture = ? WHERE course_id = ?")->execute([$target, $course_id]);
                }
            }

            // เพิ่มรอบเรียนลงตาราง course_schedule
            $sql = "INSERT INTO course_schedule (course_id, start_at, end_at, capacity) VALUES (?, ?, ?, ?)";
            if ($pdo->prepare($sql)->execute([$course_id, $start_at, $end_at, $capacity])) {
                echo "<script>alert('เปิดรอบเรียนเรียบร้อย'); window.location='index.php?action=staff_courses';</script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาด'); window.history.back();</script>";
            }
        }
        exit;
        break;

    // 10. ดูประวัติรอบเรียน (View Details)
    case 'staff_course_details':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $stmt = $pdo->prepare("SELECT * FROM course WHERE course_id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        // ดึงประวัติรอบเรียนและจำนวนคนจอง
        $sqlSch = "SELECT s.*, 
                          (SELECT COUNT(*) FROM booking b WHERE b.schedule_id = s.schedule_id AND b.status != 'Cancelled') as booked_count
                   FROM course_schedule s 
                   WHERE s.course_id = ? 
                   ORDER BY s.start_at DESC";
        $stmtSch = $pdo->prepare($sqlSch);
        $stmtSch->execute([$id]);
        $schedules = $stmtSch->fetchAll(PDO::FETCH_ASSOC);

        $page_header = "รายละเอียดรอบเรียน";
        $content_view = VIEW_PATH . '/staff/courses/details.php';
        include VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 11. พนักงานเปลี่ยนสถานะการจอง (คืน Capacity เมื่อยกเลิก/ปฏิเสธ)
    case 'staff_booking_update_status':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        $booking_id = $_POST['booking_id'] ?? null;
        $new_status = $_POST['status'] ?? null;
        if (!$booking_id || !$new_status) {
            exit;
        }

        $booking = $bookingModel->getBookingById($booking_id);
        if (!$booking) {
            exit;
        }

        $old_status = $booking['status'];
        $schedule_id = $booking['schedule_id'];
        $delta = 0;

        try {
            $pdo->beginTransaction();

            // คืน Capacity เมื่อเปลี่ยนสถานะไปสู่ Cancelled/Rejected
            if (($new_status === 'Cancelled' || $new_status === 'Rejected') && ($old_status !== 'Cancelled' && $old_status !== 'Rejected')) {
                $delta = 1;
            }

            $bookingModel->updateBookingStatus($booking_id, $new_status);
            if ($delta !== 0) {
                $courseModel->updateScheduleCapacity($schedule_id, $delta);
            }

            // อัปเดตข้อมูลผู้อนุมัติในตาราง payment ถ้าสถานะเป็น Confirmed
            if ($new_status === 'Confirmed') {
                $stmtPayment = $pdo->prepare("UPDATE payment SET verified_by = ?, verified_at = NOW() WHERE booking_id = ?");
                $stmtPayment->execute([$_SESSION['user_id'], $booking_id]);
            }

            $pdo->commit();
            header('Location: index.php?action=staff_booking_detail&id=' . $booking_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ: " . $e->getMessage() . "'); window.history.back();</script>";
            exit;
        }
        break;

    // ... (ในไฟล์ public/index.php ภายใต้ switch ($action))

    case 'staff_booking_list':
        // 1. ตรวจสอบสิทธิ์: ต้องเป็น Staff (role_id = 2)
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        // 2. ล้าง Buffer (สำคัญ! เพื่อลบ Navbar หน้าแรกทิ้งไป)
        while (ob_get_level()) {
            ob_end_clean();
        }

        $title = "จัดการรายการจอง";
        $bookings = [];

        try {
            $bookings = $bookingModel->getAllBookingsWithDetails();
        } catch (Exception $e) {
            error_log("Error fetching booking list: " . $e->getMessage());
        }

        // 3. กำหนดไฟล์เนื้อหา (View) ที่จะแสดงตรงกลาง
        $content_view = VIEW_PATH . '/staff/bookings/list.php';

        // 4. เรียก Staff Layout มาครอบเนื้อหา
        if (file_exists(VIEW_PATH . '/layouts/staff_layout.php')) {
            include VIEW_PATH . '/layouts/staff_layout.php';
        } else {
            // กรณีไม่มี Layout ให้แสดงเนื้อหาเพียวๆ (กัน Error)
            include $content_view;
        }

        // 5. จบการทำงานทันที (ห้ามลืม!)
        exit;

    case 'staff_booking_detail':
        // 1. ตรวจสอบสิทธิ์
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        // 2. ล้าง Buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        $booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($booking_id === 0) {
            header('Location: index.php?action=staff_booking_list');
            exit;
        }

        try {
            $bookingDetail = $bookingModel->getBookingFullDetails($booking_id);

            // [START Logic คำนวณส่วนลดสำหรับหน้า Staff]
            $bookingDetail['original_price'] = $bookingDetail['price'];
            $bookingDetail['final_price'] = $bookingDetail['paid_amount'] ?? $bookingDetail['price'];
            $bookingDetail['discount_percent'] = 0;
            
            if ($bookingDetail['original_price'] > 0 && $bookingDetail['final_price'] < $bookingDetail['original_price']) {
                $discountVal = $bookingDetail['original_price'] - $bookingDetail['final_price'];
                $bookingDetail['discount_percent'] = round(($discountVal / $bookingDetail['original_price']) * 100);
            }
            // [END Logic]

            if (!$bookingDetail) {
                echo "<script>alert('ไม่พบรายการจองนี้'); window.location='index.php?action=staff_booking_list';</script>";
                exit;
            }

            $title = "รายละเอียดการจอง #" . $booking_id;

            // 3. กำหนดไฟล์เนื้อหา (View) - ใช้ approve.php
            $content_view = VIEW_PATH . '/staff/bookings/approve.php';

            // 4. เรียก Staff Layout มาครอบ
            if (file_exists(VIEW_PATH . '/layouts/staff_layout.php')) {
                include VIEW_PATH . '/layouts/staff_layout.php';
            } else {
                include $content_view;
            }
        } catch (Exception $e) {
            // ให้แสดง Error บนหน้าจอเลย จะได้รู้ว่าผิดที่คอลัมน์ไหน
            echo "<h1>เกิดข้อผิดพลาด SQL:</h1>";
            echo "<h3>" . $e->getMessage() . "</h3>";
            exit;
        }

        // 5. จบการทำงานทันที
        exit;

        // ไฟล์: public/index.php (ใน switch case)

    case 'verify_payment':
        // เรียกใช้ Helper สำหรับส่งเมล
        require_once APP_PATH . '/helpers/EmailHelper.php';

        // ตรวจสอบสิทธิ์
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $booking_id = intval($_POST['booking_id']);
            $status = $_POST['status'];
            // รับค่า reject_note (ถ้าไม่มีส่งมา จะเป็น null)
            $reject_note = isset($_POST['reject_note']) ? trim($_POST['reject_note']) : null;

            try {
                // เรียก Model อัปเดตสถานะ
                $bookingModel->verifyPaymentAndUpdateBooking($booking_id, $status, $reject_note);

                // --- เริ่มส่วนส่งอีเมลหาลูกค้า ---
                // 1. ดึงข้อมูลลูกค้าและการจอง
                $sqlInfo = "SELECT u.email, u.full_name, c.course_id, c.name as course_name, 
                                   cs.start_at, cs.end_at, b.booking_id, b.booked_at, c.price as original_price, p.amount as paid_amount
                            FROM booking b
                            JOIN user u ON b.user_id = u.user_id
                            JOIN course_schedule cs ON b.schedule_id = cs.schedule_id
                            JOIN course c ON cs.course_id = c.course_id
                            LEFT JOIN payment p ON b.booking_id = p.booking_id
                            WHERE b.booking_id = ?";

                $stmtInfo = $pdo->prepare($sqlInfo);
                $stmtInfo->execute([$booking_id]);
                $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if ($info && $info['email']) {
                    $subject = "";
                    $body = "";

                    // สร้างเนื้อหาตามสถานะ
                    if ($status == 'Confirmed') {

                        // ==========================================
                        // [เพิ่ม] ระบบสร้างใบเสร็จ (HTML Receipt)
                        // ==========================================

                        // 1. สร้างเลขใบเสร็จและบันทึกลง DB
                        // (ต้องมีฟังก์ชัน createReceipt ใน Booking Model)
                        $receiptNo = $bookingModel->createReceipt($booking_id);

                        // [Fix] คำนวณส่วนลดจากยอดที่จ่ายจริง
                        $originalPrice = $info['original_price'];
                        $finalPrice = $info['paid_amount'] ?? $originalPrice;
                        $discountVal = 0;
                        $discountRow = "";

                        if ($originalPrice > 0 && $finalPrice < $originalPrice) {
                            $discountVal = $originalPrice - $finalPrice;
                            $discountPercent = round(($discountVal / $originalPrice) * 100);
                            
                            $discountRow = '
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;">ส่วนลด (' . $discountPercent . '%)</td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right; color: red;">-' . number_format($discountVal, 2) . '</td>
                            </tr>';
                        }
                        
                        $subject = "✅ การจองสำเร็จและใบเสร็จรับเงิน - " . $info['course_name'];

                        // 2. สร้าง HTML เนื้อหาอีเมล พร้อมใบเสร็จในตัว
                        $body = '
                        <div style="font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                            <div style="background-color: #4CAF50; padding: 20px; text-align: center; color: white;">
                                <h2 style="margin: 0;">ยืนยันการลงทะเบียนเรียน</h2>
                                <p style="margin: 5px 0 0;">Bangkok Spa Academy</p>
                            </div>
                            
                            <div style="padding: 20px;">
                                <p>เรียนคุณ <strong>' . $info['full_name'] . '</strong></p>
                                <p>การจองคอร์สเรียนของคุณได้รับการอนุมัติเรียบร้อยแล้ว รายละเอียดดังนี้:</p>
                                
                                <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #eee; margin-top: 15px;">
                                    <h3 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px; color: #333;">
                                        ใบเสร็จรับเงิน (Receipt)
                                    </h3>
                                    <p style="margin: 5px 0;"><strong>เลขที่:</strong> ' . $receiptNo . '</p>
                                    <p style="margin: 5px 0;"><strong>วันที่:</strong> ' . date('d/m/Y') . '</p>
                                    
                                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                                        <tr style="background-color: #eee;">
                                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">รายการ</th>
                                            <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">จำนวนเงิน</th>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $info['course_name'] . '</td>
                                            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">' . number_format($originalPrice, 2) . '</td>
                                        </tr>
                                        ' . $discountRow . '
                                        <tr>
                                            <td style="padding: 10px; text-align: right;"><strong>รวมสุทธิ</strong></td>
                                            <td style="padding: 10px; text-align: right; color: #4CAF50;"><strong>' . number_format($finalPrice, 2) . ' บาท</strong></td>
                                        </tr>
                                    </table>
                                </div>

                                <p style="margin-top: 20px;"><strong>รอบเรียน:</strong> ' . date('d/m/Y', strtotime($info['start_at'])) . ' - ' . date('d/m/Y', strtotime($info['end_at'])) . '</p>
                                
                                <p style="text-align: center; color: #777; font-size: 12px; margin-top: 30px;">
                                    กรุณาแสดงอีเมลนี้เมื่อมาถึงสถาบัน<br>
                                    ขอบคุณที่ไว้วางใจ Bangkok Spa Academy
                                </p>
                            </div>
                        </div>';
                    } elseif ($status == 'Rejected') {
                        $subject = "❌ แจ้งเตือน: การจองถูกปฏิเสธ";
                        $body = "
                            <h3>เรียนคุณ {$info['full_name']}</h3>
                            <p>ขออภัย การจองคอร์ส <strong>{$info['course_name']}</strong> ของคุณไม่ผ่านการอนุมัติ</p>
                            <p style='color:red;'>สาเหตุ: {$reject_note}</p>
                            <p>กรุณาติดต่อเจ้าหน้าที่หรือทำรายการใหม่</p>
                        ";
                    }

                    // สั่งส่งอีเมล
                    sendEmail($info['email'], $info['full_name'], $subject, $body);
                }
                // --- จบส่วนส่งอีเมล ---

                // แจ้งเตือนและเด้งกลับไปหน้าเดิม (แก้ปัญหาจอขาว)
                $msg = ($status == 'Confirmed') ? 'อนุมัติเรียบร้อย และส่งใบเสร็จทางอีเมลแล้ว' : 'บันทึกการปฏิเสธเรียบร้อย';
                echo "<script>
                        alert('$msg'); 
                        window.location.href = 'index.php?action=staff_booking_detail&id=$booking_id';
                      </script>";
                exit;
            } catch (Exception $e) {
                echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
                exit;
            }
        }
        break;

    // ... (โค้ดส่วนอื่นๆ ของ index.php) ...


    // =============================================
    // STAFF: Attendance (เช็กชื่อ)
    // =============================================

    // แสดงหน้า Check-in (รายวัน)
    case 'staff_attendance_checkin':
        require_once APP_PATH . '/models/Attendance.php';
        $attendModel = new Attendance($pdo);

        // รับวันที่จาก URL หรือใช้วันปัจจุบัน
        $filter_date = $_GET['date'] ?? date('Y-m-d');

        // ดึงข้อมูลตารางเรียนและนักเรียน
        $schedules = $attendModel->getSchedulesByDate($filter_date);
        $scheduleData = [];
        foreach ($schedules as $sch) {
            // [แก้ไข] ส่งวันที่เข้าไปเพื่อดึงสถานะของวันนั้นๆ
            $sch['students'] = $attendModel->getStudentsInSchedule($sch['schedule_id'], $filter_date);
            $scheduleData[] = $sch;
        }

        // เรียกไฟล์ View ตามโครงสร้างของคุณ
        $content_view = VIEW_PATH . '/staff/attendance/checkin.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // Report ประวัติการเข้าเรียน (PDF/Print)
    case 'staff_schedule_history':
        require_once APP_PATH . '/models/Attendance.php';
        $attendModel = new Attendance($pdo);
        $schedule_id = $_GET['id'] ?? 0;

        $reportData = $attendModel->getAttendanceReport($schedule_id);

        if (!$reportData) {
            echo "<script>alert('ไม่พบข้อมูลรอบเรียน'); window.history.back();</script>";
            exit;
        }

        // ถ้าต้องการ Print PDF
        // เราจะใช้ View ที่ออกแบบมาสำหรับ Print โดยเฉพาะ
        // ไม่โหลด Layout หลัก
        require_once VIEW_PATH . '/staff/attendance/history.php';
        exit;
        break;

    // บันทึกข้อมูล (Save)
    case 'staff_attendance_save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Attendance.php';
            $attendModel = new Attendance($pdo);

            $schedule_id = $_POST['schedule_id'];
            $redirect_date = $_POST['redirect_date'];
            // [เพิ่ม] รับค่า attendance_date (ควรจะเป็นค่าเดียวกับ redirect_date หรือแยกก็ได้)
            $attendance_date = $_POST['attendance_date'] ?? $redirect_date; 
            $present_users = $_POST['present_users'] ?? []; // รับ ID คนที่ติ๊กถูก

            $attendModel->saveAttendance($schedule_id, $present_users, $_SESSION['user_id'], $attendance_date);

            // กลับไปหน้าเดิม
            header("Location: index.php?action=staff_attendance_checkin&date=$redirect_date&status=success");
            exit;
        }
        break;

    case 'staff_product_list':
        // 1. ตรวจสอบสิทธิ์
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        require_once APP_PATH . '/models/Product.php';
        $productModel = new Product($pdo);
        $products = $productModel->getActiveProducts();


        $content_view = VIEW_PATH . '/staff/products/list.php';

        // 3. เรียก Layout
        require_once VIEW_PATH . '/layouts/staff_layout.php';

        exit; // จบการทำงานทันที
        break;

    // =============================================
    // STAFF: อัปเดตสต็อก (เติมของ / ขายของ)
    // =============================================
    case 'staff_product_update_stock':
        // 1. ตรวจสอบสิทธิ์ (เฉพาะ Admin/Staff)
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Product.php';
            $productModel = new Product($pdo);

            // รับค่าจากฟอร์ม
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $type       = $_POST['type'] ?? '';        // 'add' หรือ 'sell'
            $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
            $user_id    = $_SESSION['user_id'];        // รหัสพนักงานที่ทำรายการ

            // ตรวจสอบความถูกต้องของข้อมูล
            if ($product_id <= 0 || $quantity <= 0) {
                echo "<script>alert('ข้อมูลไม่ถูกต้อง กรุณาระบุจำนวนสินค้าให้ถูกต้อง'); window.history.back();</script>";
                exit;
            }

            $result = false;

            // 2. แยกการทำงานตามประเภท (เติมสต็อก vs ขายสินค้า)
            if ($type === 'add') {
                // กรณี: เติมสต็อก (Restock)
                // เรียกใช้ฟังก์ชัน adjustStock (บวกเพิ่ม)
                $result = $productModel->adjustStock($product_id, $quantity);
            } elseif ($type === 'sell') {
                // กรณี: ขายสินค้า (Sale)
                // เรียกใช้ฟังก์ชัน sellProduct (บันทึกการขาย + ตัดสต็อก)
                // ฟังก์ชันนี้เราเพิ่งเพิ่มไปใน Product.php
                $result = $productModel->sellProduct($user_id, $product_id, $quantity);
            }

            // 3. ตรวจสอบผลลัพธ์และแจ้งเตือน
            if ($result) {
                // สำเร็จ -> กลับไปหน้าเดิม
                // (อาจจะเพิ่ม Session Flash Message ตรงนี้ถ้ามีระบบแจ้งเตือน)
                header('Location: index.php?action=staff_product_list');
            } else {
                // ไม่สำเร็จ -> แจ้งเตือน
                echo "<script>
                        alert('ทำรายการไม่สำเร็จ! \\n- กรณีขาย: สินค้าคงเหลืออาจไม่พอ\\n- กรุณาลองใหม่อีกครั้ง'); 
                        window.history.back();
                      </script>";
            }
        } else {
            // ถ้าเข้ามาด้วยวิธีอื่นที่ไม่ใช่ POST ให้เด้งกลับ
            header('Location: index.php?action=staff_product_list');
        }
        break;

    // =============================================
    // STAFF: Create / Store / Edit / Update Product
    // =============================================

    // 1. หน้าฟอร์มเพิ่มสินค้า
    case 'staff_product_create':
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        $content_view = VIEW_PATH . '/staff/products/create.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 2. บันทึกสินค้าใหม่ (POST)
    case 'staff_product_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Product.php';
            $productModel = new Product($pdo);

            // จัดการอัปโหลดรูป
            $picture_path = null;
            if (!empty($_FILES['product_picture']['name'])) {
                $target_dir = "assets/images/products/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $file_name = time() . '_' . basename($_FILES["product_picture"]["name"]);
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["product_picture"]["tmp_name"], $target_file);
                $picture_path = $target_file;
            }

            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'sku' => $_POST['sku'],
                'stock' => $_POST['stock'],
                'product_type' => $_POST['product_type'],
                'product_picture' => $picture_path
            ];

            $productModel->createProduct($data);
            header('Location: index.php?action=staff_product_list');
        }
        break;

    // 3. หน้าฟอร์มแก้ไขสินค้า
    case 'staff_product_edit':
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        require_once APP_PATH . '/models/Product.php';
        $productModel = new Product($pdo);
        $product = $productModel->getProductById($_GET['id']);

        $content_view = VIEW_PATH . '/staff/products/edit.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 4. บันทึกการแก้ไข (POST)
    case 'staff_product_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Product.php';
            $productModel = new Product($pdo);
            $id = $_GET['id'];

            // จัดการอัปโหลดรูป (ถ้ามี)
            $picture_path = null;
            if (!empty($_FILES['product_picture']['name'])) {
                $target_dir = "assets/images/products/";
                $file_name = time() . '_' . basename($_FILES["product_picture"]["name"]);
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["product_picture"]["tmp_name"], $target_file);
                $picture_path = $target_file;
            }

            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'sku' => $_POST['sku'],
                'product_type' => $_POST['product_type'],
                'is_active' => $_POST['is_active'],
                'product_picture' => $picture_path // ถ้าเป็น null Model จะไม่ไปทับของเดิม
            ];

            $productModel->updateProduct($id, $data);
            header('Location: index.php?action=staff_product_list');
        }
        break;

    // =============================================
    // STAFF: รายงานยอดขาย (Sales Report)
    // =============================================

    // 1. หน้าแสดงรายการขายรายวัน (Sale List)
    case 'staff_sale_list':
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        require_once APP_PATH . '/models/Sale.php';
        $saleModel = new Sale($pdo);

        // รับค่าวันที่จาก GET (ถ้าไม่มี ให้ใช้วันปัจจุบัน)
        $filter_date = $_GET['date'] ?? date('Y-m-d');

        $sales = $saleModel->getSalesByDate($filter_date);
        $total_daily = $saleModel->getTotalDailyAmount($filter_date);

        // ดึงยอดขายคอร์สเรียน
        $sqlCourseSales = "SELECT p.payment_id, p.booking_id, p.amount, p.receipt_number, p.receipt_date,
                                  u.full_name as customer_name, c.name as course_name,
                                  staff.full_name as staff_name
                           FROM payment p
                           JOIN booking b ON p.booking_id = b.booking_id
                           JOIN user u ON b.user_id = u.user_id
                           JOIN course_schedule s ON b.schedule_id = s.schedule_id
                           JOIN course c ON s.course_id = c.course_id
                           LEFT JOIN user staff ON p.verified_by = staff.user_id
                           WHERE b.status = 'Confirmed' AND DATE(p.receipt_date) = :date
                           ORDER BY p.receipt_date DESC";
        $stmt = $pdo->prepare($sqlCourseSales);
        $stmt->execute(['date' => $filter_date]);
        $courseSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // คำนวณยอดรวมคอร์สเรียน
        $total_course_daily = 0;
        foreach ($courseSales as $cs) {
            $total_course_daily += $cs['amount'];
        }

        $total_daily += $total_course_daily;

        // เรียกไฟล์ View ชื่อ sale_list.php ตามที่ขอ
        $content_view = VIEW_PATH . '/staff/sales/sale_list.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 2. (AJAX) ดึงรายละเอียดสินค้าในบิล เพื่อแสดงใน Modal
    case 'staff_get_sale_details':
        if (!isset($_SESSION['role_id'])) exit;

        $sale_id = $_GET['sale_id'] ?? 0;
        require_once APP_PATH . '/models/Sale.php';
        $saleModel = new Sale($pdo);

        $items = $saleModel->getSaleItems($sale_id);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
        break;

    // =============================================
    // STAFF: Promotion Management
    // =============================================

    // 1. หน้ารายการโปรโมชั่น
    case 'staff_promotion_list':
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }

        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);

        $productPromos = $promoModel->getProductPromotions();
        $coursePromos = $promoModel->getCoursePromotions();

        $content_view = VIEW_PATH . '/staff/promotions/list.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // ==========================================
    // STAFF: ข้อมูลส่วนตัวพนักงาน
    // ==========================================
    case 'staff_profile':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
            header('Location: index.php?action=login');
            exit;
        }

        // 1. ดึงข้อมูลพนักงานจาก Session หรือ Database
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM user u LEFT JOIN role r ON u.role_id = r.role_id WHERE u.user_id = :uid");
        $stmt->execute(['uid' => $_SESSION['user_id']]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. จัดการการบันทึกข้อมูล (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $full_name = trim($_POST['full_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $bank_name = trim($_POST['bank_name'] ?? '');
                $bank_account = trim($_POST['bank_account'] ?? '');
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                // ตรวจสอบรหัสผ่านปัจจุบัน (ถ้ามีการใส่รหัสผ่านใหม่)
                if (!empty($new_password)) {
                    if ($staff['password'] !== $current_password) {
                        $_SESSION['error'] = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                    } elseif ($new_password !== $confirm_password) {
                        $_SESSION['error'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
                    } else {
                        // อัปเดตข้อมูล + รหัสผ่านใหม่
                        $stmt = $pdo->prepare("UPDATE user SET full_name = :fn, phone = :ph, email = :em, bank_name = :bn, bank_account = :ba, password = :pwd WHERE user_id = :uid");
                        $stmt->execute([
                            'fn' => $full_name,
                            'ph' => $phone,
                            'em' => $email,
                            'bn' => $bank_name,
                            'ba' => $bank_account,
                            'pwd' => $new_password,
                            'uid' => $_SESSION['user_id']
                        ]);
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['success'] = 'บันทึกข้อมูลและเปลี่ยนรหัสผ่านสำเร็จ';
                    }
                } else {
                    // อัปเดตเฉพาะข้อมูล (ไม่เปลี่ยนรหัสผ่าน)
                    $stmt = $pdo->prepare("UPDATE user SET full_name = :fn, phone = :ph, email = :em, bank_name = :bn, bank_account = :ba WHERE user_id = :uid");
                    $stmt->execute([
                        'fn' => $full_name,
                        'ph' => $phone,
                        'em' => $email,
                        'bn' => $bank_name,
                        'ba' => $bank_account,
                        'uid' => $_SESSION['user_id']
                    ]);
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['success'] = 'บันทึกข้อมูลสำเร็จ';
                }

                // ดึงข้อมูลใหม่หลังบันทึก
                $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM user u LEFT JOIN role r ON u.role_id = r.role_id WHERE u.user_id = :uid");
                $stmt->execute(['uid' => $_SESSION['user_id']]);
                $staff = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }

        // 3. สร้าง Message HTML
        $message = '';
        if (!empty($_SESSION['success'])) {
            $message = '<div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if (!empty($_SESSION['error'])) {
            $message = '<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }

        // 4. แสดงหน้า
        $content_view = VIEW_PATH . '/staff/profile.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 2. หน้าฟอร์มเพิ่มโปรฯ สินค้า
    case 'staff_promotion_product_create':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $products = $promoModel->getAllProducts();

        $content_view = VIEW_PATH . '/staff/promotions/create_product.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 3. บันทึกโปรฯ สินค้า (แก้ไขให้รองรับ Multiple Checkbox)
    case 'staff_promotion_product_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Promotion.php';
            $promoModel = new Promotion($pdo);

            // Upload รูปภาพ (ครั้งเดียว ใช้ร่วมกัน)
            $picture_path = null;
            if (!empty($_FILES['promotion_picture']['name'])) {
                $target_dir = "assets/images/promotions/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                // [Modified] Generate random filename to avoid issues with special characters/spaces
                $ext = pathinfo($_FILES["promotion_picture"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_p_' . bin2hex(random_bytes(8)) . '.' . $ext;
                
                $picture_path = $target_dir . $file_name;
                move_uploaded_file($_FILES["promotion_picture"]["tmp_name"], $picture_path);
            }

            // รับค่า product_id[]
            $product_ids = $_POST['product_id'];

            // [New] ตรวจสอบ product_ids ว่ามีค่าไหม (กัน error)
            if (!empty($product_ids) && is_array($product_ids)) {
                
                // [New] Check Overlap Before Insert
                foreach ($product_ids as $pid) {
                    if ($promoModel->checkProductOverlap($pid, $_POST['start_at'], $_POST['end_at'])) {
                        // ดึงชื่อสินค้าที่ซ้ำ
                        $pName = "ไม่ระบุ";
                        try {
                            $pName = $pdo->query("SELECT name FROM product WHERE product_id = $pid")->fetchColumn();
                        } catch(Exception $e){}

                        $_SESSION['error'] = "ไม่สามารถบันทึกได้: พบช่วงเวลาโปรโมชั่นซ้อนทับสำหรับสินค้า '$pName'";
                        header('Location: index.php?action=staff_promotion_product_create');
                        exit; 
                    }
                }

                // ✅ วนลูปบันทึกทีละสินค้า
                foreach ($product_ids as $pid) {
                    $data = [
                        'user_id' => $_SESSION['user_id'],
                        'product_id' => $pid, // ใช้ ID จากลูป
                        'discount' => $_POST['discount'],
                        'start_at' => $_POST['start_at'],
                        'end_at' => $_POST['end_at'],
                        'visible' => $_POST['visible'],
                        'picture' => $picture_path
                    ];
                    $promoModel->createProductPromotion($data);
                }
            }
            // แจ้งเตือนสําเร็จ
            $_SESSION['success'] = "เพิ่มโปรโมชั่นสินค้าเรียบร้อยแล้ว";
            header('Location: index.php?action=staff_promotion_list');
        }
        break;

    // ----------------------------------------------------
    // EDIT & TOGGLE PRODUCT PROMOTION
    // ----------------------------------------------------
    case 'staff_promotion_product_edit':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $id = $_GET['id'] ?? 0;
        $promo = $promoModel->getProductPromotionById($id);
        
        if (!$promo) {
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        // [New Condition] หากหมดอายุ ห้ามแก้ไข
        if (strtotime($promo['end_at']) < time()) {
            $_SESSION['error'] = 'โปรโมชั่นหมดอายุแล้ว ไม่สามารถแก้ไขได้';
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        $stmt = $pdo->prepare("SELECT name FROM product WHERE product_id = ?");
        $stmt->execute([$promo['product_id']]);
        $prodName = $stmt->fetchColumn();
        $promo['item_name'] = $prodName;

        $content_view = VIEW_PATH . '/staff/promotions/edit_product.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    case 'staff_promotion_product_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Promotion.php';
            $promoModel = new Promotion($pdo);
            $id = $_POST['id'];

            $picture_path = null;
            if (!empty($_FILES['promotion_picture']['name'])) {
                $target_dir = "assets/images/promotions/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES["promotion_picture"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_p_upd_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $picture_path = $target_dir . $file_name;
                move_uploaded_file($_FILES["promotion_picture"]["tmp_name"], $picture_path);
            }

            $data = [
                'discount' => $_POST['discount'],
                'start_at' => $_POST['start_at'],
                'end_at' => $_POST['end_at'],
                'visible' => $_POST['visible'],
                'picture' => $picture_path
            ];
            
            $promoModel->updateProductPromotion($id, $data);
            header('Location: index.php?action=staff_promotion_list');
        }
        break;

    case 'staff_promotion_product_toggle':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $id = $_GET['id'] ?? 0;

        // [New Condition] ตรวจสอบว่ามีโปรฯ และยังไม่หมดอายุ
        $promo = $promoModel->getProductPromotionById($id);
        if (!$promo) {
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        if (strtotime($promo['end_at']) < time()) {
            $_SESSION['error'] = 'โปรโมชั่นหมดอายุแล้ว ไม่สามารถเปิด/ปิดการมองเห็นได้';
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        $current = $_GET['status'] ?? 0;
        $newStatus = ($current == 1) ? 0 : 1;
        $promoModel->toggleProductStatus($id, $newStatus);
        header('Location: index.php?action=staff_promotion_list');
        exit;
        break;

    // ----------------------------------------------------
    // EDIT & TOGGLE COURSE PROMOTION
    // ----------------------------------------------------
    case 'staff_promotion_course_edit':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $id = $_GET['id'] ?? 0;
        $promo = $promoModel->getCoursePromotionById($id);
        
        if (!$promo) {
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        // [New Condition] หากหมดอายุ ห้ามแก้ไข
        if (strtotime($promo['end_at']) < time()) {
            $_SESSION['error'] = 'โปรโมชั่นหมดอายุแล้ว ไม่สามารถแก้ไขได้';
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        $stmt = $pdo->prepare("SELECT name FROM course WHERE course_id = ?");
        $stmt->execute([$promo['course_id']]);
        $courseName = $stmt->fetchColumn();
        $promo['item_name'] = $courseName;

        $content_view = VIEW_PATH . '/staff/promotions/edit_course.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    case 'staff_promotion_course_update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Promotion.php';
            $promoModel = new Promotion($pdo);
            $id = $_POST['id'];

            $picture_path = null;
            if (!empty($_FILES['promotion_picture']['name'])) {
                $target_dir = "assets/images/promotions/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $ext = pathinfo($_FILES["promotion_picture"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_c_upd_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $picture_path = $target_dir . $file_name;
                move_uploaded_file($_FILES["promotion_picture"]["tmp_name"], $picture_path);
            }

            $data = [
                'discount' => $_POST['discount'],
                'start_at' => $_POST['start_at'],
                'end_at' => $_POST['end_at'],
                'visible' => $_POST['visible'],
                'picture' => $picture_path
            ];
            
            $promoModel->updateCoursePromotion($id, $data);
            header('Location: index.php?action=staff_promotion_list');
        }
        break;

    case 'staff_promotion_course_toggle':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $id = $_GET['id'] ?? 0;

        // [New Condition] ตรวจสอบว่ามีโปรฯ และยังไม่หมดอายุ
        $promo = $promoModel->getCoursePromotionById($id);
        if (!$promo) {
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        if (strtotime($promo['end_at']) < time()) {
            $_SESSION['error'] = 'โปรโมชั่นหมดอายุแล้ว ไม่สามารถเปิด/ปิดการมองเห็นได้';
            header('Location: index.php?action=staff_promotion_list');
            exit;
        }

        $current = $_GET['status'] ?? 0;
        $newStatus = ($current == 1) ? 0 : 1;
        $promoModel->toggleCourseStatus($id, $newStatus);
        header('Location: index.php?action=staff_promotion_list');
        exit;
        break;

    // 4. หน้าฟอร์มเพิ่มโปรฯ หลักสูตร
    case 'staff_promotion_course_create':
        if (!isset($_SESSION['role_id'])) exit;
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $courses = $promoModel->getAllCourses();

        $content_view = VIEW_PATH . '/staff/promotions/create_course.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 5. บันทึกโปรฯ หลักสูตร
    case 'staff_promotion_course_store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Promotion.php';
            $promoModel = new Promotion($pdo);

            $picture_path = null;
            if (!empty($_FILES['promotion_picture']['name'])) {
                $target_dir = "assets/images/promotions/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                // [Modified] Generate random filename to avoid issues with special characters/spaces
                $ext = pathinfo($_FILES["promotion_picture"]["name"], PATHINFO_EXTENSION);
                $file_name = time() . '_c_' . bin2hex(random_bytes(8)) . '.' . $ext;
                
                $picture_path = $target_dir . $file_name;
                move_uploaded_file($_FILES["promotion_picture"]["tmp_name"], $picture_path);
            }

            // [แก้ไข] รองรับการเลือกหลาย Course (CheckBox)
            $course_ids = $_POST['course_id'] ?? []; 
            if (!is_array($course_ids)) {
                $course_ids = [$course_ids];
            }

            // [New] Validation check BEFORE Insert
            foreach ($course_ids as $cid) {
                 if ($promoModel->checkCourseOverlap($cid, $_POST['start_at'], $_POST['end_at'])) {
                    // ดึงชื่อหลักสูตรที่ซ้ำ
                    $cName = "ไม่ระบุ";
                    try {
                        $cName = $pdo->query("SELECT name FROM course WHERE course_id = $cid")->fetchColumn(); 
                    } catch(Exception $e){}
                    
                    $_SESSION['error'] = "ไม่สามารถบันทึกได้: พบช่วงเวลาโปรโมชั่นซ้อนทับสำหรับหลักสูตร '$cName'";
                    header('Location: index.php?action=staff_promotion_course_create');
                    exit; 
                }
            }

            foreach ($course_ids as $cid) {
                $data = [
                    'user_id' => $_SESSION['user_id'],
                    'course_id' => $cid,
                    'discount' => $_POST['discount'],
                    'start_at' => $_POST['start_at'],
                    'end_at' => $_POST['end_at'],
                    'visible' => $_POST['visible'],
                    'picture' => $picture_path
                ];
                $promoModel->createCoursePromotion($data);
            }
            
            // แจ้งเตือนสำเร็จ
            $_SESSION['success'] = "เพิ่มโปรโมชั่นหลักสูตรเรียบร้อยแล้ว";
            header('Location: index.php?action=staff_promotion_list');
        }
        break;

    // =============================================
    // ADMIN: Dashboard
    // =============================================
    case 'admin_dashboard':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            header('Location: index.php?action=login');
            exit;
        }

        // 1. นับจำนวนคน
        $staff_count = $pdo->query("SELECT COUNT(*) FROM user WHERE role_id = 2")->fetchColumn();
        $member_count = $pdo->query("SELECT COUNT(*) FROM user WHERE role_id = 3")->fetchColumn();

        // 2. คำนวณยอดขายวันนี้ (รวมทั้งสินค้า และ คอร์สที่อนุมัติแล้ว)
        $today = date('Y-m-d');

        // 2.1 ยอดสินค้าวันนี้
        $sqlSaleToday = "SELECT SUM(total_amount) FROM sale WHERE DATE(sold_at) = '$today'";
        $saleToday = $pdo->query($sqlSaleToday)->fetchColumn() ?: 0;

        // 2.2 ยอดคอร์สวันนี้ (Status = Confirmed)
        $sqlBookingToday = "SELECT SUM(p.amount) FROM booking b 
                            JOIN payment p ON b.booking_id = p.booking_id
                            WHERE b.status = 'Confirmed' AND DATE(b.booked_at) = '$today'";
        $bookingToday = $pdo->query($sqlBookingToday)->fetchColumn() ?: 0;

        $daily_sales = $saleToday + $bookingToday;

        // 3. ดึงข้อมูลกราฟรายได้ย้อนหลัง 6 เดือน (สำหรับแสดงแทนตาราง)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = date("Y-m-01 00:00:00", strtotime("-$i months"));
            $end = date("Y-m-t 23:59:59", strtotime("-$i months"));
            $label = date("M", strtotime("-$i months")); // Jan, Feb

            // ยอดสินค้าเดือนนี้
            $sTotal = $pdo->query("SELECT SUM(total_amount) FROM sale WHERE sold_at BETWEEN '$start' AND '$end'")->fetchColumn() ?: 0;
            // ยอดคอร์สเดือนนี้
            $bTotal = $pdo->query("SELECT SUM(p.amount) FROM booking b JOIN payment p ON b.booking_id = p.booking_id WHERE b.status = 'Confirmed' AND b.booked_at BETWEEN '$start' AND '$end'")->fetchColumn() ?: 0;

            $chartData[] = [
                'label' => $label,
                'total' => $sTotal + $bTotal
            ];
        }

        // เรียก View Dashboard
        $content_view = VIEW_PATH . '/admin/dashboard.php';
        require_once VIEW_PATH . '/layouts/admin_layout.php';
        exit;
        break;

    // =============================================
    // ADMIN: Manage Staff (จัดการพนักงาน)
    // =============================================
    case 'admin_manage_staff':
        // ดึงข้อมูล User ที่มี Role_id = 2 (สมมติว่าเป็นพนักงาน)
        $sql = "SELECT * FROM user WHERE role_id = 2 ORDER BY user_id DESC";
        $staff_list = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $content_view = VIEW_PATH . '/admin/staff/list.php';
        require_once VIEW_PATH . '/layouts/admin_layout.php';
        exit;
        break;

    case 'admin_staff_create':
    case 'admin_staff_edit':
        $staff = [];
        if ($action == 'admin_staff_edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->execute([$_GET['id']]);
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $content_view = VIEW_PATH . '/admin/staff/create_edit.php';
        require_once VIEW_PATH . '/layouts/admin_layout.php';
        exit;
        break;

    case 'admin_staff_save':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_POST['user_id'] ?? '';
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            // role_id 2 = Staff
            $role_id = 2;

            if ($user_id) { // UPDATE
                // ถ้ามีการกรอก Password ใหม่ ให้แก้ด้วย
                if (!empty($password)) {
                    $sql = "UPDATE user SET full_name=?, email=?, phone=?, username=?, password=? WHERE user_id=?";
                    $params = [$full_name, $email, $phone, $username, password_hash($password, PASSWORD_DEFAULT), $user_id];
                } else {
                    $sql = "UPDATE user SET full_name=?, email=?, phone=?, username=? WHERE user_id=?";
                    $params = [$full_name, $email, $phone, $username, $user_id];
                }
                $pdo->prepare($sql)->execute($params);
            } else { // INSERT
                $sql = "INSERT INTO user (role_id, full_name, email, phone, username, password, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $pdo->prepare($sql)->execute([$role_id, $full_name, $email, $phone, $username, password_hash($password, PASSWORD_DEFAULT)]);
            }
            header('Location: index.php?action=admin_manage_staff');
            exit;
        }
        break;

    // =============================================
    // เปลี่ยนจาก ลบ (Delete) เป็น ปรับสถานะ (Toggle Status)
    // =============================================
    case 'admin_staff_toggle_status':
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $user_id = $_GET['id'];
            $new_status = $_GET['status']; // รับค่า 0 (ปิด) หรือ 1 (เปิด)

            // อัปเดตสถานะ is_active
            $stmt = $pdo->prepare("UPDATE user SET is_active = ? WHERE user_id = ? AND role_id = 2");
            $stmt->execute([$new_status, $user_id]);
        }
        header('Location: index.php?action=admin_manage_staff');
        exit;
        break;

    // =============================================
    // ADMIN: Manage Member (จัดการสมาชิก)
    // =============================================
    case 'admin_manage_member':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            header('Location: index.php?action=login');
            exit;
        }
        // ดึงข้อมูล User ที่มี Role_id = 3 (สมาชิก)
        $sql = "SELECT * FROM user WHERE role_id = 3 ORDER BY user_id DESC";
        $member_list = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $content_view = VIEW_PATH . '/admin/member/list.php';
        require_once VIEW_PATH . '/layouts/admin_layout.php';
        exit;
        break;

    case 'admin_member_edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            header('Location: index.php?action=login');
            exit;
        }
        $member = [];
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ? AND role_id = 3");
            $stmt->execute([$_GET['id']]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $content_view = VIEW_PATH . '/admin/member/edit.php';
        require_once VIEW_PATH . '/layouts/admin_layout.php';
        exit;
        break;

    case 'admin_member_save':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_POST['user_id'] ?? '';
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            if ($user_id) { // UPDATE
                // ถ้ามีการกรอก Password ใหม่ ให้แก้ด้วย
                if (!empty($password)) {
                    $sql = "UPDATE user SET full_name=?, email=?, phone=?, username=?, password=? WHERE user_id=? AND role_id=3";
                    $params = [$full_name, $email, $phone, $username, password_hash($password, PASSWORD_DEFAULT), $user_id];
                } else {
                    $sql = "UPDATE user SET full_name=?, email=?, phone=?, username=? WHERE user_id=? AND role_id=3";
                    $params = [$full_name, $email, $phone, $username, $user_id];
                }
                $pdo->prepare($sql)->execute($params);
            }
            header('Location: index.php?action=admin_manage_member');
            exit;
        }
        break;

    case 'admin_member_toggle_status':
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $user_id = $_GET['id'];
            $new_status = $_GET['status']; // รับค่า 0 (ปิด) หรือ 1 (เปิด)

            // อัปเดตสถานะ is_active
            $stmt = $pdo->prepare("UPDATE user SET is_active = ? WHERE user_id = ? AND role_id = 3");
            $stmt->execute([$new_status, $user_id]);
        }
        header('Location: index.php?action=admin_manage_member');
        exit;
        break;

    // =============================================
    // STAFF: POS (Point of Sale) & ตะกร้าสินค้า
    // =============================================

    // 1. หน้าจอขายสินค้า (POS)
    case 'staff_pos':
        if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
            header('Location: index.php?action=login');
            exit;
        }
        require_once APP_PATH . '/models/Product.php';
        $productModel = new Product($pdo);

        // ดึงสินค้าทั้งหมด (พร้อมส่วนลด)
        $products = $productModel->getActiveProductsWithPromo();

        // เตรียมข้อมูลตะกร้า
        $cart = $_SESSION['pos_cart'] ?? [];
        
        // คำนวณยอดรวม
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['line_total'];
        }

        $page_header = "ระบบขายหน้าร้าน (POS)";
        $content_view = VIEW_PATH . '/staff/pos/view.php';
        require_once VIEW_PATH . '/layouts/staff_layout.php';
        exit;
        break;

    // 2. เพิ่มสินค้าลงตะกร้า (AJAX/POST)
    case 'staff_pos_add':
        while (ob_get_level()) ob_end_clean(); // Clear any previous output
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id']);
            $qty = intval($_POST['qty'] ?? 1);

            require_once APP_PATH . '/models/Product.php';
            $productModel = new Product($pdo);
            $product = $productModel->getProductById($product_id);

            if ($product) {
                // เช็คจำนวนสินค้าในตะกร้าปัจจุบัน
                $current_qty_in_cart = isset($_SESSION['pos_cart'][$product_id]) ? $_SESSION['pos_cart'][$product_id]['qty'] : 0;
                $total_requested_qty = $current_qty_in_cart + $qty;

                if ($product['stock'] >= $total_requested_qty) {
                    // คำนวณราคาและส่วนลด
                    $promo = $productModel->getActivePromotion($product_id);
                    $unit_price = $product['price'];
                    $discount_percent = 0;
                    if ($promo) {
                        $discount_percent = floatval($promo['discount']);
                    }

                    $discount_per_unit = $unit_price * ($discount_percent / 100);
                    $final_unit_price = $unit_price - $discount_per_unit;

                    // เพิ่ม/รวมลงใน Session Cart
                    if (isset($_SESSION['pos_cart'][$product_id])) {
                        $_SESSION['pos_cart'][$product_id]['qty'] += $qty;
                    } else {
                        $_SESSION['pos_cart'][$product_id] = [
                            'product_id' => $product['product_id'],
                            'name'       => $product['name'],
                            'qty'        => $qty,
                            'unit_price' => $unit_price,
                            'discount_percent' => $discount_percent,
                            'discount_per_unit' => $discount_per_unit,
                            'final_unit_price' => $final_unit_price
                        ];
                    }

                    // อัปเดต Line Total ทุกครั้ง
                    $_SESSION['pos_cart'][$product_id]['line_total'] = 
                        $_SESSION['pos_cart'][$product_id]['final_unit_price'] * $_SESSION['pos_cart'][$product_id]['qty'];

                    echo json_encode(['status' => 'success', 'msg' => 'เพิ่มสินค้าเรียบร้อย']);
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'msg' => 'ไม่สามารถเพิ่มสินค้าได้ เนื่องจากจำนวนในสต็อกไม่เพียงพอ']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'msg' => 'ไม่พบสินค้า']);
            }
        }
        exit;
        break;

    // 3. ลบสินค้าออกจากตะกร้า
    case 'staff_pos_remove':
        $idx = $_GET['id'] ?? 0;
        if (isset($_SESSION['pos_cart'][$idx])) {
            unset($_SESSION['pos_cart'][$idx]);
        }
        // รองรับ AJAX
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }
        header('Location: index.php?action=staff_pos');
        exit;
        break;

    // 8. ลดจำนวนสินค้า
    case 'staff_pos_decrease':
        $product_id = $_GET['id'] ?? 0;
        if (isset($_SESSION['pos_cart'][$product_id])) {
            if ($_SESSION['pos_cart'][$product_id]['qty'] > 1) {
                $_SESSION['pos_cart'][$product_id]['qty']--;
                // Recalculate line total
                $_SESSION['pos_cart'][$product_id]['line_total'] = 
                    $_SESSION['pos_cart'][$product_id]['final_unit_price'] * $_SESSION['pos_cart'][$product_id]['qty'];
            } else {
                // ถ้าเหลือ 1 ชิ้น กดลด -> ลบออกจากตะกร้า
                unset($_SESSION['pos_cart'][$product_id]);
            }
        }
        
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }
        header('Location: index.php?action=staff_pos');
        exit;
        break;

    // 4. เคลียร์ตะกร้า
    case 'staff_pos_clear':
        unset($_SESSION['pos_cart']);
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            while (ob_get_level()) ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }
        header('Location: index.php?action=staff_pos');
        exit;
        break;

    // 7. (AJAX) ดึงข้อมูลตะกร้า HTML
    case 'staff_pos_get_cart':
        while (ob_get_level()) ob_end_clean();
        $cart = $_SESSION['pos_cart'] ?? [];
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['line_total'];
        }
        
        // ดึงข้อมูลสต็อกล่าสุดเพื่อส่งกลับไปอัปเดตหน้าจอ
        require_once APP_PATH . '/models/Product.php';
        $productModel = new Product($pdo);
        $products = $productModel->getActiveProductsWithPromo();
        $stockData = [];
        foreach ($products as $p) {
            $current_qty_in_cart = isset($cart[$p['product_id']]) ? $cart[$p['product_id']]['qty'] : 0;
            $remaining_stock = $p['stock'] - $current_qty_in_cart;
            $stockData[$p['product_id']] = max(0, $remaining_stock);
        }

        // ส่งกลับเป็น JSON ที่มีทั้ง HTML และข้อมูลสต็อก
        $response = [
            'stockData' => $stockData,
            'html' => ''
        ];

        ob_start();
        ?>
        <div class="cart-header" style="padding: 15px; border-bottom: 1px solid #eee; background: #f8f9fa;">
            <h3 style="margin: 0; font-size: 18px;"><i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า</h3>
            <a href="javascript:void(0)" onclick="clearCart()" style="font-size: 12px; color: #dc3545; text-decoration: none; float: right;">ล้างรายการ</a>
        </div>

        <div class="cart-items" style="flex: 1; overflow-y: auto; padding: 0;">
            <?php if (empty($cart)): ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-shopping-basket" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>ยังไม่มีสินค้าในตะกร้า</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($cart as $id => $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <div style="font-weight: 600; font-size: 14px;"><?= htmlspecialchars($item['name']) ?></div>
                                <div style="font-size: 12px; color: #666;">
                                    <?= number_format($item['final_unit_price'], 2) ?> x 
                                    <i class="fas fa-minus-circle" onclick="decreaseQty(<?= $id ?>)" style="cursor: pointer; color: #f39c12; margin-right: 2px;"></i>
                                    <?= $item['qty'] ?>
                                    <?php if($item['discount_percent'] > 0): ?>
                                        <span style="color: #e74c3c; font-size: 10px;">(-<?= $item['discount_percent'] ?>%)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right; padding: 10px;">
                                <div style="font-weight: bold;"><?= number_format($item['line_total'], 2) ?></div>
                                <a href="javascript:void(0)" onclick="removeFromCart(<?= $id ?>)" style="color: #dc3545; font-size: 12px;"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <div class="cart-footer" style="padding: 20px; border-top: 1px solid #eee; background: #f8f9fa;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 18px; font-weight: bold;">
                <span>ยอดรวมสุทธิ</span>
                <span style="color: #2ecc71;">฿<?= number_format($totalAmount, 2) ?></span>
            </div>
            
            <a href="index.php?action=staff_pos_checkout" 
               class="btn-checkout <?= empty($cart) ? 'disabled' : '' ?>"
               onclick="return confirm('ยืนยันการขาย?')"
               style="display: block; width: 100%; padding: 12px; background: #2ecc71; color: white; text-align: center; border-radius: 5px; text-decoration: none; font-weight: bold;"
            >
                <i class="fas fa-money-bill-wave"></i> ชำระเงิน
            </a>
        </div>
        <?php
        $response['html'] = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
        break;

    // 5. ชำระเงิน (Checkout)
    case 'staff_pos_checkout':
        if (empty($_SESSION['pos_cart'])) {
            header('Location: index.php?action=staff_pos');
            exit;
        }

        require_once APP_PATH . '/models/Sale.php';
        $saleModel = new Sale($pdo);
        
        $sale_id = $saleModel->createSale($_SESSION['user_id'], $_SESSION['pos_cart']);

        if ($sale_id) {
            // เคลียร์ตะกร้า
            unset($_SESSION['pos_cart']);
            session_write_close(); // Ensure session is saved before redirect
            // ไปหน้าใบเสร็จ
            header("Location: index.php?action=staff_pos_receipt&id=$sale_id");
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกการขาย'); window.history.back();</script>";
        }
        exit;
        break;

    // 6. ดูใบเสร็จ (Print Preview)
    case 'staff_pos_receipt':
        $sale_id = $_GET['id'] ?? 0;
        require_once APP_PATH . '/models/Sale.php';
        $saleModel = new Sale($pdo);

        // ดึงข้อมูลบิลและรายการสินค้า
        $stmt = $pdo->prepare("SELECT s.*, u.full_name as staff_name FROM sale s LEFT JOIN user u ON s.recorded_by = u.user_id WHERE s.sale_id = ?");
        $stmt->execute([$sale_id]);
        $saleData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$saleData) die("Receipt not found");

        $saleItems = $saleModel->getSaleItems($sale_id);

        require_once VIEW_PATH . '/staff/pos/receipt.php';
        exit;
        break;

    // 7. ดูใบเสร็จคอร์สเรียน
    case 'staff_course_receipt':
        $booking_id = $_GET['id'] ?? 0;
        
        $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.receipt_number, p.receipt_date,
                       u.full_name as customer_name, c.name as course_name, c.price as course_price,
                       staff.full_name as staff_name
                FROM payment p
                JOIN booking b ON p.booking_id = b.booking_id
                JOIN user u ON b.user_id = u.user_id
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                LEFT JOIN user staff ON p.verified_by = staff.user_id
                WHERE p.booking_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        $receiptData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receiptData) die("Receipt not found");

        require_once VIEW_PATH . '/staff/pos/course_receipt.php';
        exit;
        break;

    // =============================================
    // ADMIN: Real Reports (รายงานจาก DB จริง)
    // =============================================
    case 'admin_reports':
    case 'admin_report_pdf':

        $filter = $_GET['filter'] ?? 'monthly';
        $selected_date = $_GET['date'] ?? date('Y-m-d');
        $selected_month = $_GET['month'] ?? date('Y-m');
        $selected_year = $_GET['year'] ?? date('Y');
        $start_date_custom = $_GET['start_date'] ?? date('Y-m-01');
        $end_date_custom = $_GET['end_date'] ?? date('Y-m-t');

        // กำหนดตัวแปรตาม Filter
        $sqlDateFormat = "";
        $startDate = "";
        $endDate = "";
        $labels = [];

        if ($filter == 'daily') {
            $startDate = $selected_date;
            $endDate = $selected_date;
            $sqlDateFormat = "%Y-%m-%d %H:00"; // Group by hour for a single day
            for ($i = 0; $i <= 23; $i++) {
                $h = str_pad($i, 2, '0', STR_PAD_LEFT);
                $labels["$selected_date $h:00"] = "$h:00";
            }
        } elseif ($filter == 'yearly') {
            $startDate = "$selected_year-01-01";
            $endDate = "$selected_year-12-31";
            $sqlDateFormat = "%Y-%m";
            
            // ถ้าเป็นปีปัจจุบัน ให้แสดงถึงแค่เดือนปัจจุบัน
            if ($selected_year == date('Y')) {
                $monthsInYear = date('n');
                $endDate = date('Y-m-t');
            } else {
                $monthsInYear = 12;
            }
            
            for ($i = 1; $i <= $monthsInYear; $i++) {
                $m = str_pad($i, 2, '0', STR_PAD_LEFT);
                $labels["$selected_year-$m"] = date('M', strtotime("$selected_year-$m-01"));
            }
        } elseif ($filter == 'custom') {
            $startDate = $start_date_custom;
            $endDate = $end_date_custom;
            $sqlDateFormat = "%Y-%m-%d";
            
            $begin = new DateTime($startDate);
            $end = new DateTime($endDate);
            $end = $end->modify('+1 day'); // Include end date
            
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
            
            foreach ($period as $dt) {
                $labels[$dt->format("Y-m-d")] = $dt->format("d/m");
            }
        } else { // monthly
            $startDate = "$selected_month-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            $sqlDateFormat = "%Y-%m-%d";
            
            // ถ้าเป็นเดือนปัจจุบัน ให้แสดงถึงแค่วันนี้
            if ($selected_month == date('Y-m')) {
                $daysInMonth = date('d');
                $endDate = date('Y-m-d');
            } else {
                $daysInMonth = date('t', strtotime($startDate));
            }
            
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $d = str_pad($i, 2, '0', STR_PAD_LEFT);
                $labels["$selected_month-$d"] = "$d";
            }
        }

        // =============================================
        // 1. ดึงข้อมูลรายได้รวม (Total Revenue)
        // =============================================

        // 1.1 สินค้า (จาก Sale)
        $sqlSale = "SELECT DATE_FORMAT(sold_at, '$sqlDateFormat') as t_date, SUM(total_amount) as total 
                    FROM sale 
                    WHERE sold_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                    GROUP BY t_date";
        $salesData = $pdo->query($sqlSale)->fetchAll(PDO::FETCH_KEY_PAIR);

        // [แก้ไขจุดที่ 1] 1.2 คอร์ส (เปลี่ยนจาก Payment เป็น Booking ที่ Confirmed)
        // ดึงราคาจาก Payment โดยตรง เพื่อให้ได้ราคาสุทธิหลังหักส่วนลด
        $sqlPay = "SELECT DATE_FORMAT(b.booked_at, '$sqlDateFormat') as t_date, SUM(p.amount) as total 
                   FROM booking b
                   JOIN payment p ON b.booking_id = p.booking_id
                   WHERE b.status = 'Confirmed' 
                   AND b.booked_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                   GROUP BY t_date";
        $paymentsData = $pdo->query($sqlPay)->fetchAll(PDO::FETCH_KEY_PAIR);

        // รวมข้อมูล
        $reportData = [];
        $grandTotal = 0;
        $totalCourse = 0;
        $totalProduct = 0;

        foreach ($labels as $key => $labelName) {
            $s = $salesData[$key] ?? 0;
            $p = $paymentsData[$key] ?? 0;
            $sum = $s + $p;

            $reportData[$key] = [
                'label' => $labelName,
                'course' => $p,
                'product' => $s,
                'total' => $sum
            ];

            $grandTotal += $sum;
            $totalCourse += $p;
            $totalProduct += $s;
        }

        // =============================================
        // 2. ข้อมูลสำหรับกราฟชุดใหม่ (Pie Chart & Bar Chart)
        // =============================================

        $revenueByType = [];

        // [แก้ไขจุดที่ 2] 2.1 ประเภทคอร์ส (จาก Booking ที่ Confirmed)
        $sqlTypeC = "SELECT c.course_type, SUM(p.amount) as total
                     FROM booking b
                     JOIN payment p ON b.booking_id = p.booking_id
                     JOIN course_schedule cs ON b.schedule_id = cs.schedule_id
                     JOIN course c ON cs.course_id = c.course_id
                     WHERE b.status = 'Confirmed'
                     AND b.booked_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                     GROUP BY c.course_type";
        $typeCData = $pdo->query($sqlTypeC)->fetchAll(PDO::FETCH_KEY_PAIR);

        // 2.2 ประเภทสินค้า
        $sqlTypeP = "SELECT pr.product_type, SUM(si.line_total) as total
                     FROM sale_item si
                     JOIN sale s ON si.sale_id = s.sale_id
                     JOIN product pr ON si.product_id = pr.product_id
                     WHERE s.sold_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                     GROUP BY pr.product_type";
        $typePData = $pdo->query($sqlTypeP)->fetchAll(PDO::FETCH_KEY_PAIR);

        // รวมประเภท
        foreach ($typeCData as $type => $amount) $revenueByType[$type] = ($revenueByType[$type] ?? 0) + $amount;
        foreach ($typePData as $type => $amount) $revenueByType[$type] = ($revenueByType[$type] ?? 0) + $amount;
        arsort($revenueByType);


        
        $sqlRevCourse = "SELECT c.name, SUM(p.amount) as total
                         FROM booking b
                         JOIN payment p ON b.booking_id = p.booking_id
                         JOIN course_schedule cs ON b.schedule_id = cs.schedule_id
                         JOIN course c ON cs.course_id = c.course_id
                         WHERE b.status = 'Confirmed'
                         AND b.booked_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                         GROUP BY c.course_id ORDER BY total DESC LIMIT 10";
        $revByCourse = $pdo->query($sqlRevCourse)->fetchAll(PDO::FETCH_ASSOC);

       
        $sqlRevProduct = "SELECT pr.name, SUM(si.line_total) as total
                          FROM sale_item si
                          JOIN sale s ON si.sale_id = s.sale_id
                          JOIN product pr ON si.product_id = pr.product_id
                          WHERE s.sold_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                          GROUP BY pr.product_id ORDER BY total DESC LIMIT 10";
        $revByProduct = $pdo->query($sqlRevProduct)->fetchAll(PDO::FETCH_ASSOC);

        $sqlTopC = "SELECT c.name, COUNT(b.booking_id) as total 
                    FROM booking b
                    JOIN course_schedule cs ON b.schedule_id = cs.schedule_id
                    JOIN course c ON cs.course_id = c.course_id
                    WHERE b.status = 'Confirmed' AND b.booked_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                    GROUP BY c.course_id ORDER BY total DESC LIMIT 5";
        $topCourses = $pdo->query($sqlTopC)->fetchAll(PDO::FETCH_ASSOC);

        $sqlTopP = "SELECT p.name, SUM(si.qty) as total 
                    FROM sale_item si
                    JOIN sale s ON si.sale_id = s.sale_id
                    JOIN product p ON si.product_id = p.product_id
                    WHERE s.sold_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
                    GROUP BY p.product_id ORDER BY total DESC LIMIT 5";
        $topProducts = $pdo->query($sqlTopP)->fetchAll(PDO::FETCH_ASSOC);

        
        $pctCourse = ($grandTotal > 0) ? ($totalCourse / $grandTotal) * 100 : 0;
        $pctProduct = ($grandTotal > 0) ? ($totalProduct / $grandTotal) * 100 : 0;

        if ($action == 'admin_report_pdf') {
            include VIEW_PATH . '/admin/reports/export_pdf.php';
        } else {
            $content_view = VIEW_PATH . '/admin/reports/income_overview.php';
            require_once VIEW_PATH . '/layouts/admin_layout.php';
        }
        exit;
        break;



    default:
    case 'home':
        // ดึงโปรโมชั่นสำหรับแสดงหน้าโฮม
        require_once APP_PATH . '/models/Promotion.php';
        $promoModel = new Promotion($pdo);
        $homepagePromotions = $promoModel->getActivePromotionsForHomepage();
       
        $title = "Bangkok Spa Academy - สถาบันวิชาชีพสปา กรุงเทพ";
        include VIEW_PATH . '/guest/home.php';
        break;
}


$content = ob_get_clean();

include VIEW_PATH . '/layouts/main_layout.php';
exit;

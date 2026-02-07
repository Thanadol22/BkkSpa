<?php

class Booking
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * ดึงข้อมูลการจองหลัก
     */
    public function getBookingById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM booking WHERE booking_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * อัปเดตสถานะการจอง (ใช้โดยลูกค้าในการยกเลิก)
     */
    public function updateBookingStatus($booking_id, $new_status)
    {
        $confirmed_at_sql = "";
        $params = [$new_status];

        // ถ้าสถานะเป็น 'Confirmed' ให้บันทึกเวลาที่อนุมัติ
        if ($new_status === 'Confirmed') {
            $confirmed_at_sql = ", confirmed_at = NOW()";
        }

        $sql = "UPDATE booking SET status = ?" . $confirmed_at_sql . " WHERE booking_id = ?";
        $params[] = $booking_id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllBookingsWithDetails()
    {
        // SQL ต้องดึง 'price' และ 'status' ออกมาด้วย เพื่อให้หน้าเว็บแสดงผลได้
        $sql = "SELECT 
                    b.booking_id,
                    b.status,             /* จำเป็นสำหรับดีไซน์ใหม่ */
                    b.booked_at,
                    b.confirmed_at,          /* <--- ต้องเพิ่มบรรทัดนี้ ไม่งั้นเช็ค 3 วันไม่ได้ */
                    b.refund_bank_name,      /* <--- เพิ่มเพื่อใช้ใน Modal */
                    b.refund_bank_account,   /* <--- เพิ่มเพื่อใช้ใน Modal */
                    b.refund_slip,
                    
                    u.full_name,
                    u.phone,
                    c.name AS course_name,
                    c.price,              /* จำเป็นสำหรับดีไซน์ใหม่ */
                    s.start_at
                FROM booking b
                JOIN user u ON b.user_id = u.user_id
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                ORDER BY b.booking_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ดึงรายละเอียดการจองครบถ้วนสำหรับหน้า Detail
     */
    // ไฟล์: app/models/Booking.php

    public function getBookingFullDetails($booking_id)
    {
        $sql = "SELECT 
                    /* 1. ข้อมูลการจอง */
                    b.booking_id,
                    b.status,
                    b.booked_at,
                    
                    /* 2. ข้อมูลลูกค้า */
                    u.user_id,
                    u.full_name,
                    u.phone,
                    u.email, 
                    
                    /* 3. ข้อมูลคอร์ส */
                    c.name AS course_name,
                    c.price,
                    c.course_picture,
                    s.start_at,
                    s.end_at,
                    
                    /* 4. ข้อมูลการชำระเงิน */
                    p.payment_id,
                    p.slip_url,
                    p.amount AS paid_amount,
                    p.status AS payment_status
                  
                    
                FROM booking b
                JOIN user u ON b.user_id = u.user_id
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                LEFT JOIN payment p ON b.booking_id = p.booking_id
                WHERE b.booking_id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $booking_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // ดึงสลิปหลายรูปจากตาราง booking_slip (ถ้ามี)
            $stmtS = $this->db->prepare("SELECT slip_url FROM booking_slip WHERE booking_id = ?");
            $stmtS->execute([$booking_id]);
            $slips = $stmtS->fetchAll(PDO::FETCH_COLUMN);
            $row['slips'] = $slips ?: [];
        }

        return $row;
    }
    /**
     * อนุมัติหรือปฏิเสธการชำระเงินและอัปเดตสถานะการจอง
     * @param int $bookingId
     * @param string $status 'Confirmed' หรือ 'Rejected'
     * @return bool
     */
    // ไฟล์: app/models/Booking.php

    public function verifyPaymentAndUpdateBooking($booking_id, $status, $reject_note = null)
    {
        try {
            $this->db->beginTransaction();
            // [แก้ไขจุดสำคัญ] สร้าง SQL แบบ Dynamic
            // ถ้าสถานะเป็น Confirmed ต้องอัปเดตเวลา confirmed_at ด้วย ไม่งั้นปุ่มยกเลิก 3 วันจะไม่ทำงาน
            $confirmed_sql = "";
            if ($status === 'Confirmed') {
                $confirmed_sql = ", confirmed_at = NOW() ";
            }

            // 1. อัปเดตสถานะ booking + reject_note + confirmed_at (ถ้ามี)
            $sqlBook = "UPDATE booking SET status = :status, reject_note = :note " . $confirmed_sql . " WHERE booking_id = :id";
            $stmtBook = $this->db->prepare($sqlBook);
            $stmtBook->execute([
                'status' => $status,
                'note' => $reject_note,
                'id' => $booking_id
            ]);

            // 2. อัปเดตสถานะ payment
            $sqlPay = "UPDATE payment SET status = :status WHERE booking_id = :id";
            $stmtPay = $this->db->prepare($sqlPay);
            $stmtPay->execute(['status' => $status, 'id' => $booking_id]);

            // 3. คืนที่นั่ง (Capacity) กรณีปฏิเสธ
            if ($status == 'Rejected' || $status == 'Cancelled') {
                $stmtSch = $this->db->prepare("SELECT schedule_id FROM booking WHERE booking_id = ?");
                $stmtSch->execute([$booking_id]);
                $schedule = $stmtSch->fetch(PDO::FETCH_ASSOC);

                if ($schedule) {
                    $this->db->prepare("UPDATE course_schedule SET capacity = capacity + 1 WHERE schedule_id = ?")
                        ->execute([$schedule['schedule_id']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function requestRefund($booking_id, $bank_name, $bank_account)
    {
        try {
            $this->db->beginTransaction();

            // 1. อัปเดตสถานะเป็น RefundPending และบันทึกข้อมูลธนาคาร
            $sql = "UPDATE booking 
                    SET status = 'RefundPending', 
                        refund_bank_name = :b_name, 
                        refund_bank_account = :b_acc,
                        cancelled_at = NOW()
                    WHERE booking_id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'b_name' => $bank_name,
                'b_acc' => $bank_account,
                'id' => $booking_id
            ]);

            // 2. คืนที่นั่ง (Capacity) ทันที
            $this->restoreCapacity($booking_id);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * [ใหม่] พนักงานบันทึกการโอนเงินคืน (อัปโหลดสลิป)
     */
    public function processStaffRefund($booking_id, $slip_path)
    {
        $sql = "UPDATE booking 
                SET status = 'Refunded', 
                    refund_slip = :slip, 
                    refund_date = NOW() 
                WHERE booking_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'slip' => $slip_path,
            'id' => $booking_id
        ]);
    }

    // ฟังก์ชันช่วยคืนที่นั่ง (Private)
    private function restoreCapacity($booking_id)
    {
        $stmtSch = $this->db->prepare("SELECT schedule_id FROM booking WHERE booking_id = ?");
        $stmtSch->execute([$booking_id]);
        $schedule = $stmtSch->fetch(PDO::FETCH_ASSOC);

        if ($schedule) {
            $this->db->prepare("UPDATE course_schedule SET capacity = capacity + 1 WHERE schedule_id = ?")
                ->execute([$schedule['schedule_id']]);
        }
    }
    // ... ใน Class Booking ...

    /**
     * สร้างเลขที่ใบเสร็จอัตโนมัติ (Format: RCP-YYYYMM-XXXX)
     */
    public function generateReceiptNumber()
    {
        $prefix = "RCP-" . date('Ym') . "-";

        // หาเลขที่สุดท้ายของเดือนนี้
        $sql = "SELECT receipt_number FROM payment 
                WHERE receipt_number LIKE ? 
                ORDER BY receipt_number DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            // ตัดเอาเฉพาะตัวเลขหลังสุดมา +1
            $num = (int)substr($last, -4);
            $newNum = $num + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * บันทึกเลขใบเสร็จลงฐานข้อมูล
     */
    public function createReceipt($booking_id)
    {
        // 1. สร้างเลข
        $rcpNum = $this->generateReceiptNumber();

        // 2. อัปเดตตาราง payment
        // (ต้องแน่ใจว่า payment record ถูกสร้างไว้แล้วตอนแนบสลิป)
        $sql = "UPDATE payment SET receipt_number = ?, receipt_date = NOW() 
                WHERE booking_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rcpNum, $booking_id]);

        return $rcpNum;
    }
    // 1. แก้ไขฟังก์ชัน getMyBookings ให้ดึงข้อมูลรีวิวมาเช็ค
    public function getMyBookings($user_id)
    {
        die("Model Loaded - ฉันแก้ถูกไฟล์แล้ว");
        $sql = "SELECT 
                    b.booking_id, b.status, b.booked_at, b.confirmed_at,
                    b.refund_bank_name, b.refund_bank_account, b.refund_slip,
                    u.full_name, 
                    
                    /* [จุดสำคัญ] ดึงจาก s.course_id และตั้งชื่อใหม่เป็น master_course_id */
                    s.course_id AS master_course_id,
                    
                    c.name AS course_name, c.price, c.course_picture,
                    s.start_at, s.end_at,
                    r.review_c_id, r.rating, r.comment 
                FROM booking b
                JOIN user u ON b.user_id = u.user_id
                JOIN course_schedule s ON b.schedule_id = s.schedule_id
                JOIN course c ON s.course_id = c.course_id
                
                /* JOIN ตารางรีวิว (ชื่อตารางต้องตรงกับฐานข้อมูลของคุณ) */
                LEFT JOIN review_course r ON b.booking_id = r.booking_id 
                
                WHERE b.user_id = :uid
                ORDER BY b.booking_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. เพิ่มฟังก์ชันบันทึกรีวิว
    public function submitReview($booking_id, $user_id, $course_id, $rating, $comment, $imagePath)
    {
        // Insert ลงคอลัมน์ตามตารางของคุณ
        $sql = "INSERT INTO review_course (booking_id, user_id, course_id, rating, comment, review_image, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$booking_id, $user_id, $course_id, $rating, $comment, $imagePath]);
    }
    // ดึงรีวิวล่าสุดมาแสดงหน้า Home (จำกัด 6 รายการ)
    public function getLatestReviews($limit = 6)
    {
        $sql = "SELECT r.*, u.full_name, c.name AS course_name 
                FROM course_review r 
                JOIN user u ON r.user_id = u.user_id 
                JOIN course c ON r.course_id = c.course_id 
                ORDER BY r.created_at DESC 
                LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // คำนวณคะแนนเฉลี่ยรวม
    public function getRatingStats()
    {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM course_review";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

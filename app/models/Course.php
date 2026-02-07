<?php

class Course
{
    private $db; // PDO Connection object

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function updateScheduleCapacity($schedule_id, $delta)
    {
        if (empty($schedule_id)) {
            error_log("Error: schedule_id is empty in updateScheduleCapacity.");
            return false;
        }

        // คำสั่ง SQL: ใช้ ? แทน :name เพื่อให้ง่ายต่อการ Execute ด้วย Array (วิธีเดิมก็ใช้ได้, แต่วิธีนี้ช่วย Debug)
        $sql = "UPDATE course_schedule 
                SET capacity = capacity + ?
                WHERE schedule_id = ? 
                AND capacity + ? >= 0";

        try {
            $stmt = $this->db->prepare($sql);

            // [การแก้ไข] ใช้ Execute ด้วย Array แทน BindParam
            $success = $stmt->execute([
                $delta,
                $schedule_id,
                $delta
            ]);

            // ตรวจสอบจำนวนแถวที่ถูกอัปเดต
            if ($success && $stmt->rowCount() > 0) {
                return true;
            } else {
                // ถ้า execute เป็น true แต่ rowCount เป็น 0 แสดงว่าเงื่อนไข WHERE ไม่ผ่าน
                // ถ้า execute เป็น false แสดงว่า SQL มีปัญหา
                error_log("Capacity Update Failed/No Rows Updated. Schedule ID: " . $schedule_id . " Delta: " . $delta);
                // [Debugging] ถ้าต้องการดู SQL Error ที่แท้จริง: 
                // error_log(print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database Error in updateScheduleCapacity: " . $e->getMessage());
            return false;
        }
    }
    // ----------------------------------------------------
    // เพิ่มฟังก์ชันนี้เข้าไปเพื่อให้หายตัวแดง
    // ----------------------------------------------------
    public function getAllCourses()
    {
        // ดึงข้อมูลคอร์สทั้งหมด เรียงตามชื่อ
        $sql = "SELECT * FROM course ORDER BY course_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // (อาจมีฟังก์ชันอื่นๆ ต่อจากนี้ เช่น getCourseById)
    public function getCourseById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE course_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // [เพิ่มใน Class Course]

    // --- เพิ่มต่อท้ายใน Class Course ---

    public function getActiveCoursesForAttendance() {
        $today = date('Y-m-d');
        
        // แก้ไข SQL: ใช้ LEFT JOIN แทน Subquery เพื่อแก้ปัญหา Unknown column
        $sql = "SELECT 
                    c.course_id, 
                    c.name AS course_name, 
                    u.full_name, 
                    u.user_id,
                    b.booking_id,
                    CASE WHEN a.attendance_id IS NOT NULL THEN 1 ELSE 0 END as is_checked
                FROM course_schedule s
                JOIN course c ON s.course_id = c.course_id
                JOIN booking b ON s.schedule_id = b.schedule_id
                JOIN user u ON b.user_id = u.user_id
                /* ใช้ LEFT JOIN แทน เพื่อดึงข้อมูลการเช็กชื่อของวันนี้ */
                LEFT JOIN attendance a ON b.booking_id = a.booking_id AND a.check_date = :today
                WHERE c.is_active = 1 
                  AND b.status = 'Confirmed'
                  AND :today BETWEEN DATE(s.start_at) AND DATE(s.end_at)
                ORDER BY c.course_id, u.full_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['today' => $today]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // จัดกลุ่มข้อมูล (ส่วนนี้เหมือนเดิม)
        $courses = [];
        foreach ($rows as $row) {
            $courses[$row['course_id']]['course_name'] = $row['course_name'];
            $courses[$row['course_id']]['students'][] = $row;
        }
        return $courses;
    }
    // 2. บันทึกการเช็กชื่อลงฐานข้อมูล
    public function saveAttendance($booking_ids, $date) {
        // เตรียม SQL สำหรับบันทึก (ถ้ามีแล้วให้อัปเดต)
        $sql = "INSERT INTO attendance (booking_id, check_date, status) 
                VALUES (?, ?, 'Present') 
                ON DUPLICATE KEY UPDATE status = 'Present'";
        $stmt = $this->db->prepare($sql);

        foreach ($booking_ids as $bid) {
            $stmt->execute([$bid, $date]);
        }
    }
}

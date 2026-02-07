<?php
class Attendance {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
        $this->ensureAttendanceDateColumn();
    }

    // ตรวจสอบและเพิ่มคอลัมน์ attendance_date ถ้ายังไม่มี
    private function ensureAttendanceDateColumn() {
        try {
            // ตรวจสอบคอลัมน์
            $sql = "SHOW COLUMNS FROM attendance LIKE 'attendance_date'";
            $stmt = $this->db->query($sql);
            if ($stmt->rowCount() == 0) {
                // เพิ่มคอลัมน์ attendance_date
                $this->db->exec("ALTER TABLE attendance ADD COLUMN attendance_date DATE DEFAULT NULL AFTER schedule_id");
                // เพื่อความเข้ากันได้กับข้อมูลเก่า อาจจะต้อง update ข้อมูลเก่า (optional)
                // $this->db->exec("UPDATE attendance SET attendance_date = DATE(checked_at) WHERE attendance_date IS NULL");
            }
        } catch (Exception $e) {
            // เงียบไว้ หรือ log error
            error_log("Migration Error: " . $e->getMessage());
        }
    }

    // ดึงตารางเรียนที่มีการเรียนการสอนในวันที่ระบุ (ครอบคลุมช่วงวันที่เริ่ม-จบ)
    public function getSchedulesByDate($date) {
        $sql = "SELECT cs.schedule_id, c.name as course_name, cs.start_at, cs.end_at
                FROM course_schedule cs
                JOIN course c ON cs.course_id = c.course_id
                WHERE :date BETWEEN DATE(cs.start_at) AND DATE(cs.end_at)
                ORDER BY cs.start_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงรายชื่อนักเรียนในคลาส พร้อมสถานะการมาเรียนในวันที่ระบุ
    public function getStudentsInSchedule($schedule_id, $date) {
        $sql = "SELECT 
                    u.user_id, 
                    u.full_name, 
                    a.status as attendance_status -- 1=มา, 0/Null=ขาด
                FROM booking b
                JOIN user u ON b.user_id = u.user_id
                LEFT JOIN attendance a ON b.schedule_id = a.schedule_id 
                                      AND b.user_id = a.user_id
                                      AND a.attendance_date = :date
                WHERE b.schedule_id = :schedule_id 
                  AND b.status = 'Confirmed'
                ORDER BY u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['schedule_id' => $schedule_id, 'date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // บันทึก/อัปเดต การเช็กชื่อ (ระบุวันที่)
    public function saveAttendance($schedule_id, $present_user_ids, $staff_id, $attendance_date) {
        try {
            $this->db->beginTransaction();
            
            // 1. ดึงนักเรียนทั้งหมดในคลาสเพื่อเคลียร์ค่า
            $students = $this->getStudentsInSchedule($schedule_id, $attendance_date);

            foreach ($students as $stu) {
                $uid = $stu['user_id'];
                $is_present = in_array($uid, $present_user_ids) ? 1 : 0; 

                // เช็คว่ามี Record เดิมของวันที่ระบุไหม
                $checkSql = "SELECT attendance_id FROM attendance WHERE schedule_id = ? AND user_id = ? AND attendance_date = ?";
                $stmtCheck = $this->db->prepare($checkSql);
                $stmtCheck->execute([$schedule_id, $uid, $attendance_date]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $updateSql = "UPDATE attendance SET status = ?, checked_at = NOW(), checked_by = ? WHERE attendance_id = ?";
                    $this->db->prepare($updateSql)->execute([$is_present, $staff_id, $existing['attendance_id']]);
                } else {
                    $insertSql = "INSERT INTO attendance (schedule_id, user_id, attendance_date, status, checked_at, checked_by) VALUES (?, ?, ?, ?, NOW(), ?)";
                    $this->db->prepare($insertSql)->execute([$schedule_id, $uid, $attendance_date, $is_present, $staff_id]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAttendanceReport($schedule_id) {
        // 1. ดึงช่วงเวลาเรียน
        $sqlC = "SELECT s.start_at, s.end_at, c.name as course_name 
                 FROM course_schedule s 
                 JOIN course c ON s.course_id = c.course_id 
                 WHERE s.schedule_id = ?";
        $stmtC = $this->db->prepare($sqlC);
        $stmtC->execute([$schedule_id]);
        $course = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$course) return null;

        // 2. สร้าง Array วันที่
        $dates = [];
        $start = new DateTime($course['start_at']);
        $end = new DateTime($course['end_at']);
        $end->modify('+1 day'); // include end date
        
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        foreach ($period as $dt) {
            $dates[] = $dt->format("Y-m-d"); // ใช้ format เดียวกับ database
        }

        // 3. ดึงนักเรียนทั้งหมด
        $sqlStu = "SELECT u.user_id, u.full_name 
                   FROM booking b 
                   JOIN user u ON b.user_id = u.user_id 
                   WHERE b.schedule_id = ? AND b.status = 'Confirmed'
                   ORDER BY u.full_name ASC";
        $stmtStu = $this->db->prepare($sqlStu);
        $stmtStu->execute([$schedule_id]);
        $students = $stmtStu->fetchAll(PDO::FETCH_ASSOC);

        // 4. ดึงข้อมูลการมาเรียนทั้งหมดของ Schedule นี้
        $sqlAtt = "SELECT user_id, attendance_date, status FROM attendance WHERE schedule_id = ?";
        $stmtAtt = $this->db->prepare($sqlAtt);
        $stmtAtt->execute([$schedule_id]);
        $rawAtt = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

        // Map Attendance to Student & Date
        $attendanceMap = [];
        foreach ($rawAtt as $row) {
            $key = $row['user_id'] . '_' . $row['attendance_date'];
            $attendanceMap[$key] = $row['status'];
        }

        return [
            'course' => $course,
            'dates' => $dates,
            'students' => $students,
            'attendanceMap' => $attendanceMap
        ];
    }
}
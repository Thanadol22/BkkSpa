<?php
class Promotion {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // ==========================================
    // ส่วนของ สินค้า (Product)
    // ==========================================
    public function getProductPromotions() {
        // ดึงข้อมูลโปรโมชั่น + ชื่อสินค้า
        $sql = "SELECT pp.*, p.name as item_name, p.product_picture as original_picture
                FROM promotion_product pp
                JOIN product p ON pp.product_id = p.product_id
                ORDER BY pp.start_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProductPromotion($data) {
        $sql = "INSERT INTO promotion_product 
                (user_id, product_id, discount, start_at, end_at, visible, promotion_p_picture) 
                VALUES (:user_id, :product_id, :discount, :start_at, :end_at, :visible, :picture)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // ดึงรายชื่อสินค้าทั้งหมดเพื่อใส่ใน Dropdown
    public function getAllProducts() {
        $stmt = $this->db->prepare("SELECT product_id, name FROM product WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // ส่วนของ หลักสูตร (Course)
    // ==========================================
    public function getCoursePromotions() {
        // ดึงข้อมูลโปรโมชั่น + ชื่อหลักสูตร
        $sql = "SELECT pc.*, c.name as item_name, c.course_picture as original_picture
                FROM promotion_course pc
                JOIN course c ON pc.course_id = c.course_id
                ORDER BY pc.start_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCoursePromotion($data) {
        // หมายเหตุ: ตามรูปภาพ DB ตาราง promotion_course ใช้ชื่อฟิลด์ promotion_p_picture
        $sql = "INSERT INTO promotion_course 
                (user_id, course_id, discount, start_at, end_at, visible, promotion_p_picture) 
                VALUES (:user_id, :course_id, :discount, :start_at, :end_at, :visible, :picture)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getAllCourses() {
        $stmt = $this->db->prepare("SELECT course_id, name FROM course WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [New] ดึงโปรโมชั่นที่ Active ของคอร์สนี้
    public function getActiveCoursePromotion($course_id) {
        $sql = "SELECT * FROM promotion_course 
                WHERE course_id = :course_id 
                  AND visible = 1 
                  AND NOW() BETWEEN start_at AND end_at
                ORDER BY discount DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $course_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [New] เช็คโปรโมชั่น ณ วันที่จอง (แฟร์กว่า)
    public function getPromotionAtDate($course_id, $date) {
        $sql = "SELECT * FROM promotion_course 
                WHERE course_id = :course_id 
                  AND visible = 1 
                  AND DATE(:date) BETWEEN DATE(start_at) AND DATE(end_at)
                ORDER BY discount DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $course_id, 'date' => $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
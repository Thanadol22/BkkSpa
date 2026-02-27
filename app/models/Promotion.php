<?php
class Promotion {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // ==========================================
    // ส่วนของ สินค้า (Product)
    // ==========================================
    // [New] ตรวจสอบช่วงเวลาซ้อนทับ
    // [New] ตรวจสอบช่วงเวลาซ้อนทับ
    public function checkProductOverlap($product_id, $start_at, $end_at, $exclude_id = null) {
        // ใช้ logic: (StartA <= EndB) AND (EndA >= StartB)
        // เอาเงื่อนไข AND visible = 1 ออก เพื่อให้เช็คซ้อนทับกับโปรที่ปิดอยู่ด้วย (กันพลาด)
        $sql = "SELECT COUNT(*) FROM promotion_product 
                WHERE product_id = :product_id 
                AND :start_at <= end_at 
                AND :end_at >= start_at";
        
        $params = [
            'product_id' => $product_id,
            'start_at' => $start_at,
            'end_at' => $end_at
        ];

        if ($exclude_id) {
            $sql .= " AND promotion_p_id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

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

    public function getProductPromotionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM promotion_product WHERE promotion_p_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProductPromotion($id, $data) {
        $sql = "UPDATE promotion_product 
                SET discount = :discount, start_at = :start_at, end_at = :end_at, visible = :visible";
        
        if (!empty($data['picture'])) {
            $sql .= ", promotion_p_picture = :picture";
        }
        
        $sql .= " WHERE promotion_p_id = :id";
        
        $params = [
            'discount' => $data['discount'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'visible' => $data['visible'],
            'id' => $id
        ];

        if (!empty($data['picture'])) {
            $params['picture'] = $data['picture'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleProductStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE promotion_product SET visible = :visible WHERE promotion_p_id = :id");
        return $stmt->execute(['visible' => $status, 'id' => $id]);
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
    // [New] ตรวจสอบช่วงเวลาซ้อนทับ
    public function checkCourseOverlap($course_id, $start_at, $end_at, $exclude_id = null) {
        // ใช้ logic: (StartA <= EndB) AND (EndA >= StartB)
        // เอาเงื่อนไข AND visible = 1 ออก เพื่อให้เช็คซ้อนทับกับโปรที่ปิดอยู่ด้วย
        $sql = "SELECT COUNT(*) FROM promotion_course 
                WHERE course_id = :course_id 
                AND :start_at <= end_at 
                AND :end_at >= start_at";

        $params = [
            'course_id' => $course_id,
            'start_at' => $start_at,
            'end_at' => $end_at
        ];
        
        if ($exclude_id) {
            $sql .= " AND promotion_c_id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

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

    public function getCoursePromotionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM promotion_course WHERE promotion_c_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCoursePromotion($id, $data) {
        $sql = "UPDATE promotion_course 
                SET discount = :discount, start_at = :start_at, end_at = :end_at, visible = :visible";
        
        if (!empty($data['picture'])) {
            $sql .= ", promotion_p_picture = :picture";
        }
        
        $sql .= " WHERE promotion_c_id = :id";
        
        $params = [
            'discount' => $data['discount'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'visible' => $data['visible'],
            'id' => $id
        ];

        if (!empty($data['picture'])) {
            $params['picture'] = $data['picture'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleCourseStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE promotion_course SET visible = :visible WHERE promotion_c_id = :id");
        return $stmt->execute(['visible' => $status, 'id' => $id]);
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
                  AND DATE(:date) BETWEEN DATE(start_at) AND DATE(end_at)
                ORDER BY discount DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $course_id, 'date' => $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivePromotionsForHomepage() {
        $promos = [];

        // 1. Product Promotions
        $sql = "SELECT pp.promotion_p_picture as picture, 'product' as type, pp.product_id as item_id
                FROM promotion_product pp
                JOIN product p ON pp.product_id = p.product_id
                WHERE pp.visible = 1 
                  AND p.is_active = 1
                  AND NOW() BETWEEN pp.start_at AND pp.end_at 
                  AND pp.promotion_p_picture IS NOT NULL 
                  AND pp.promotion_p_picture != ''";
        $stmt = $this->db->query($sql);
        $promos = array_merge($promos, $stmt->fetchAll(PDO::FETCH_ASSOC));

        // 2. Course Promotions
        $sql = "SELECT pc.promotion_p_picture as picture, 'course' as type, pc.course_id as item_id
                FROM promotion_course pc
                JOIN course c ON pc.course_id = c.course_id
                WHERE pc.visible = 1 
                  AND c.is_active = 1
                  AND NOW() BETWEEN pc.start_at AND pc.end_at 
                  AND pc.promotion_p_picture IS NOT NULL 
                  AND pc.promotion_p_picture != ''";
        $stmt = $this->db->query($sql);
        $promos = array_merge($promos, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        return $promos;
    }
}
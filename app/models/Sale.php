<?php
class Sale {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // ดึงรายการบิลขาย ตามวันที่
    public function getSalesByDate($date) {
        $sql = "SELECT s.*, u.full_name as staff_name 
                FROM sale s 
                LEFT JOIN user u ON s.recorded_by = u.user_id 
                WHERE DATE(s.sold_at) = :date 
                ORDER BY s.sold_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   // ดึงรายละเอียดสินค้าในบิล (Updated: เลือกฟิลด์ใหม่มาด้วย)
    public function getSaleItems($sale_id) {
        // เลือกทุกคอลัมน์จาก sale_item รวมถึง discount_percent, discount_per_unit, final_unit_price
        $sql = "SELECT si.*, p.name as product_name, p.sku, p.product_picture
                FROM sale_item si
                LEFT JOIN product p ON si.product_id = p.product_id
                WHERE si.sale_id = :sale_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sale_id' => $sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // คำนวณยอดรวมรายวัน
    public function getTotalDailyAmount($date) {
        $sql = "SELECT SUM(total_amount) FROM sale WHERE DATE(sold_at) = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        return $stmt->fetchColumn() ?: 0;
    }
}
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

    // [ใหม่] สร้างบิลขายแบบยัดไส้หลายรายการ (Shopping Cart)
    public function createSale($user_id, $items) {
        try {
            $this->db->beginTransaction();

            // 1. คำนวณยอดรวมทั้งหมด (Grand Total)
            $grand_total = 0;
            foreach ($items as $item) {
                $grand_total += $item['line_total'];
            }

            // 2. สร้าง Header (SALE)
            $sqlSale = "INSERT INTO sale (recorded_by, total_amount, sold_at) VALUES (?, ?, NOW())";
            $this->db->prepare($sqlSale)->execute([$user_id, $grand_total]);
            $sale_id = $this->db->lastInsertId();

            // 3. สร้าง Detail (SALE_ITEM) และตัดสต็อก
            $sqlItem = "INSERT INTO sale_item 
                        (sale_id, product_id, qty, unit_price, discount_percent, discount_per_unit, final_unit_price, line_total) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtItem = $this->db->prepare($sqlItem);

            $sqlUpdateStock = "UPDATE product SET stock = stock - ? WHERE product_id = ?";
            $stmtStock = $this->db->prepare($sqlUpdateStock);

            foreach ($items as $item) {
                // Insert Item
                $stmtItem->execute([
                    $sale_id,
                    $item['product_id'],
                    $item['qty'],
                    $item['unit_price'],
                    $item['discount_percent'],
                    $item['discount_per_unit'],
                    $item['final_unit_price'],
                    $item['line_total']
                ]);

                // Update Stock
                $stmtStock->execute([$item['qty'], $item['product_id']]);
            }

            $this->db->commit();
            return $sale_id;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create Sale Error: " . $e->getMessage());
            return false;
        }
    }
}
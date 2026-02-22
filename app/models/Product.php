<?php
// ไฟล์: app/models/Product.php

class Product {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ดึงข้อมูลสินค้าทั้งหมด
    public function getAllProducts() {
        // เรียงตามวันที่สร้างล่าสุดก่อน
        $sql = "SELECT * FROM product ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveProducts() {
        // ดึงเฉพาะ is_active = 1
        $sql = "SELECT * FROM product WHERE is_active = 1 ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [New] ดึงสินค้าพร้อมส่วนลด (ถ้ามี)
    public function getActiveProductsWithPromo() {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT p.*, MAX(pp.discount) as discount 
                FROM product p
                LEFT JOIN promotion_product pp ON p.product_id = pp.product_id
                    AND pp.visible = 1 
                    AND :now BETWEEN pp.start_at AND pp.end_at
                WHERE p.is_active = 1
                GROUP BY p.product_id
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['now' => $now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ฟังก์ชันปรับปรุงจำนวนสต็อก (บวกเพิ่ม หรือ ลบออก)
    public function adjustStock($product_id, $quantity) {
        // ใช้การบวกค่าเข้าไปตรงๆ (ถ้า $quantity เป็นลบ มันจะลดลงเอง)
        // วิธีนี้ดีกว่าดึงมาบวกใน PHP เพราะป้องกันปัญหาข้อมูลชนกัน
        $sql = "UPDATE product SET stock = stock + :qty WHERE product_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'qty' => $quantity,
            'id'  => $product_id
        ]);
    }

    // ดึงสินค้าจาก ID
    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM product WHERE product_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // (แถม) ฟังก์ชันดึงจำนวนสต็อกปัจจุบัน (เผื่อเอาไว้เช็คก่อนตัดของ)
    public function getStock($product_id) {
        $stmt = $this->db->prepare("SELECT stock FROM product WHERE product_id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetchColumn();
    }

   // 1. ฟังก์ชันหาโปรโมชั่นที่ "ใช้งานได้จริง" ณ เวลานี้
    // แก้ไข: ใช้เครื่องหมาย ? แทน :name เพื่อป้องกันปัญหา Parameter ซ้ำ
    public function getActivePromotion($product_id) {
        $now = date('Y-m-d H:i:s');
        
        // SQL ค้นหาโปรโมชั่น
        $sql = "SELECT discount, start_at, end_at FROM promotion_product 
                WHERE product_id = ? 
                  AND visible = 1 
                  AND start_at <= ? 
                  AND end_at >= ? 
                ORDER BY discount DESC LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$product_id, $now, $now]);
        
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $promo;
    }

    // แก้ไขฟังก์ชัน sellProduct เพื่อดู Error จริง
    public function sellProduct($user_id, $product_id, $qty) {
        try {
            $this->db->beginTransaction();

            // 1. ตรวจสอบสินค้า
            $product = $this->getProductById($product_id);
            if (!$product) {
                throw new Exception("ไม่พบสินค้า ID: " . $product_id);
            }
            
            // เช็คสต็อก (แสดงค่าปัจจุบันให้ดูด้วย)
            if ($product['stock'] < $qty) {
                throw new Exception("สต็อกไม่พอ (มี: {$product['stock']}, ต้องการ: $qty)");
            }

            // 2. คำนวณราคา
            $unit_price = $product['price'];
            $discount_percent = 0.00;
            
            $promo = $this->getActivePromotion($product_id);
            if ($promo) {
                $discount_percent = floatval($promo['discount']);
            }

            $discount_per_unit = $unit_price * ($discount_percent / 100);
            $final_unit_price  = $unit_price - $discount_per_unit;
            $line_total        = $final_unit_price * $qty;

            // 3. บันทึก Sale (หัวบิล)
            // เช็คก่อนว่า User ID นี้มีจริงไหม
            $stmtUser = $this->db->prepare("SELECT COUNT(*) FROM user WHERE user_id = ?");
            $stmtUser->execute([$user_id]);
            if ($stmtUser->fetchColumn() == 0) {
                throw new Exception("ไม่พบรหัสผู้ใช้งาน (User ID: $user_id) ในฐานข้อมูล - กรุณา Login ใหม่");
            }

            $sqlSale = "INSERT INTO sale (recorded_by, total_amount, sold_at) VALUES (?, ?, NOW())";
            $stmtSale = $this->db->prepare($sqlSale);
            if (!$stmtSale->execute([$user_id, $line_total])) {
                throw new Exception("บันทึกตาราง sale ไม่สำเร็จ: " . implode(" ", $stmtSale->errorInfo()));
            }
            $sale_id = $this->db->lastInsertId();

            // 4. บันทึก Sale Item (รายการสินค้า)
            $sqlItem = "INSERT INTO sale_item 
                        (sale_id, product_id, qty, unit_price, discount_percent, discount_per_unit, final_unit_price, line_total) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmtItem = $this->db->prepare($sqlItem);
            $paramsItem = [
                $sale_id,
                $product_id,
                $qty,
                $unit_price,
                $discount_percent,
                $discount_per_unit,
                $final_unit_price,
                $line_total
            ];
            
            if (!$stmtItem->execute($paramsItem)) {
                throw new Exception("บันทึกตาราง sale_item ไม่สำเร็จ (เช็คชื่อคอลัมน์ใน DB): " . implode(" ", $stmtItem->errorInfo()));
            }

            // 5. ตัดสต็อก
            $sqlUpdate = "UPDATE product SET stock = stock - ? WHERE product_id = ?";
            if (!$this->db->prepare($sqlUpdate)->execute([$qty, $product_id])) {
                throw new Exception("ตัดสต็อกไม่สำเร็จ");
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            
            // 🚨 แสดง Error ออกมาทางหน้าจอทันที เพื่อให้รู้สาเหตุ
            echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; margin: 20px; font-family: sans-serif;">';
            echo '<h3>❌ เกิดข้อผิดพลาด (Debug Mode)</h3>';
            echo '<p><strong>สาเหตุ:</strong> ' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
            echo '<a href="#" onclick="window.history.back()">กลับไปหน้าเดิม</a>';
            echo '</div>';
            exit; // หยุดการทำงานเพื่อให้เห็น Error
        }
    }

    // เพิ่มสินค้าใหม่
    public function createProduct($data) {
        $sql = "INSERT INTO product (name, description, price, sku, stock, product_type, product_picture, is_active) 
                VALUES (:name, :description, :price, :sku, :stock, :type, :picture, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sku' => $data['sku'],
            'stock' => $data['stock'],
            'type' => $data['product_type'],
            'picture' => $data['product_picture']
        ]);
    }

    // แก้ไขสินค้า
    public function updateProduct($id, $data) {
        // เช็คว่ามีการส่งรูปภาพมาใหม่หรือไม่
        if (!empty($data['product_picture'])) {
            $sql = "UPDATE product SET 
                    name = :name, description = :description, price = :price, 
                    sku = :sku, product_type = :type, product_picture = :picture, is_active = :active 
                    WHERE product_id = :id";
            $params = [
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'sku' => $data['sku'],
                'type' => $data['product_type'],
                'picture' => $data['product_picture'],
                'active' => $data['is_active'],
                'id' => $id
            ];
        } else {
            // ไม่เปลี่ยนรูป
            $sql = "UPDATE product SET 
                    name = :name, description = :description, price = :price, 
                    sku = :sku, product_type = :type, is_active = :active 
                    WHERE product_id = :id";
            $params = [
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'sku' => $data['sku'],
                'type' => $data['product_type'],
                'active' => $data['is_active'],
                'id' => $id
            ];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // ดึงรายชื่อประเภทสินค้าทั้งหมดที่มีในระบบ
    public function getAllProductTypes() {
        $sql = "SELECT DISTINCT product_type FROM product WHERE product_type IS NOT NULL AND product_type != '' ORDER BY product_type ASC";
        
        // ❌ บรรทัดนี้ผิด: $stmt = $this->query($sql);
        
        // ✅ แก้เป็นแบบนี้ครับ:
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // ส่งกลับเฉพาะชื่อประเภท
    }
    
}
?>
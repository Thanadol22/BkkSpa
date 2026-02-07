<div class="section-container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin:0; font-size:20px; color:var(--primary-green);">สินค้าทั้งหมด</h3>
        <a href="index.php?action=staff_product_create" class="btn-quick-green">
            <i class="fas fa-plus"></i> เพิ่มสินค้าใหม่
        </a>
    </div>

    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th style="width: 80px;">รูปภาพ</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th style="text-align: center;">คงเหลือ</th>
                    <th style="text-align: center;">สถานะ</th>
                    <th style="text-align: right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <?php 
                            $stock = $p['stock'] ?? 0;
                            $is_out_of_stock = ($stock <= 0);
                            $img = !empty($p['product_picture']) ? $p['product_picture'] : 'assets/images/no-image.jpg';
                        ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($img) ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                            </td>

                            <td>
                                <div style="font-weight: 600; font-size: 15px; color: #333;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <small style="color:#888;">
                                    <i class="fas fa-barcode"></i> <?= htmlspecialchars($p['sku'] ?? '-') ?> 
                                    • <?= htmlspecialchars($p['product_type'] ?? 'General') ?>
                                </small>
                            </td>

                            <td style="font-weight:500;">
                                <?= number_format($p['price'], 0) ?> บาท
                            </td>

                            <td style="text-align: center;">
                                <?php if ($stock < 10 && $stock > 0): ?>
                                    <span style="color: #e67e22; font-weight: bold;"><?= number_format($stock) ?></span>
                                <?php elseif ($is_out_of_stock): ?>
                                    <span style="color: #dc3545; font-weight: bold;">0</span>
                                <?php else: ?>
                                    <span style="color: #333; font-weight: bold;"><?= number_format($stock) ?></span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($p['is_active'] == 0): ?>
                                    <span class="status-badge badge-gray">ปิดใช้งาน</span>
                                <?php elseif ($is_out_of_stock): ?>
                                    <span class="status-badge badge-gray" style="background: #ffebeb; color: #dc3545;">สินค้าหมด</span>
                                <?php else: ?>
                                    <span class="status-badge badge-approved">พร้อมขาย</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: right;">
                                
                                <button type="button" 
                                        class="btn-icon" 
                                        title="เติมสต็อก"
                                        onclick="openStockModal(<?= $p['product_id'] ?>, 'add', '<?= htmlspecialchars($p['name']) ?>')">
                                    <i class="fas fa-plus-circle" style="color: var(--primary-green);"></i>
                                </button>

                                <button type="button" 
                                        class="btn-icon" 
                                        title="ขาย/ตัดสต็อก"
                                        onclick="openStockModal(<?= $p['product_id'] ?>, 'sell', '<?= htmlspecialchars($p['name']) ?>')">
                                    <i class="fas fa-minus-circle" style="color: #e67e22;"></i>
                                </button>

                                <a href="index.php?action=staff_product_edit&id=<?= $p['product_id'] ?>" 
                                   class="btn-icon" title="แก้ไข">
                                    <i class="fas fa-edit" style="color: #FFC107;"></i>
                                </a>

                                <a href="index.php?action=staff_product_delete&id=<?= $p['product_id'] ?>" 
                                   class="btn-icon" title="ลบ"
                                   onclick="return confirm('ยืนยันการลบสินค้า?');">
                                    <i class="fas fa-trash-alt" style="color: #dc3545;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px; color:#888;">
                            <i class="fas fa-box-open" style="font-size: 40px; margin-bottom: 10px; color: #ddd;"></i><br>
                            ไม่พบข้อมูลสินค้า
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="stockModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 400px;"> <form action="index.php?action=staff_product_update_stock" method="POST">
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="type" id="modalType">
            
            <div class="modal-header">
                <div class="modal-title-group">
                    <h3 id="stockModalTitle" style="color: var(--primary-green);">จัดการสต็อก</h3>
                    <p id="modalProductName">ชื่อสินค้า</p>
                </div>
                <button type="button" class="btn-close-x" onclick="closeStockModal()"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="modal-body">
                <div style="padding: 10px 0;">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom: 5px; font-weight: 500;">จำนวน (ชิ้น)</label>
                        <input type="number" name="quantity" class="capacity-input" style="width: 100%; text-align: center; font-size: 24px; font-weight: bold; color: #333;" value="1" min="1" required>
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 5px; font-weight: 500;">หมายเหตุ</label>
                        <input type="text" name="remark" class="custom-date-input" style="width: 100%;" placeholder="เช่น สั่งของเพิ่ม, ขายหน้าร้าน">
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-modal-cancel" onclick="closeStockModal()">ยกเลิก</button>
                <button type="submit" class="btn-modal-confirm" id="modalSubmitBtn">ยืนยัน</button>
            </div>
        </form>
    </div>
</div>

<script>
    const stockModal = document.getElementById('stockModal');

    function openStockModal(id, type, name) {
        document.getElementById('modalProductId').value = id;
        document.getElementById('modalType').value = type;
        document.getElementById('modalProductName').innerText = name;
        
        let title = document.getElementById('stockModalTitle');
        let btn = document.getElementById('modalSubmitBtn');
        
        if (type === 'add') {
            title.innerText = 'เพิ่มจำนวนสินค้า (Restock)';
            title.style.color = 'var(--primary-green)';
            btn.innerText = 'ยืนยันการเพิ่ม';
            btn.style.backgroundColor = 'var(--primary-green)';
        } else {
            title.innerText = 'ตัดสต็อกสินค้า (ขาย/เบิก)';
            title.style.color = '#dc3545'; // สีแดง
            btn.innerText = 'ยืนยันการขาย';
            btn.style.backgroundColor = '#dc3545';
        }
        
        stockModal.classList.add('show');
    }

    function closeStockModal() {
        stockModal.classList.remove('show');
    }

    window.onclick = function(event) {
        if (event.target == stockModal) closeStockModal();
    }
</script>
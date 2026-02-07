<div class="section-container">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h3 style="margin:0; font-size:20px; color:var(--primary-green);">
                <i class="fas fa-file-invoice-dollar"></i> รายการขายรายวัน
            </h3>
            <p style="font-size: 14px; color: #888; margin-top: 5px;">
                ข้อมูลประจำวันที่: <strong><?= date('d/m/Y', strtotime($filter_date)) ?></strong>
            </p>
        </div>

        <form method="GET" action="index.php" class="d-flex align-items-center">
            <input type="hidden" name="action" value="staff_sale_list">
            <div style="background: #fff; padding: 5px 15px; border-radius: 20px; border: 1px solid #ddd; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <i class="far fa-calendar-alt text-muted mr-2"></i>
                <input type="date" name="date" value="<?= $filter_date ?>"
                    style="border: none; outline: none; background: transparent; color: #555; font-family: inherit;"
                    onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
        <div>
            <span style="font-size: 15px; opacity: 0.9; font-weight: 300;">ยอดขายรวม (Total Sales)</span>
            <h1 style="margin: 5px 0 0; font-size: 36px; font-weight: 700;">฿<?= number_format($total_daily, 2) ?></h1>
        </div>
        <div style="font-size: 48px; opacity: 0.2;">
            <i class="fas fa-chart-pie"></i>
        </div>
    </div>

    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th style="width: 100px;">เวลา</th>
                    <th>เลขที่ใบเสร็จ</th>
                    <th>พนักงานขาย</th>
                    <th style="text-align: right;">ยอดสุทธิ</th>
                    <th style="text-align: right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales)): ?>
                    <?php foreach ($sales as $s): ?>
                        <tr>
                            <td>
                                <span style="font-weight: 500; color: #666; background: #f8f9fa; padding: 4px 8px; border-radius: 6px;">
                                    <?= date('H:i', strtotime($s['sold_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--primary-green);">
                                    #SALE-<?= str_pad($s['sale_id'], 6, '0', STR_PAD_LEFT) ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 28px; height: 28px; background: #e2e6ea; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px; color: #6c757d; font-size: 12px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span style="color: #333;"><?= htmlspecialchars($s['staff_name']) ?></span>
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: bold; font-size: 16px; color: #333;">
                                ฿<?= number_format($s['total_amount'], 2) ?>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-icon"
                                    onclick="viewSaleDetails(<?= $s['sale_id'] ?>)"
                                    title="ดูรายละเอียดสินค้า">
                                    <i class="fas fa-search-plus" style="color: #17a2b8;"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 50px; color:#999;">
                            <i class="fas fa-shopping-basket" style="font-size: 40px; margin-bottom: 15px; color: #eee;"></i><br>
                            ยังไม่มีรายการขายในวันนี้
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="saleDetailModal" class="custom-modal-overlay">
    <div class="custom-modal-content" style="max-width: 850px;">
        <div class="modal-header" style="justify-content: space-between; display: flex; align-items: center; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px;">
            <div>
                <h3 style="margin:0; color: var(--primary-green); font-size: 18px;"><i class="fas fa-receipt"></i> รายละเอียดใบเสร็จ</h3>
                <small style="color: #888;" id="modalSaleId">#SALE-XXXXXX</small>
            </div>
            <button type="button" onclick="closeSaleModal()" style="border:none; background:none; font-size:24px; cursor:pointer; color: #ccc; transition: 0.2s;">&times;</button>
        </div>

        <div class="modal-body" style="padding-top: 20px;">
            <div id="loadingDetails" style="text-align: center; color: #888; padding: 20px;">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i><br><br>กำลังโหลดข้อมูล...
            </div>

            <div class="table-responsive">
                <table class="staff-table" id="detailsTable" style="display: none; width: 100%;">
                    <thead>
                        <tr style="background: #f8f9fa; font-size: 13px; color: #666;">
                            <th style="padding: 10px;">สินค้า</th>
                            <th style="text-align: right; white-space: nowrap;">ราคาต่อชิ้น</th>
                            <th style="text-align: center; white-space: nowrap;">ส่วนลด (%)</th>
                            <th style="text-align: right; white-space: nowrap;">ส่วนลด (บาท)</th>
                            <th style="text-align: right; white-space: nowrap;">ราคาสุทธิ</th>
                            <th style="text-align: center;">จำนวน</th>
                            <th style="text-align: right;">รวมสุทธิ</th>
                        </tr>
                    </thead>
                    <tbody id="detailsBody">
                        </tbody>
                </table>
            </div>

            <div class="mt-3" style="text-align: right; border-top: 2px dashed #eee; padding-top: 15px; margin-top: 15px;">
                <h4 style="margin: 0; font-size: 20px;">รวมสุทธิ: <span id="modalTotalAmount" style="color: var(--primary-green); font-weight: bold;">฿0.00</span></h4>
            </div>
        </div>

        <div class="modal-footer" style="justify-content: center; margin-top: 20px;">
            <button type="button" class="btn-cancel" onclick="closeSaleModal()" style="background: #eee; color: #555; width: 100px;">ปิด</button>
        </div>
    </div>
</div>

<script>
    const saleModal = document.getElementById('saleDetailModal');
    const loading = document.getElementById('loadingDetails');
    const table = document.getElementById('detailsTable');
    const tbody = document.getElementById('detailsBody');
    const totalDisplay = document.getElementById('modalTotalAmount');
    const saleIdDisplay = document.getElementById('modalSaleId');

    function viewSaleDetails(saleId) {
        saleModal.classList.add('show');
        loading.style.display = 'block';
        table.style.display = 'none';
        tbody.innerHTML = '';
        totalDisplay.innerText = '...';
        saleIdDisplay.innerText = '#SALE-' + String(saleId).padStart(6, '0');

        fetch('index.php?action=staff_get_sale_details&sale_id=' + saleId)
            .then(response => response.json())
            .then(data => {
                let grandTotal = 0;

                if (data.length > 0) {
                    data.forEach(item => {
                        let unitPrice = parseFloat(item.unit_price);
                        let discountPct = parseFloat(item.discount_percent);
                        let discountVal = parseFloat(item.discount_per_unit);
                        let finalPrice = parseFloat(item.final_unit_price);
                        let qty = parseInt(item.qty);
                        let lineTotal = parseFloat(item.line_total);

                        grandTotal += lineTotal;

                        let percentDisplay = discountPct > 0 
                            ? `<span style="background:#ffebee; color:#c62828; padding:2px 6px; border-radius:4px; font-weight:bold; font-size:12px;">${discountPct}%</span>` 
                            : '-';
                        
                        let discountValDisplay = discountVal > 0 
                            ? `<span style="color:#dc3545;">-฿${discountVal.toLocaleString()}</span>` 
                            : '-';

                        let row = `
                            <tr>
                                <td style="padding: 10px;">
                                    <div style="font-weight: 600; color: #333;">${item.product_name || 'สินค้า (ลบแล้ว)'}</div>
                                    <small style="color: #999;">${item.sku || '-'}</small>
                                </td>
                                <td style="text-align: right; color: #666;">
                                    ฿${unitPrice.toLocaleString()}
                                </td>
                                <td style="text-align: center;">
                                    ${percentDisplay}
                                </td>
                                <td style="text-align: right;">
                                    ${discountValDisplay}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #333;">
                                    ฿${finalPrice.toLocaleString()}
                                </td>
                                <td style="text-align: center;">${qty}</td>
                                <td style="text-align: right; color: var(--primary-green); font-weight: bold;">
                                    ฿${lineTotal.toLocaleString()}
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:20px;">ไม่พบรายการสินค้า</td></tr>';
                }

                totalDisplay.innerText = '฿' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                loading.style.display = 'none';
                table.style.display = 'table';
            })
            .catch(err => {
                console.error(err);
                loading.innerHTML = '<span style="color:red">เกิดข้อผิดพลาดในการโหลดข้อมูล</span>';
            });
    }

    function closeSaleModal() {
        saleModal.classList.remove('show');
    }
    
    window.onclick = function(event) {
        if (event.target == saleModal) closeSaleModal();
    }
</script>
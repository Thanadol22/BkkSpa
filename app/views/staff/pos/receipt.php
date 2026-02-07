<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $sale_id ?></title>
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #555; display: flex; justify-content: center; padding: 20px; }
        .receipt-container { 
            background: #fff; 
            width: 320px; /* Standard Thermal Paper Width approx 80mm */
            padding: 20px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        
        .info { font-size: 12px; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px; }
        th { text-align: left; border-bottom: 1px solid #000; padding: 5px 0; }
        td { padding: 5px 0; vertical-align: top; }
        .qty { width: 30px; }
        .price { text-align: right; }
        
        .total-section { border-top: 1px solid #000; padding-top: 10px; font-size: 14px; font-weight: bold; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }

        .footer { text-align: center; font-size: 10px; margin-top: 20px; color: #888; }
        
        .actions { margin-top: 20px; text-align: center; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; display: inline-block; }
        .btn-print { background: #2ecc71; color: white; }
        .btn-back { background: #95a5a6; color: white; margin-left: 10px; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-container { width: 100%; box-shadow: none; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <h2>Bangkok Spa Academy</h2>
        <p>ใบเสร็จรับเงิน</p>
    </div>

    <div class="info">
        <div class="info-row">
            <span>เลขที่บิล:</span>
            <span>#<?= str_pad($sale_id, 6, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="info-row">
            <span>วันที่:</span>
            <span><?= date('d/m/Y H:i', strtotime($saleData['sold_at'])) ?></span>
        </div>
        <div class="info-row">
            <span>พนักงาน:</span>
            <span><?= htmlspecialchars($saleData['staff_name']) ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>รายการ</th>
                <th class="price">รวม</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($saleItems as $item): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($item['product_name']) ?><br>
                    <span style="color:#666; font-size:10px;">
                        <?= $item['qty'] ?> x <?= number_format($item['final_unit_price'], 2) ?>
                        <?php if($item['discount_percent'] > 0) echo "(-".(int)$item['discount_percent']."%)"; ?>
                    </span>
                </td>
                <td class="price"><?= number_format($item['line_total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>ยอดรวมสุทธิ</span>
            <span>฿<?= number_format($saleData['total_amount'], 2) ?></span>
        </div>
    </div>

    <div class="footer">
        ขอบคุณที่ใช้บริการ<br>
        Thank you
    </div>

    <div class="actions">
        <button onclick="window.print()" class="btn btn-print">พิมพ์ </button>
        <button onclick="history.length > 1 ? history.back() : window.close()" class="btn btn-back">กลับ</button>
    </div>
</div>


</body>
</html>

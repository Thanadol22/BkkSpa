<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= htmlspecialchars($receiptData['receipt_number']) ?></title>
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
        <p>ใบเสร็จรับเงิน (ค่าคอร์สเรียน)</p>
    </div>

    <div class="info">
        <div class="info-row">
            <span>เลขที่บิล:</span>
            <span><?= htmlspecialchars($receiptData['receipt_number']) ?></span>
        </div>
        <div class="info-row">
            <span>วันที่:</span>
            <span><?= date('d/m/Y H:i', strtotime($receiptData['receipt_date'])) ?></span>
        </div>
        <div class="info-row">
            <span>ลูกค้า:</span>
            <span><?= htmlspecialchars($receiptData['customer_name']) ?></span>
        </div>
        <div class="info-row">
            <span>ผู้ออกบิล:</span>
            <span><?= htmlspecialchars($receiptData['staff_name'] ?? 'System') ?></span>
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
            <tr>
                <td>
                    <?= htmlspecialchars($receiptData['course_name']) ?><br>
                    <span style="color:#666; font-size:10px;">
                        1 x <?= number_format($receiptData['course_price'], 2) ?>
                    </span>
                </td>
                <td class="price"><?= number_format($receiptData['course_price'], 2) ?></td>
            </tr>
            <?php 
            $discount_amount = $receiptData['course_price'] - $receiptData['amount'];
            if ($discount_amount > 0): 
                $discount_percent = ($discount_amount / $receiptData['course_price']) * 100;
            ?>
            <tr>
                <td>
                    <span style="color:#e74c3c; font-size:11px;">
                        ส่วนลด (<?= round($discount_percent) ?>%)
                    </span>
                </td>
                <td class="price" style="color:#e74c3c;">-<?= number_format($discount_amount, 2) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-section">
        <?php if (isset($discount_amount) && $discount_amount > 0): ?>
        <div class="total-row" style="font-weight: normal; font-size: 12px; color: #666;">
            <span>ยอดรวม</span>
            <span>฿<?= number_format($receiptData['course_price'], 2) ?></span>
        </div>
        <div class="total-row" style="font-weight: normal; font-size: 12px; color: #e74c3c;">
            <span>ส่วนลด</span>
            <span>-฿<?= number_format($discount_amount, 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row">
            <span>ยอดรวมสุทธิ</span>
            <span>฿<?= number_format($receiptData['amount'], 2) ?></span>
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

<div class="section-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>รายชื่อสมาชิกทั้งหมด</h3>
        </div>
    
    <div class="table-responsive">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เบอร์โทร</th>
                    <th>ธนาคาร</th>
                    <th>สถานะ</th>
                    <th>วันที่สมัคร</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($members)): ?>
                    <?php foreach($members as $m): ?>
                    <tr>
                        <td>#<?= $m['user_id'] ?></td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($m['full_name']) ?></div>
                            <small style="color:#888;"><?= htmlspecialchars($m['username']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($m['phone']) ?></td>
                        <td>
                            <?php if($m['bank_name']): ?>
                                <?= htmlspecialchars($m['bank_name']) ?><br>
                                <small><?= htmlspecialchars($m['bank_account']) ?></small>
                            <?php else: ?>
                                <span style="color:#ccc;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($m['is_active'] == 1): ?>
                                <span class="status-badge badge-approved">ใช้งานปกติ</span>
                            <?php else: ?>
                                <span class="status-badge badge-rejected">ถูกระงับ</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($m['created_at'])) ?></td>
                        <td>
                            <a href="index.php?action=staff_member_edit&id=<?= $m['user_id'] ?>" class="btn-quick-gray" style="padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                            <a href="index.php?action=staff_member_delete&id=<?= $m['user_id'] ?>" 
                               class="btn-quick-gray" 
                               style="padding: 5px 10px; font-size: 12px; color: #dc3545; border-color: #dc3545;"
                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสมาชิกคนนี้? ข้อมูลการจองทั้งหมดจะหายไปด้วย');">
                                <i class="fas fa-trash-alt"></i> ลบ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding: 30px;">ไม่พบข้อมูลสมาชิก</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
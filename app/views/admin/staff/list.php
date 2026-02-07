<div class="section-container">
    <div class="section-header">
        <h2 class="section-title">จัดการข้อมูลพนักงาน (Staff)</h2>
        <a href="index.php?action=admin_staff_create" class="btn-primary">
            <i class="fas fa-plus"></i> เพิ่มพนักงาน
        </a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>ชื่อ-นามสกุล</th>
                <th>อีเมล</th>
                <th>เบอร์โทรศัพท์</th>
                <th>สถานะ</th>
                <th width="120">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($staff_list)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">ไม่พบข้อมูลพนักงาน</td>
                </tr>
            <?php else: ?>
                <?php foreach ($staff_list as $st): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($st['username']) ?></b></td>
                        <td><?= htmlspecialchars($st['full_name']) ?></td>
                        <td><?= htmlspecialchars($st['email']) ?></td>
                        <td><?= htmlspecialchars($st['phone']) ?></td>
                        <td>
                            <span class="badge-status <?= $st['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $st['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?action=admin_staff_edit&id=<?= $st['user_id'] ?>" class="btn-icon-edit" title="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>

                            <?php if ($st['is_active'] == 1): ?>
                                <a href="index.php?action=admin_staff_toggle_status&id=<?= $st['user_id'] ?>&status=0"
                                    onclick="return confirm('ต้องการระงับการใช้งานพนักงานคนนี้? \n(พนักงานจะไม่สามารถล็อกอินได้ แต่ข้อมูลเก่าจะยังอยู่)');"
                                    class="btn-icon-delete" title="ระงับการใช้งาน">
                                    <i class="fas fa-user-slash"></i> </a>
                            <?php else: ?>
                                <a href="index.php?action=admin_staff_toggle_status&id=<?= $st['user_id'] ?>&status=1"
                                    onclick="return confirm('ต้องการเปิดใช้งานพนักงานคนนี้อีกครั้ง?');"
                                    style="color: #10b981;" title="เปิดใช้งาน">
                                    <i class="fas fa-user-check"></i> </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
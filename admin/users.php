<?php
// admin/users.php - User Management CMS with Edit & Delete Actions
$page_title = "Manage Users";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDBConnection();
$error = '';

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delUserId = (int)$_GET['id'];
    
    // Prevent deleting self
    if ($delUserId === $_SESSION['user_id']) {
        set_flash('error', 'You cannot delete your own active administrator account.');
    } else {
        $stmtDel = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$delUserId]);
        set_flash('success', 'User account deleted successfully.');
    }
    redirect('admin/users.php');
}

// Handle Edit User Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = (int)$_POST['user_id'];
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);

    if (!empty($name) && !empty($email)) {
        $stmtUpd = $db->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmtUpd->execute([$name, $email, $role, $userId]);
        set_flash('success', 'User account updated successfully.');
        redirect('admin/users.php');
    }
}

// User to edit if requested
$editUser = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmtE = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmtE->execute([$editId]);
    $editUser = $stmtE->fetch();
}

$users = $db->query("
    SELECT u.*, 
    (SELECT COUNT(id) FROM enrollments WHERE user_id = u.id) AS enrolled_count
    FROM users u
    ORDER BY u.created_at DESC
")->fetchAll();
?>

<div class="py-section">
    <div class="container">
        <div style="margin-bottom: 30px;">
            <h1 class="section-title" style="font-size: 1.75rem;">User & Student Directory</h1>
            <p style="color: var(--text-muted);">Manage student accounts, instructors, and system administrators.</p>
        </div>

        <?php render_flash('success'); ?>
        <?php render_flash('error'); ?>

        <?php if ($editUser): ?>
            <!-- Edit User Modal / Form -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px; margin-bottom: 40px; box-shadow: var(--shadow-md);">
                <h2 style="font-size: 1.2rem; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Edit User Account: #<?php echo $editUser['id']; ?></h2>
                <form action="users.php" method="POST">
                    <input type="hidden" name="update_user" value="1">
                    <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">System Role</label>
                            <select name="role" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                <option value="student" <?php echo $editUser['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="instructor" <?php echo $editUser['role'] === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                                <option value="admin" <?php echo $editUser['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save User Changes</button>
                    <a href="users.php" class="btn btn-outline">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-sm);">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                        <th style="padding: 10px 0;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Courses Enrolled</th>
                        <th>Registered Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 0;">#<?php echo $u['id']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span style="background:<?php echo $u['role'] === 'admin' ? '#fee2e2' : ($u['role'] === 'instructor' ? '#fef3c7' : '#d1fae5'); ?>; color:<?php echo $u['role'] === 'admin' ? '#991b1b' : ($u['role'] === 'instructor' ? '#92400e' : '#065f46'); ?>; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px;">
                                    <?php echo strtoupper($u['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $u['enrolled_count']; ?> courses</td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td style="text-align: right;">
                                <a href="users.php?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline" style="margin-right:4px;"><i class="fa-solid fa-user-pen"></i> Edit</a>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline" style="color:#ef4444;" onclick="return confirm('Are you sure you want to delete this user account?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

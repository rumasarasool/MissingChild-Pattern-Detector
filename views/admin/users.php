<?php
require_once '../../includes/auth.php';
require_once '../../models/Admin.php';

requireAdmin();

$pageTitle = 'Admin Management';
require_once '../../includes/header.php';

$adminModel = new Admin();
$admins = $adminModel->getAllAdmins();

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'investigator')
        ];
        
        if (empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
            $error = 'Please fill in all required fields.';
        } else {
            if ($adminModel->addAdmin($data)) {
                $message = 'Admin user added successfully.';
                header('Location: /views/admin/users.php');
                exit();
            } else {
                $error = 'Failed to add admin user.';
            }
        }
    } elseif ($action === 'remove') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        if ($admin_id == getCurrentAdminId()) {
            $error = 'You cannot remove your own account.';
        } else {
            if ($adminModel->removeAdmin($admin_id)) {
                $message = 'Admin user removed successfully.';
                header('Location: /views/admin/users.php');
                exit();
            } else {
                $error = 'Failed to remove admin user.';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-people"></i> Admin User Management</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Existing Admin Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $admin['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo $admin['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo $admin['last_login'] ? date('Y-m-d H:i', strtotime($admin['last_login'])) : 'Never'; ?></td>
                                <td>
                                    <?php if ($admin['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($admin['admin_id'] != getCurrentAdminId()): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Admin User</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="investigator">Investigator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus"></i> Add User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>


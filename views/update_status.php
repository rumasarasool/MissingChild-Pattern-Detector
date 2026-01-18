<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';

requireLogin();

$pageTitle = 'Update Case Status';
require_once '../includes/header.php';

$childModel = new Child();
$child_id = intval($_GET['id'] ?? 0);
$child = $childModel->getChildById($child_id);

if (!$child) {
    $_SESSION['alert_message'] = 'Child record not found.';
    $_SESSION['alert_type'] = 'danger';
    header('Location: /index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($status)) {
        $error = 'Please select a status.';
    } else {
        if ($childModel->updateCaseStatus($child_id, $status, $notes, getCurrentAdminId())) {
            $_SESSION['alert_message'] = 'Case status updated successfully.';
            $_SESSION['alert_type'] = 'success';
            header('Location: /views/view_child.php?id=' . $child_id);
            exit();
        } else {
            $error = 'Failed to update case status.';
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4"><i class="bi bi-check-circle"></i> Update Case Status</h1>
        <p><strong>Case:</strong> <?php echo htmlspecialchars($child['case_number']); ?> - <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></p>
        <p><strong>Current Status:</strong> <span class="badge bg-<?php 
            echo $child['case_status'] == 'Open' ? 'warning' : 
                 ($child['case_status'] == 'Matched' ? 'info' : 'success'); 
        ?>"><?php echo $child['case_status']; ?></span></p>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Open" <?php echo $child['case_status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                            <option value="Matched" <?php echo $child['case_status'] == 'Matched' ? 'selected' : ''; ?>>Matched</option>
                            <option value="Resolved" <?php echo $child['case_status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any relevant notes about this status change..."></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Status</button>
                <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';

requireLogin();

$pageTitle = 'Edit Missing Child';
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
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'age' => intval($_POST['age'] ?? 0),
        'gender' => sanitize($_POST['gender'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'missing_date' => $_POST['missing_date'] ?? '',
        'missing_location_city' => sanitize($_POST['missing_location_city'] ?? ''),
        'missing_location_area' => sanitize($_POST['missing_location_area'] ?? ''),
        'missing_location_landmark' => sanitize($_POST['missing_location_landmark'] ?? ''),
        'missing_location_latitude' => !empty($_POST['missing_location_latitude']) ? floatval($_POST['missing_location_latitude']) : null,
        'missing_location_longitude' => !empty($_POST['missing_location_longitude']) ? floatval($_POST['missing_location_longitude']) : null,
        'physical_description' => sanitize($_POST['physical_description'] ?? ''),
        'clothing_description' => sanitize($_POST['clothing_description'] ?? ''),
        'photo_url' => sanitize($_POST['photo_url'] ?? ''),
        'school_name' => sanitize($_POST['school_name'] ?? ''),
        'parent_guardian_name' => sanitize($_POST['parent_guardian_name'] ?? ''),
        'parent_guardian_contact' => sanitize($_POST['parent_guardian_contact'] ?? ''),
        'case_status' => sanitize($_POST['case_status'] ?? 'Open')
    ];

    if ($childModel->updateMissingChild($child_id, $data)) {
        $_SESSION['alert_message'] = 'Child record updated successfully.';
        $_SESSION['alert_type'] = 'success';
        header('Location: /views/view_child.php?id=' . $child_id);
        exit();
    } else {
        $error = 'Failed to update child record.';
    }
}

// Format datetime for input
$missing_datetime = date('Y-m-d\TH:i', strtotime($child['missing_date']));
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-pencil"></i> Edit Missing Child Record</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Similar form structure as add_child.php but with pre-filled values -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Case Number</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($child['case_number']); ?>" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($child['first_name']); ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($child['last_name']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="age" name="age" min="0" max="18" value="<?php echo $child['age']; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="Male" <?php echo $child['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $child['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $child['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $child['date_of_birth'] ? date('Y-m-d', strtotime($child['date_of_birth'])) : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="missing_date" class="form-label">Missing Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="missing_date" name="missing_date" value="<?php echo $missing_datetime; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="case_status" class="form-label">Case Status</label>
                            <select class="form-select" id="case_status" name="case_status">
                                <option value="Open" <?php echo $child['case_status'] == 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="Matched" <?php echo $child['case_status'] == 'Matched' ? 'selected' : ''; ?>>Matched</option>
                                <option value="Resolved" <?php echo $child['case_status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Location Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="missing_location_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="missing_location_city" name="missing_location_city" value="<?php echo htmlspecialchars($child['missing_location_city']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="missing_location_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="missing_location_area" name="missing_location_area" value="<?php echo htmlspecialchars($child['missing_location_area'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="missing_location_landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="missing_location_landmark" name="missing_location_landmark" value="<?php echo htmlspecialchars($child['missing_location_landmark'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="missing_location_latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="missing_location_latitude" name="missing_location_latitude" value="<?php echo $child['missing_location_latitude']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="missing_location_longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="missing_location_longitude" name="missing_location_longitude" value="<?php echo $child['missing_location_longitude']; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="physical_description" class="form-label">Physical Description</label>
                            <textarea class="form-control" id="physical_description" name="physical_description" rows="3"><?php echo htmlspecialchars($child['physical_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="clothing_description" class="form-label">Clothing Description</label>
                            <textarea class="form-control" id="clothing_description" name="clothing_description" rows="3"><?php echo htmlspecialchars($child['clothing_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="school_name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="school_name" name="school_name" value="<?php echo htmlspecialchars($child['school_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="parent_guardian_name" class="form-label">Parent/Guardian Name</label>
                            <input type="text" class="form-control" id="parent_guardian_name" name="parent_guardian_name" value="<?php echo htmlspecialchars($child['parent_guardian_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="parent_guardian_contact" class="form-label">Parent/Guardian Contact</label>
                            <input type="text" class="form-control" id="parent_guardian_contact" name="parent_guardian_contact" value="<?php echo htmlspecialchars($child['parent_guardian_contact'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="photo_url" class="form-label">Photo URL</label>
                        <input type="url" class="form-control" id="photo_url" name="photo_url" value="<?php echo htmlspecialchars($child['photo_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Record</button>
                <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';

requireLogin();

$pageTitle = 'Add Missing Child';
require_once '../includes/header.php';

$childModel = new Child();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'case_number' => sanitize($_POST['case_number'] ?? ''),
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
        'reported_by' => getCurrentAdminId()
    ];

    // Validate required fields
    if (empty($data['case_number']) || empty($data['first_name']) || empty($data['last_name']) || 
        empty($data['age']) || empty($data['gender']) || empty($data['missing_date']) || 
        empty($data['missing_location_city'])) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if case number already exists
        $existing = $childModel->getChildByCaseNumber($data['case_number']);
        if ($existing) {
            $error = 'Case number already exists.';
        } else {
            if ($childModel->addMissingChild($data)) {
                // Add to case history
                $child = $childModel->getChildByCaseNumber($data['case_number']);
                $childModel->updateCaseStatus($child['child_id'], 'Open', 'Case opened, initial investigation started', getCurrentAdminId());
                
                $_SESSION['alert_message'] = 'Missing child record added successfully.';
                $_SESSION['alert_type'] = 'success';
                header('Location: /index.php');
                exit();
            } else {
                $error = 'Failed to add missing child record.';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-person-plus"></i> Add Missing Child Record</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="case_number" class="form-label">Case Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="case_number" name="case_number" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="age" name="age" min="0" max="18" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="missing_date" class="form-label">Missing Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="missing_date" name="missing_date" required>
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
                            <input type="text" class="form-control" id="missing_location_city" name="missing_location_city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="missing_location_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="missing_location_area" name="missing_location_area">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="missing_location_landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="missing_location_landmark" name="missing_location_landmark">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="missing_location_latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="missing_location_latitude" name="missing_location_latitude">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="missing_location_longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="missing_location_longitude" name="missing_location_longitude">
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
                            <textarea class="form-control" id="physical_description" name="physical_description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="clothing_description" class="form-label">Clothing Description</label>
                            <textarea class="form-control" id="clothing_description" name="clothing_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="school_name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="school_name" name="school_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="parent_guardian_name" class="form-label">Parent/Guardian Name</label>
                            <input type="text" class="form-control" id="parent_guardian_name" name="parent_guardian_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="parent_guardian_contact" class="form-label">Parent/Guardian Contact</label>
                            <input type="text" class="form-control" id="parent_guardian_contact" name="parent_guardian_contact">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="photo_url" class="form-label">Photo URL</label>
                        <input type="url" class="form-control" id="photo_url" name="photo_url" placeholder="https://...">
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Record</button>
                <a href="/index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


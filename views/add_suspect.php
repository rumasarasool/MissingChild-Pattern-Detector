<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/Suspect.php';

requireLogin();

$pageTitle = 'Add Suspect';
require_once '../includes/header.php';

$childModel = new Child();
$suspectModel = new Suspect();

$child_id = intval($_GET['child_id'] ?? 0);
$child = $childModel->getChildById($child_id);

if (!$child) {
    $_SESSION['alert_message'] = 'Child record not found.';
    $_SESSION['alert_type'] = 'danger';
    header('Location: /index.php');
    exit();
}

$error = '';
$suspects = $suspectModel->getAllSuspects();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'new';
    
    if ($action === 'link') {
        // Link existing suspect
        $suspect_id = intval($_POST['suspect_id'] ?? 0);
        $association_type = sanitize($_POST['association_type'] ?? 'Suspected');
        $description = sanitize($_POST['description'] ?? '');
        
        if ($suspectModel->linkSuspectToCase($suspect_id, $child_id, $association_type, $description, getCurrentAdminId())) {
            $_SESSION['alert_message'] = 'Suspect linked to case successfully.';
            $_SESSION['alert_type'] = 'success';
            header('Location: /views/view_child.php?id=' . $child_id);
            exit();
        } else {
            $error = 'Failed to link suspect.';
        }
    } else {
        // Create new suspect and link
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'alias' => sanitize($_POST['alias'] ?? ''),
            'age' => !empty($_POST['age']) ? intval($_POST['age']) : null,
            'gender' => sanitize($_POST['gender'] ?? ''),
            'physical_description' => sanitize($_POST['physical_description'] ?? ''),
            'known_address' => sanitize($_POST['known_address'] ?? ''),
            'criminal_history' => sanitize($_POST['criminal_history'] ?? ''),
            'photo_url' => sanitize($_POST['photo_url'] ?? '')
        ];
        
        $suspect_id = $suspectModel->addSuspect($data);
        if ($suspect_id) {
            $association_type = sanitize($_POST['association_type'] ?? 'Suspected');
            $description = sanitize($_POST['description'] ?? '');
            if ($suspectModel->linkSuspectToCase($suspect_id, $child_id, $association_type, $description, getCurrentAdminId())) {
                $_SESSION['alert_message'] = 'Suspect added and linked to case successfully.';
                $_SESSION['alert_type'] = 'success';
                header('Location: /views/view_child.php?id=' . $child_id);
                exit();
            } else {
                $error = 'Failed to link suspect to case.';
            }
        } else {
            $error = 'Failed to add suspect.';
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-exclamation-triangle"></i> Add Suspect</h1>
        <p><strong>Case:</strong> <?php echo htmlspecialchars($child['case_number']); ?> - <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></p>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-3" id="suspectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button">New Suspect</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link" type="button">Link Existing</button>
            </li>
        </ul>

        <div class="tab-content" id="suspectTabContent">
            <!-- New Suspect Tab -->
            <div class="tab-pane fade show active" id="new" role="tabpanel">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="new">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Suspect Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="alias" class="form-label">Alias/Nickname</label>
                                    <input type="text" class="form-control" id="alias" name="alias">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="physical_description" class="form-label">Physical Description</label>
                                <textarea class="form-control" id="physical_description" name="physical_description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="known_address" class="form-label">Known Address</label>
                                <textarea class="form-control" id="known_address" name="known_address" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="criminal_history" class="form-label">Criminal History</label>
                                <textarea class="form-control" id="criminal_history" name="criminal_history" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="photo_url" class="form-label">Photo URL</label>
                                <input type="url" class="form-control" id="photo_url" name="photo_url">
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Case Association</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="association_type" class="form-label">Association Type</label>
                                <select class="form-select" id="association_type" name="association_type">
                                    <option value="Primary">Primary</option>
                                    <option value="Secondary">Secondary</option>
                                    <option value="Suspected" selected>Suspected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description/Notes</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Suspect</button>
                        <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Link Existing Tab -->
            <div class="tab-pane fade" id="link" role="tabpanel">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="link">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Link Existing Suspect</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="suspect_id" class="form-label">Select Suspect</label>
                                <select class="form-select" id="suspect_id" name="suspect_id" required>
                                    <option value="">Choose a suspect...</option>
                                    <?php foreach ($suspects as $suspect): ?>
                                    <option value="<?php echo $suspect['suspect_id']; ?>">
                                        <?php 
                                        $name = trim(($suspect['first_name'] ?? '') . ' ' . ($suspect['last_name'] ?? ''));
                                        if (empty($name)) $name = 'Unknown';
                                        echo htmlspecialchars($name);
                                        if ($suspect['alias']) echo ' (Alias: ' . htmlspecialchars($suspect['alias']) . ')';
                                        ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="association_type" class="form-label">Association Type</label>
                                <select class="form-select" id="association_type" name="association_type">
                                    <option value="Primary">Primary</option>
                                    <option value="Secondary">Secondary</option>
                                    <option value="Suspected" selected>Suspected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description/Notes</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-link"></i> Link Suspect</button>
                        <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/Witness.php';

requireLogin();

$pageTitle = 'Add Witness Report';
require_once '../includes/header.php';

$childModel = new Child();
$witnessModel = new Witness();

$child_id = intval($_GET['child_id'] ?? 0);
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
        'child_id' => $child_id,
        'witness_name' => sanitize($_POST['witness_name'] ?? ''),
        'witness_contact' => sanitize($_POST['witness_contact'] ?? ''),
        'witness_address' => sanitize($_POST['witness_address'] ?? ''),
        'report_date' => $_POST['report_date'] ?? date('Y-m-d H:i:s'),
        'sighting_location_city' => sanitize($_POST['sighting_location_city'] ?? ''),
        'sighting_location_area' => sanitize($_POST['sighting_location_area'] ?? ''),
        'sighting_location_landmark' => sanitize($_POST['sighting_location_landmark'] ?? ''),
        'sighting_location_latitude' => !empty($_POST['sighting_location_latitude']) ? floatval($_POST['sighting_location_latitude']) : null,
        'sighting_location_longitude' => !empty($_POST['sighting_location_longitude']) ? floatval($_POST['sighting_location_longitude']) : null,
        'sighting_date_time' => $_POST['sighting_date_time'] ?? null,
        'description' => sanitize($_POST['description'] ?? ''),
        'credibility_score' => intval($_POST['credibility_score'] ?? 5),
        'reported_by' => getCurrentAdminId()
    ];

    if ($witnessModel->addWitnessReport($data)) {
        $_SESSION['alert_message'] = 'Witness report added successfully.';
        $_SESSION['alert_type'] = 'success';
        header('Location: /views/view_child.php?id=' . $child_id);
        exit();
    } else {
        $error = 'Failed to add witness report.';
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-person-plus"></i> Add Witness Report</h1>
        <p><strong>Case:</strong> <?php echo htmlspecialchars($child['case_number']); ?> - <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></p>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Witness Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="witness_name" class="form-label">Witness Name</label>
                            <input type="text" class="form-control" id="witness_name" name="witness_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="witness_contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="witness_contact" name="witness_contact">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="report_date" class="form-label">Report Date</label>
                            <input type="datetime-local" class="form-control" id="report_date" name="report_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="witness_address" class="form-label">Address</label>
                        <textarea class="form-control" id="witness_address" name="witness_address" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Sighting Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sighting_location_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="sighting_location_city" name="sighting_location_city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sighting_location_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="sighting_location_area" name="sighting_location_area">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sighting_location_landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="sighting_location_landmark" name="sighting_location_landmark">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sighting_date_time" class="form-label">Sighting Date/Time</label>
                            <input type="datetime-local" class="form-control" id="sighting_date_time" name="sighting_date_time">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sighting_location_latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="sighting_location_latitude" name="sighting_location_latitude">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sighting_location_longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="sighting_location_longitude" name="sighting_location_longitude">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="credibility_score" class="form-label">Credibility Score (1-10)</label>
                        <input type="range" class="form-range" id="credibility_score" name="credibility_score" min="1" max="10" value="5" oninput="document.getElementById('score_display').textContent = this.value">
                        <span id="score_display">5</span>/10
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Report</button>
                <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


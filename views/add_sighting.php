<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/Location.php';

requireLogin();

$pageTitle = 'Add Sighting';
require_once '../includes/header.php';

$childModel = new Child();
$locationModel = new Location();

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
        'sighting_date_time' => $_POST['sighting_date_time'] ?? date('Y-m-d H:i:s'),
        'location_city' => sanitize($_POST['location_city'] ?? ''),
        'location_area' => sanitize($_POST['location_area'] ?? ''),
        'location_landmark' => sanitize($_POST['location_landmark'] ?? ''),
        'location_latitude' => !empty($_POST['location_latitude']) ? floatval($_POST['location_latitude']) : null,
        'location_longitude' => !empty($_POST['location_longitude']) ? floatval($_POST['location_longitude']) : null,
        'reported_by_witness' => sanitize($_POST['reported_by_witness'] ?? ''),
        'witness_contact' => sanitize($_POST['witness_contact'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'reliability_score' => intval($_POST['reliability_score'] ?? 5),
        'reported_by' => getCurrentAdminId()
    ];

    if (empty($data['location_city'])) {
        $error = 'Please provide at least the city.';
    } else {
        if ($locationModel->addSighting($data)) {
            $_SESSION['alert_message'] = 'Sighting recorded successfully.';
            $_SESSION['alert_type'] = 'success';
            header('Location: /views/view_child.php?id=' . $child_id);
            exit();
        } else {
            $error = 'Failed to record sighting.';
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-geo-alt"></i> Add Location Sighting</h1>
        <p><strong>Case:</strong> <?php echo htmlspecialchars($child['case_number']); ?> - <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></p>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Sighting Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sighting_date_time" class="form-label">Sighting Date/Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="sighting_date_time" name="sighting_date_time" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="location_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location_city" name="location_city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="location_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="location_area" name="location_area">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="location_landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="location_landmark" name="location_landmark">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="location_latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="location_latitude" name="location_latitude">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="location_longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="location_longitude" name="location_longitude">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reported_by_witness" class="form-label">Reported By (Witness Name)</label>
                            <input type="text" class="form-control" id="reported_by_witness" name="reported_by_witness">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="witness_contact" class="form-label">Witness Contact</label>
                            <input type="text" class="form-control" id="witness_contact" name="witness_contact">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="reliability_score" class="form-label">Reliability Score (1-10)</label>
                        <input type="range" class="form-range" id="reliability_score" name="reliability_score" min="1" max="10" value="5" oninput="document.getElementById('reliability_display').textContent = this.value">
                        <span id="reliability_display">5</span>/10
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Sighting</button>
                <a href="/views/view_child.php?id=<?php echo $child_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


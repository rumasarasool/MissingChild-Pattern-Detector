<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/PatternDetector.php';

requireLogin();

$pageTitle = 'Add Found Child Report';
require_once '../includes/header.php';

$childModel = new Child();
$patternDetector = new PatternDetector();

$error = '';
$potential_matches = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'age' => !empty($_POST['age']) ? intval($_POST['age']) : null,
        'gender' => sanitize($_POST['gender'] ?? ''),
        'found_date' => $_POST['found_date'] ?? date('Y-m-d H:i:s'),
        'found_location_city' => sanitize($_POST['found_location_city'] ?? ''),
        'found_location_area' => sanitize($_POST['found_location_area'] ?? ''),
        'found_location_landmark' => sanitize($_POST['found_location_landmark'] ?? ''),
        'found_location_latitude' => !empty($_POST['found_location_latitude']) ? floatval($_POST['found_location_latitude']) : null,
        'found_location_longitude' => !empty($_POST['found_location_longitude']) ? floatval($_POST['found_location_longitude']) : null,
        'physical_description' => sanitize($_POST['physical_description'] ?? ''),
        'clothing_description' => sanitize($_POST['clothing_description'] ?? ''),
        'condition_description' => sanitize($_POST['condition_description'] ?? ''),
        'reported_by' => getCurrentAdminId()
    ];

    if (empty($data['found_date']) || empty($data['found_location_city'])) {
        $error = 'Please provide found date and location city.';
    } else {
        if ($childModel->addFoundChild($data)) {
            $found_id = getDB()->lastInsertId();
            
            // Check for potential matches
            $potential_matches = $patternDetector->findPotentialMatches($found_id);
            
            // Generate alert if matches found
            if (!empty($potential_matches)) {
                $db = getDB();
                $best_match = $potential_matches[0];
                $stmt = $db->prepare("
                    INSERT INTO alerts (alert_type, title, message, related_child_id, severity)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $title = "Potential Match Found";
                $message = "Found child report #{$found_id} may match missing child case {$best_match['case_number']} based on age, gender, and location proximity.";
                $stmt->execute(['Found_Match', $title, $message, $best_match['child_id'], 'Medium']);
                
                $_SESSION['alert_message'] = 'Found child report added. ' . count($potential_matches) . ' potential match(es) found.';
                $_SESSION['alert_type'] = 'info';
            } else {
                $_SESSION['alert_message'] = 'Found child report added successfully.';
                $_SESSION['alert_type'] = 'success';
            }
            
            // Show matches on same page
            if (empty($potential_matches)) {
                header('Location: /index.php');
                exit();
            }
        } else {
            $error = 'Failed to add found child report.';
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-check-circle"></i> Add Found Child Report</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Found Child Information</h5>
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
                        <div class="col-md-2 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" min="0" max="18">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="found_date" class="form-label">Found Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="found_date" name="found_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="found_location_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="found_location_city" name="found_location_city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="found_location_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="found_location_area" name="found_location_area">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="found_location_landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="found_location_landmark" name="found_location_landmark">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="found_location_latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="found_location_latitude" name="found_location_latitude">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="found_location_longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="found_location_longitude" name="found_location_longitude">
                        </div>
                    </div>
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
                    <div class="mb-3">
                        <label for="condition_description" class="form-label">Condition Description</label>
                        <textarea class="form-control" id="condition_description" name="condition_description" rows="3" placeholder="Physical and mental condition of the child"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Report</button>
                <a href="/index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <?php if (!empty($potential_matches)): ?>
        <div class="card mt-4 border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Potential Matches Found</h5>
            </div>
            <div class="card-body">
                <p>The following missing children cases may match this found child report:</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Case Number</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Missing Date</th>
                                <th>Location</th>
                                <th>Match Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($potential_matches as $match): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($match['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($match['first_name'] . ' ' . $match['last_name']); ?></td>
                                <td><?php echo $match['age']; ?> years</td>
                                <td><?php echo $match['gender']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($match['missing_date'])); ?></td>
                                <td><?php echo htmlspecialchars($match['missing_location_city']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $score = ($match['age_score'] ?? 0) + ($match['location_score'] ?? 0);
                                        echo $score >= 15 ? 'success' : ($score >= 10 ? 'warning' : 'info');
                                    ?>">
                                        <?php echo $score; ?>/20
                                    </span>
                                </td>
                                <td>
                                    <a href="/views/view_child.php?id=<?php echo $match['child_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php
                                    $found_id = getDB()->lastInsertId();
                                    if ($found_id): ?>
                                    <form method="POST" action="/api/match_found.php" style="display:inline;" onsubmit="return confirm('Confirm match?');">
                                        <input type="hidden" name="found_id" value="<?php echo $found_id; ?>">
                                        <input type="hidden" name="child_id" value="<?php echo $match['child_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check"></i> Match
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


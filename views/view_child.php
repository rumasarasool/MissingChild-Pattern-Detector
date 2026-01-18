<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/Witness.php';
require_once '../models/Suspect.php';
require_once '../models/Location.php';

requireLogin();

$pageTitle = 'View Child Case';
require_once '../includes/header.php';

$childModel = new Child();
$witnessModel = new Witness();
$suspectModel = new Suspect();
$locationModel = new Location();

$child_id = intval($_GET['id'] ?? 0);
$child = $childModel->getChildById($child_id);

if (!$child) {
    $_SESSION['alert_message'] = 'Child record not found.';
    $_SESSION['alert_type'] = 'danger';
    header('Location: /index.php');
    exit();
}

// Get related data
$case_history = $childModel->getCaseHistory($child_id);
$witness_reports = $witnessModel->getWitnessReportsByCase($child_id);
$suspects = $suspectModel->getSuspectsByCase($child_id);
$sightings = $locationModel->getSightingsByChild($child_id);
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">
            <i class="bi bi-person"></i> Case: <?php echo htmlspecialchars($child['case_number']); ?>
            <span class="badge bg-<?php 
                echo $child['case_status'] == 'Open' ? 'warning' : 
                     ($child['case_status'] == 'Matched' ? 'info' : 'success'); 
            ?> ms-2">
                <?php echo $child['case_status']; ?>
            </span>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Child Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></p>
                        <p><strong>Age:</strong> <?php echo $child['age']; ?> years</p>
                        <p><strong>Gender:</strong> <?php echo $child['gender']; ?></p>
                        <?php if ($child['date_of_birth']): ?>
                        <p><strong>Date of Birth:</strong> <?php echo date('Y-m-d', strtotime($child['date_of_birth'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Missing Date:</strong> <?php echo date('Y-m-d H:i', strtotime($child['missing_date'])); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($child['missing_location_city']); ?></p>
                        <?php if ($child['missing_location_area']): ?>
                        <p><strong>Area:</strong> <?php echo htmlspecialchars($child['missing_location_area']); ?></p>
                        <?php endif; ?>
                        <?php if ($child['school_name']): ?>
                        <p><strong>School:</strong> <?php echo htmlspecialchars($child['school_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($child['physical_description']): ?>
                <hr>
                <p><strong>Physical Description:</strong><br><?php echo nl2br(htmlspecialchars($child['physical_description'])); ?></p>
                <?php endif; ?>
                <?php if ($child['clothing_description']): ?>
                <p><strong>Clothing Description:</strong><br><?php echo nl2br(htmlspecialchars($child['clothing_description'])); ?></p>
                <?php endif; ?>
                <?php if ($child['parent_guardian_name']): ?>
                <hr>
                <p><strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($child['parent_guardian_name']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($child['parent_guardian_contact']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Case History -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Case History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($case_history)): ?>
                <p class="text-muted">No case history available.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($case_history as $history): ?>
                    <div class="mb-3">
                        <strong><?php echo $history['status']; ?></strong>
                        <span class="text-muted ms-2"><?php echo date('Y-m-d H:i', strtotime($history['created_at'])); ?></span>
                        <?php if ($history['updated_by_username']): ?>
                        <span class="text-muted">by <?php echo htmlspecialchars($history['updated_by_username']); ?></span>
                        <?php endif; ?>
                        <?php if ($history['notes']): ?>
                        <p class="mt-1"><?php echo nl2br(htmlspecialchars($history['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="/views/edit_child.php?id=<?php echo $child_id; ?>" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-pencil"></i> Edit Record
                </a>
                <a href="/views/add_witness.php?child_id=<?php echo $child_id; ?>" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-person-plus"></i> Add Witness Report
                </a>
                <a href="/views/add_sighting.php?child_id=<?php echo $child_id; ?>" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-geo-alt"></i> Add Sighting
                </a>
                <a href="/views/add_suspect.php?child_id=<?php echo $child_id; ?>" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-exclamation-triangle"></i> Link Suspect
                </a>
                <a href="/views/update_status.php?id=<?php echo $child_id; ?>" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-check-circle"></i> Update Status
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Case Statistics</h5>
            </div>
            <div class="card-body">
                <p><strong>Witness Reports:</strong> <?php echo count($witness_reports); ?></p>
                <p><strong>Suspects Linked:</strong> <?php echo count($suspects); ?></p>
                <p><strong>Sightings:</strong> <?php echo count($sightings); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Witness Reports -->
<?php if (!empty($witness_reports)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Witness Reports</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Witness Name</th>
                                <th>Report Date</th>
                                <th>Sighting Location</th>
                                <th>Description</th>
                                <th>Credibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($witness_reports as $witness): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($witness['witness_name'] ?? 'Anonymous'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($witness['report_date'])); ?></td>
                                <td><?php echo htmlspecialchars($witness['sighting_location_city'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($witness['description'] ?? '', 0, 50)); ?>...</td>
                                <td>
                                    <span class="badge bg-<?php echo $witness['credibility_score'] >= 7 ? 'success' : ($witness['credibility_score'] >= 5 ? 'warning' : 'danger'); ?>">
                                        <?php echo $witness['credibility_score']; ?>/10
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Suspects -->
<?php if (!empty($suspects)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Linked Suspects</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Alias</th>
                                <th>Association Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suspects as $suspect): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($suspect['first_name'] ?? '') . ' ' . ($suspect['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($suspect['alias'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-warning"><?php echo $suspect['association_type']; ?></span></td>
                                <td><?php echo htmlspecialchars(substr($suspect['description'] ?? '', 0, 50)); ?>...</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sightings -->
<?php if (!empty($sightings)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Location Sightings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sighting Date/Time</th>
                                <th>Location</th>
                                <th>Reported By</th>
                                <th>Description</th>
                                <th>Reliability</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sightings as $sighting): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($sighting['sighting_date_time'])); ?></td>
                                <td><?php echo htmlspecialchars($sighting['location_city'] . ($sighting['location_area'] ? ', ' . $sighting['location_area'] : '')); ?></td>
                                <td><?php echo htmlspecialchars($sighting['reported_by_witness'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($sighting['description'] ?? '', 0, 50)); ?>...</td>
                                <td>
                                    <span class="badge bg-<?php echo $sighting['reliability_score'] >= 7 ? 'success' : ($sighting['reliability_score'] >= 5 ? 'warning' : 'danger'); ?>">
                                        <?php echo $sighting['reliability_score']; ?>/10
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>


<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/PatternDetector.php';

requireLogin();

$pageTitle = 'View Found Child';
require_once '../includes/header.php';

$childModel = new Child();
$patternDetector = new PatternDetector();

$found_id = intval($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("
    SELECT fc.*, mc.case_number, mc.first_name as missing_first_name, mc.last_name as missing_last_name,
           a.username as matched_by_username
    FROM found_children fc
    LEFT JOIN missing_children mc ON fc.matched_with_child_id = mc.child_id
    LEFT JOIN admins a ON fc.matched_by = a.admin_id
    WHERE fc.found_id = ?
");
$stmt->execute([$found_id]);
$found = $stmt->fetch();

if (!$found) {
    $_SESSION['alert_message'] = 'Found child record not found.';
    $_SESSION['alert_type'] = 'danger';
    header('Location: /index.php');
    exit();
}

// Get potential matches if not already matched
$potential_matches = [];
if (!$found['matched_with_child_id']) {
    $potential_matches = $patternDetector->findPotentialMatches($found_id);
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">
            <i class="bi bi-check-circle"></i> Found Child Report #<?php echo $found_id; ?>
            <?php if ($found['matched_with_child_id']): ?>
            <span class="badge bg-success ms-2">Matched</span>
            <?php else: ?>
            <span class="badge bg-warning ms-2">Not Matched</span>
            <?php endif; ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Found Child Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars(($found['first_name'] ?? 'Unknown') . ' ' . ($found['last_name'] ?? '')); ?></p>
                        <?php if ($found['age']): ?>
                        <p><strong>Age:</strong> <?php echo $found['age']; ?> years</p>
                        <?php endif; ?>
                        <?php if ($found['gender']): ?>
                        <p><strong>Gender:</strong> <?php echo $found['gender']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Found Date:</strong> <?php echo date('Y-m-d H:i', strtotime($found['found_date'])); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($found['found_location_city']); ?></p>
                        <?php if ($found['found_location_area']): ?>
                        <p><strong>Area:</strong> <?php echo htmlspecialchars($found['found_location_area']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($found['physical_description']): ?>
                <hr>
                <p><strong>Physical Description:</strong><br><?php echo nl2br(htmlspecialchars($found['physical_description'])); ?></p>
                <?php endif; ?>
                <?php if ($found['clothing_description']): ?>
                <p><strong>Clothing Description:</strong><br><?php echo nl2br(htmlspecialchars($found['clothing_description'])); ?></p>
                <?php endif; ?>
                <?php if ($found['condition_description']): ?>
                <p><strong>Condition:</strong><br><?php echo nl2br(htmlspecialchars($found['condition_description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($found['matched_with_child_id']): ?>
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Matched Case</h5>
            </div>
            <div class="card-body">
                <p><strong>Case Number:</strong> <?php echo htmlspecialchars($found['case_number']); ?></p>
                <p><strong>Missing Child:</strong> <?php echo htmlspecialchars($found['missing_first_name'] . ' ' . $found['missing_last_name']); ?></p>
                <p><strong>Matched By:</strong> <?php echo htmlspecialchars($found['matched_by_username'] ?? 'N/A'); ?></p>
                <p><strong>Matched At:</strong> <?php echo $found['matched_at'] ? date('Y-m-d H:i', strtotime($found['matched_at'])) : 'N/A'; ?></p>
                <a href="/views/view_child.php?id=<?php echo $found['matched_with_child_id']; ?>" class="btn btn-primary">
                    <i class="bi bi-eye"></i> View Missing Child Case
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <?php if (!$found['matched_with_child_id'] && !empty($potential_matches)): ?>
                <a href="#matches" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-exclamation-triangle"></i> View Potential Matches
                </a>
                <?php endif; ?>
                <a href="/views/search.php?type=found" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Back to Search
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!$found['matched_with_child_id'] && !empty($potential_matches)): ?>
<div class="row mt-3" id="matches">
    <div class="col-md-12">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Potential Matches</h5>
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
                                <th>Actions</th>
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
                                    <form method="POST" action="/api/match_found.php" style="display:inline;" onsubmit="return confirm('Confirm match?');">
                                        <input type="hidden" name="found_id" value="<?php echo $found_id; ?>">
                                        <input type="hidden" name="child_id" value="<?php echo $match['child_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check"></i> Match
                                        </button>
                                    </form>
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


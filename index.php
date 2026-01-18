<?php
require_once 'includes/auth.php';
require_once 'models/Child.php';
require_once 'models/PatternDetector.php';

requireLogin();

$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$childModel = new Child();
$patternDetector = new PatternDetector();

// Get statistics
$stats = $childModel->getStatistics();

// Get recent alerts
$db = getDB();
$stmt = $db->query("
    SELECT * FROM alerts 
    WHERE is_read = 0 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_alerts = $stmt->fetchAll();

// Get recent missing children
$recent_missing = $childModel->getAllMissingChildren(5);

// Get unread alert count
$stmt = $db->query("SELECT COUNT(*) as count FROM alerts WHERE is_read = 0");
$alert_count = $stmt->fetch()['count'];
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Missing Children</h5>
                <h2 class="mb-0"><?php echo $stats['total_missing']; ?></h2>
                <small>Total Cases</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Found Children</h5>
                <h2 class="mb-0"><?php echo $stats['total_found']; ?></h2>
                <small>Total Found</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Open Cases</h5>
                <h2 class="mb-0">
                    <?php 
                    $open_count = 0;
                    foreach ($stats['by_status'] as $status) {
                        if ($status['case_status'] == 'Open') {
                            $open_count = $status['count'];
                            break;
                        }
                    }
                    echo $open_count;
                    ?>
                </h2>
                <small>Pending Resolution</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-bell"></i> Active Alerts</h5>
                <h2 class="mb-0"><?php echo $alert_count; ?></h2>
                <small>Unread Notifications</small>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<?php if (!empty($recent_alerts)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-bell-fill"></i> Recent Alerts</h5>
            </div>
            <div class="card-body">
                <?php foreach ($recent_alerts as $alert): ?>
                <div class="alert alert-<?php 
                    echo $alert['severity'] == 'Critical' ? 'danger' : 
                         ($alert['severity'] == 'High' ? 'warning' : 'info'); 
                ?> mb-2">
                    <strong><?php echo htmlspecialchars($alert['title']); ?></strong><br>
                    <small><?php echo htmlspecialchars($alert['message']); ?></small>
                    <span class="badge bg-secondary float-end"><?php echo $alert['severity']; ?></span>
                </div>
                <?php endforeach; ?>
                <a href="/views/patterns.php" class="btn btn-sm btn-primary">View All Alerts</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Missing Children -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Recent Missing Children Cases</h5>
            </div>
            <div class="card-body">
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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_missing)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No missing children records found.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recent_missing as $child): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($child['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></td>
                                <td><?php echo $child['age']; ?> years</td>
                                <td><?php echo $child['gender']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($child['missing_date'])); ?></td>
                                <td><?php echo htmlspecialchars($child['missing_location_city']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $child['case_status'] == 'Open' ? 'warning' : 
                                             ($child['case_status'] == 'Matched' ? 'info' : 'success'); 
                                    ?>">
                                        <?php echo $child['case_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/views/view_child.php?id=<?php echo $child['child_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="/views/search.php" class="btn btn-primary">View All Cases</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>


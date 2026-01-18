<?php
require_once '../includes/auth.php';
require_once '../models/PatternDetector.php';
require_once '../models/Suspect.php';

requireLogin();

$pageTitle = 'Pattern Detection';
require_once '../includes/header.php';

$patternDetector = new PatternDetector();
$suspectModel = new Suspect();

// Get all patterns
$patterns = $patternDetector->getAllPatterns();

// Get alerts
$db = getDB();
$stmt = $db->query("
    SELECT * FROM alerts 
    ORDER BY created_at DESC
");
$all_alerts = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-diagram-3"></i> Pattern Detection & Alerts</h1>
    </div>
</div>

<!-- Alerts Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-bell-fill"></i> System Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($all_alerts)): ?>
                <p class="text-muted">No alerts at this time.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Severity</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_alerts as $alert): ?>
                            <tr>
                                <td>
                                    <?php
                                    $icons = [
                                        'Multiple_Missing_Same_Location' => 'bi-geo-alt',
                                        'Found_Match' => 'bi-check-circle',
                                        'Repeat_Suspect' => 'bi-exclamation-triangle',
                                        'Suspicious_Zone' => 'bi-shield-exclamation'
                                    ];
                                    $icon = $icons[$alert['alert_type']] ?? 'bi-bell';
                                    ?>
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </td>
                                <td><strong><?php echo htmlspecialchars($alert['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($alert['message']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $alert['severity'] == 'Critical' ? 'danger' : 
                                             ($alert['severity'] == 'High' ? 'warning' : 
                                             ($alert['severity'] == 'Medium' ? 'info' : 'secondary')); 
                                    ?>">
                                        <?php echo $alert['severity']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($alert['created_at'])); ?></td>
                                <td>
                                    <?php if ($alert['is_read']): ?>
                                    <span class="badge bg-secondary">Read</span>
                                    <?php else: ?>
                                    <span class="badge bg-primary">Unread</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- High-Risk Locations -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> High-Risk Locations (Hotspots)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['high_risk_locations'])): ?>
                <p class="text-muted">No high-risk locations detected.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Area</th>
                                <th>Landmark</th>
                                <th>Case Count</th>
                                <th>Case Numbers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patterns['high_risk_locations'] as $location): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($location['missing_location_city']); ?></strong></td>
                                <td><?php echo htmlspecialchars($location['missing_location_area'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($location['missing_location_landmark'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-danger"><?php echo $location['case_count']; ?></span></td>
                                <td><small><?php echo htmlspecialchars($location['case_numbers']); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Repeat Suspects -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-person-x"></i> Repeat Suspects</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['repeat_suspects'])): ?>
                <p class="text-muted">No repeat suspects detected.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Suspect Name</th>
                                <th>Alias</th>
                                <th>Case Count</th>
                                <th>Case Numbers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patterns['repeat_suspects'] as $suspect): ?>
                            <tr>
                                <td>
                                    <strong><?php 
                                        $name = trim(($suspect['first_name'] ?? '') . ' ' . ($suspect['last_name'] ?? ''));
                                        echo htmlspecialchars($name ?: 'Unknown');
                                    ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($suspect['alias'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-warning"><?php echo $suspect['case_count']; ?></span></td>
                                <td><small><?php echo htmlspecialchars($suspect['case_numbers']); ?></small></td>
                                <td>
                                    <a href="/views/view_suspect.php?id=<?php echo $suspect['suspect_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Area Clustering -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Area Clustering</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['area_clustering']['by_area'])): ?>
                <p class="text-muted">No area clustering detected.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>City</th>
                                <th>Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patterns['area_clustering']['by_area'] as $area): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($area['missing_location_area']); ?></strong></td>
                                <td><?php echo htmlspecialchars($area['missing_location_city']); ?></td>
                                <td><span class="badge bg-info"><?php echo $area['case_count']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> School Clustering</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['area_clustering']['by_school'])): ?>
                <p class="text-muted">No school clustering detected.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>City</th>
                                <th>Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patterns['area_clustering']['by_school'] as $school): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($school['school_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($school['missing_location_city']); ?></td>
                                <td><span class="badge bg-info"><?php echo $school['case_count']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Time Patterns -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Peak Hours</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['time_patterns']['peak_hours'])): ?>
                <p class="text-muted">No data available.</p>
                <?php else: ?>
                <canvas id="hoursChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Peak Days</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['time_patterns']['peak_days'])): ?>
                <p class="text-muted">No data available.</p>
                <?php else: ?>
                <canvas id="daysChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Peak Months</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['time_patterns']['peak_months'])): ?>
                <p class="text-muted">No data available.</p>
                <?php else: ?>
                <canvas id="monthsChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Suspicious Zones -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Suspicious Activity Zones</h5>
            </div>
            <div class="card-body">
                <?php if (empty($patterns['suspicious_zones'])): ?>
                <p class="text-muted">No suspicious zones detected.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Area</th>
                                <th>Landmark</th>
                                <th>Unique Children</th>
                                <th>Total Sightings</th>
                                <th>Case Numbers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patterns['suspicious_zones'] as $zone): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($zone['location_city']); ?></strong></td>
                                <td><?php echo htmlspecialchars($zone['location_area'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($zone['location_landmark'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-warning"><?php echo $zone['unique_children']; ?></span></td>
                                <td><span class="badge bg-danger"><?php echo $zone['total_sightings']; ?></span></td>
                                <td><small><?php echo htmlspecialchars($zone['case_numbers']); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
<?php if (!empty($patterns['time_patterns']['peak_hours'])): ?>
// Peak Hours Chart
const hoursCtx = document.getElementById('hoursChart');
new Chart(hoursCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_map(function($h) { return $h['hour']; }, $patterns['time_patterns']['peak_hours'])); ?>],
        datasets: [{
            label: 'Cases',
            data: [<?php echo implode(',', array_map(function($h) { return $h['count']; }, $patterns['time_patterns']['peak_hours'])); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.5)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
<?php endif; ?>

<?php if (!empty($patterns['time_patterns']['peak_days'])): ?>
// Peak Days Chart
const daysCtx = document.getElementById('daysChart');
new Chart(daysCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($d) { return $d['day_name']; }, $patterns['time_patterns']['peak_days'])) . '"'; ?>],
        datasets: [{
            label: 'Cases',
            data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $patterns['time_patterns']['peak_days'])); ?>],
            backgroundColor: 'rgba(255, 99, 132, 0.5)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
<?php endif; ?>

<?php if (!empty($patterns['time_patterns']['peak_months'])): ?>
// Peak Months Chart
const monthsCtx = document.getElementById('monthsChart');
new Chart(monthsCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($m) { return $m['month_name']; }, $patterns['time_patterns']['peak_months'])) . '"'; ?>],
        datasets: [{
            label: 'Cases',
            data: [<?php echo implode(',', array_map(function($m) { return $m['count']; }, $patterns['time_patterns']['peak_months'])); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.5)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>


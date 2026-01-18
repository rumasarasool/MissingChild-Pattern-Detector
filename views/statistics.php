<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';

requireLogin();

$pageTitle = 'Statistics & Analytics';
require_once '../includes/header.php';

$childModel = new Child();
$stats = $childModel->getStatistics();
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-bar-chart"></i> Statistics & Analytics</h1>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Total Missing</h5>
                <h2 class="mb-0"><?php echo $stats['total_missing']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Found</h5>
                <h2 class="mb-0"><?php echo $stats['total_found']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Open Cases</h5>
                <h2 class="mb-0">
                    <?php 
                    $open = 0;
                    foreach ($stats['by_status'] as $s) {
                        if ($s['case_status'] == 'Open') {
                            $open = $s['count'];
                            break;
                        }
                    }
                    echo $open;
                    ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Resolved Cases</h5>
                <h2 class="mb-0">
                    <?php 
                    $resolved = 0;
                    foreach ($stats['by_status'] as $s) {
                        if ($s['case_status'] == 'Resolved') {
                            $resolved = $s['count'];
                            break;
                        }
                    }
                    echo $resolved;
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Case Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Gender Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="genderChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Age Group Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="ageGroupChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Location Frequency -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Location-wise Case Frequency</h5>
            </div>
            <div class="card-body">
                <canvas id="locationChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Tables -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = $stats['total_missing'];
                            foreach ($stats['by_status'] as $status): 
                                $percentage = $total > 0 ? round(($status['count'] / $total) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $status['case_status'] == 'Open' ? 'warning' : 
                                             ($status['case_status'] == 'Matched' ? 'info' : 'success'); 
                                    ?>">
                                        <?php echo $status['case_status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $status['count']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                            <?php echo $percentage; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Locations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Cases</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($stats['location_frequency'] as $location): 
                                $percentage = $total > 0 ? round(($location['count'] / $total) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($location['missing_location_city']); ?></strong></td>
                                <td><?php echo $location['count']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $percentage; ?>%">
                                            <?php echo $percentage; ?>%
                                        </div>
                                    </div>
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

<script>
// Status Chart
const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($s) { return $s['case_status']; }, $stats['by_status'])) . '"'; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_map(function($s) { return $s['count']; }, $stats['by_status'])); ?>],
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gender Chart
const genderCtx = document.getElementById('genderChart');
new Chart(genderCtx, {
    type: 'pie',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($g) { return $g['gender']; }, $stats['by_gender'])) . '"'; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_map(function($g) { return $g['count']; }, $stats['by_gender'])); ?>],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Age Group Chart
const ageGroupCtx = document.getElementById('ageGroupChart');
new Chart(ageGroupCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($a) { return $a['age_group']; }, $stats['by_age_group'])) . '"'; ?>],
        datasets: [{
            label: 'Cases',
            data: [<?php echo implode(',', array_map(function($a) { return $a['count']; }, $stats['by_age_group'])); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.8)'
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

// Monthly Trends Chart
const monthlyCtx = document.getElementById('monthlyChart');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($m) { return $m['month']; }, array_reverse($stats['monthly_trends']))) . '"'; ?>],
        datasets: [{
            label: 'Missing Cases',
            data: [<?php echo implode(',', array_map(function($m) { return $m['count']; }, array_reverse($stats['monthly_trends']))); ?>],
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.4
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

// Location Chart
const locationCtx = document.getElementById('locationChart');
new Chart(locationCtx, {
    type: 'bar',
    data: {
        labels: [<?php echo '"' . implode('","', array_map(function($l) { return $l['missing_location_city']; }, $stats['location_frequency'])) . '"'; ?>],
        datasets: [{
            label: 'Cases',
            data: [<?php echo implode(',', array_map(function($l) { return $l['count']; }, $stats['location_frequency'])); ?>],
            backgroundColor: 'rgba(153, 102, 255, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
            x: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>


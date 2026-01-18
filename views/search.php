<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';
require_once '../models/Location.php';

requireLogin();

$pageTitle = 'Search';
require_once '../includes/header.php';

$childModel = new Child();
$locationModel = new Location();

$results = [];
$search_type = $_GET['type'] ?? 'missing';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    if ($search_type === 'missing') {
        $filters = [
            'name' => $_GET['name'] ?? '',
            'case_number' => $_GET['case_number'] ?? '',
            'age' => !empty($_GET['age']) ? intval($_GET['age']) : '',
            'gender' => $_GET['gender'] ?? '',
            'city' => $_GET['city'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        $results = $childModel->searchMissingChildren($filters);
    } elseif ($search_type === 'found') {
        $results = $childModel->getAllFoundChildren();
        // Filter if needed
        if (!empty($_GET['city'])) {
            $results = array_filter($results, function($r) {
                return stripos($r['found_location_city'], $_GET['city']) !== false;
            });
        }
    } elseif ($search_type === 'location') {
        $city = $_GET['city'] ?? '';
        $area = $_GET['area'] ?? '';
        $landmark = $_GET['landmark'] ?? '';
        if ($city || $area || $landmark) {
            $results = $locationModel->searchByLocation($city, $area, $landmark);
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-search"></i> Search</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Search Options</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $search_type === 'missing' ? 'active' : ''; ?>" href="?type=missing">
                            <i class="bi bi-person-x"></i> Missing Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $search_type === 'found' ? 'active' : ''; ?>" href="?type=found">
                            <i class="bi bi-person-check"></i> Found Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $search_type === 'location' ? 'active' : ''; ?>" href="?type=location">
                            <i class="bi bi-geo-alt"></i> By Location
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php 
                    echo $search_type === 'missing' ? 'Search Missing Children' : 
                         ($search_type === 'found' ? 'Search Found Children' : 'Search by Location');
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="type" value="<?php echo $search_type; ?>">
                    
                    <?php if ($search_type === 'missing'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="case_number" class="form-label">Case Number</label>
                            <input type="text" class="form-control" id="case_number" name="case_number" value="<?php echo htmlspecialchars($_GET['case_number'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" min="0" max="18" value="<?php echo htmlspecialchars($_GET['age'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">All</option>
                                <option value="Male" <?php echo ($_GET['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($_GET['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($_GET['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All</option>
                                <option value="Open" <?php echo ($_GET['status'] ?? '') === 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="Matched" <?php echo ($_GET['status'] ?? '') === 'Matched' ? 'selected' : ''; ?>>Matched</option>
                                <option value="Resolved" <?php echo ($_GET['status'] ?? '') === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_from" class="form-label">Missing Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_to" class="form-label">Missing Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                        </div>
                    </div>
                    <?php elseif ($search_type === 'found'): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="area" name="area" value="<?php echo htmlspecialchars($_GET['area'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="landmark" class="form-label">Landmark</label>
                            <input type="text" class="form-control" id="landmark" name="landmark" value="<?php echo htmlspecialchars($_GET['landmark'] ?? ''); ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="search" value="1" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="/views/search.php?type=<?php echo $search_type; ?>" class="btn btn-secondary">Clear</a>
                </form>
            </div>
        </div>

        <?php if (!empty($results)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Search Results (<?php echo count($results); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <?php if ($search_type === 'missing'): ?>
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
                            <?php elseif ($search_type === 'found'): ?>
                            <tr>
                                <th>Found Date</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Location</th>
                                <th>Matched</th>
                                <th>Actions</th>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <th>Case Number</th>
                                <th>Child Name</th>
                                <th>Sighting Date/Time</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            <?php endif; ?>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                            <tr>
                                <?php if ($search_type === 'missing'): ?>
                                <td><strong><?php echo htmlspecialchars($result['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                <td><?php echo $result['age']; ?> years</td>
                                <td><?php echo $result['gender']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($result['missing_date'])); ?></td>
                                <td><?php echo htmlspecialchars($result['missing_location_city']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $result['case_status'] == 'Open' ? 'warning' : 
                                             ($result['case_status'] == 'Matched' ? 'info' : 'success'); 
                                    ?>">
                                        <?php echo $result['case_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/views/view_child.php?id=<?php echo $result['child_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                                <?php elseif ($search_type === 'found'): ?>
                                <td><?php echo date('Y-m-d H:i', strtotime($result['found_date'])); ?></td>
                                <td><?php echo htmlspecialchars(($result['first_name'] ?? 'Unknown') . ' ' . ($result['last_name'] ?? '')); ?></td>
                                <td><?php echo $result['age'] ?? 'N/A'; ?></td>
                                <td><?php echo $result['gender'] ?? 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($result['found_location_city']); ?></td>
                                <td>
                                    <?php if ($result['matched_with_child_id']): ?>
                                    <span class="badge bg-success">Matched</span><br>
                                    <small><?php echo htmlspecialchars($result['case_number'] ?? ''); ?></small>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Not Matched</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/views/view_found.php?id=<?php echo $result['found_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                                <?php else: ?>
                                <td><strong><?php echo htmlspecialchars($result['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($result['sighting_date_time'])); ?></td>
                                <td><?php echo htmlspecialchars($result['location_city'] . ($result['location_area'] ? ', ' . $result['location_area'] : '')); ?></td>
                                <td><?php echo htmlspecialchars(substr($result['description'] ?? '', 0, 50)); ?>...</td>
                                <td>
                                    <a href="/views/view_child.php?id=<?php echo $result['child_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Case
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif (isset($_GET['search'])): ?>
        <div class="alert alert-info mt-3">No results found.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


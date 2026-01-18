<?php
require_once '../includes/auth.php';
require_once '../models/Child.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $found_id = intval($_POST['found_id'] ?? 0);
    $child_id = intval($_POST['child_id'] ?? 0);
    
    if ($found_id && $child_id) {
        $childModel = new Child();
        $childModel->matchFoundChild($found_id, $child_id, getCurrentAdminId());
        
        $_SESSION['alert_message'] = 'Found child matched with missing child case successfully.';
        $_SESSION['alert_type'] = 'success';
    }
}

header('Location: /index.php');
exit();


<?php
/**
 * Alert Generation System
 * This file contains functions to generate alerts based on patterns
 */

require_once '../config/database.php';
require_once '../models/PatternDetector.php';
require_once '../models/Child.php';

function generateAlerts() {
    $db = getDB();
    $patternDetector = new PatternDetector();
    $childModel = new Child();
    
    // 1. Alert: Multiple children missing from same location
    $highRiskLocations = $patternDetector->detectHighRiskLocations(2);
    foreach ($highRiskLocations as $location) {
        if ($location['case_count'] >= 2) {
            // Check if alert already exists
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM alerts 
                WHERE alert_type = 'Multiple_Missing_Same_Location' 
                AND related_location = ? 
                AND DATE(created_at) = CURDATE()
            ");
            $location_str = $location['missing_location_city'] . ', ' . ($location['missing_location_area'] ?? '') . ', ' . ($location['missing_location_landmark'] ?? '');
            $stmt->execute([$location_str]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if (!$exists) {
                $stmt = $db->prepare("
                    INSERT INTO alerts (alert_type, title, message, related_location, severity)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $title = "Multiple Children Missing from Same Location";
                $message = "{$location['case_count']} children have gone missing from: {$location_str}. Case numbers: {$location['case_numbers']}";
                $severity = $location['case_count'] >= 3 ? 'Critical' : ($location['case_count'] >= 2 ? 'High' : 'Medium');
                $stmt->execute(['Multiple_Missing_Same_Location', $title, $message, $location_str, $severity]);
            }
        }
    }
    
    // 2. Alert: Repeat suspects
    $repeatSuspects = $patternDetector->detectRepeatSuspects();
    foreach ($repeatSuspects as $suspect) {
        if ($suspect['case_count'] > 1) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM alerts 
                WHERE alert_type = 'Repeat_Suspect' 
                AND related_suspect_id = ? 
                AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$suspect['suspect_id']]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if (!$exists) {
                $stmt = $db->prepare("
                    INSERT INTO alerts (alert_type, title, message, related_suspect_id, severity)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $name = trim(($suspect['first_name'] ?? '') . ' ' . ($suspect['last_name'] ?? ''));
                if (empty($name)) $name = $suspect['alias'] ?? 'Unknown';
                $title = "Suspect Linked to Multiple Cases";
                $message = "Suspect '{$name}' is now linked to {$suspect['case_count']} cases: {$suspect['case_numbers']}";
                $severity = $suspect['case_count'] >= 3 ? 'Critical' : 'High';
                $stmt->execute(['Repeat_Suspect', $title, $message, $suspect['suspect_id'], $severity]);
            }
        }
    }
    
    // 3. Alert: Found child matches (handled in add_found.php)
    // This is triggered when a found child is added
    
    // 4. Alert: Suspicious zones
    $suspiciousZones = $patternDetector->detectSuspiciousZones(3);
    foreach ($suspiciousZones as $zone) {
        if ($zone['total_sightings'] >= 3) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM alerts 
                WHERE alert_type = 'Suspicious_Zone' 
                AND related_location = ? 
                AND DATE(created_at) = CURDATE()
            ");
            $location_str = $zone['location_city'] . ', ' . ($zone['location_area'] ?? '');
            $stmt->execute([$location_str]);
            $exists = $stmt->fetch()['count'] > 0;
            
            if (!$exists) {
                $stmt = $db->prepare("
                    INSERT INTO alerts (alert_type, title, message, related_location, severity)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $title = "Suspicious Activity Zone Detected";
                $message = "Multiple sightings ({$zone['total_sightings']}) reported in {$location_str} involving {$zone['unique_children']} different children. Case numbers: {$zone['case_numbers']}";
                $severity = $zone['total_sightings'] >= 5 ? 'Critical' : ($zone['total_sightings'] >= 3 ? 'High' : 'Medium');
                $stmt->execute(['Suspicious_Zone', $title, $message, $location_str, $severity]);
            }
        }
    }
}

// Auto-generate alerts when this file is included (can be called via cron or on-demand)
// generateAlerts();


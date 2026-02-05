<?php
/**
 * Corporate Blueprint Edit Shell
 * Handles data processing and includes the view template.
 */
require_once '../system/session_check.php';

// Initialize variables for the template
$success = "";
$error = "";
$jsonFile = 'corporate_strategy.json';

// Handle POST request (Save data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set parameters for the API logic
    $_POST['internal_save'] = true; 
    require_once '../api/corporate_blueprint_api.php';
    
    if (isset($api_response)) {
        if ($api_response['success']) {
            $success = $api_response['message'];
        } else {
            $error = $api_response['message'];
        }
    }
}

// Read current data for the template
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true) ?: [];
} else {
    $data = [];
}

// Extract sections for the template
$companyOverview = $data['companyOverview'] ?? [];
$orgStructure = $data['organizationStructure'] ?? [];
$internalOrg = $data['internalOrganization'] ?? [];
$timeline = $data['timeline'] ?? [];
$corporateCore = $data['corporateCore'] ?? [];
$cultureExplanation = $data['cultureExplanation'] ?? [];
$valuesExplanation = $data['valuesExplanation'] ?? [];
$strategicObjectives = $data['strategicObjectives'] ?? [];

// Include the template
include '../templates/corporate_blueprint_edit_template.php';
?>

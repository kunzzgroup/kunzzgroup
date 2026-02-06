<?php
// Created to support tokyopage5upload.php configuration storage

function getTokyoLocationConfig() {
    // Determine the path to the configuration file (relative to this file)
    $configFile = __DIR__ . '/tokyo_location_config.json';
    
    // Check if the file exists
    if (file_exists($configFile)) {
        // Read file content
        $content = file_get_contents($configFile);
        // Decode JSON
        $data = json_decode($content, true);
        
        // Return array if valid, else empty array
        return is_array($data) ? $data : [];
    }
    
    // Return empty array if file doesn't exist
    return [];
}

function saveTokyoLocationConfig($config) {
    // Determine the path to the configuration file
    $configFile = __DIR__ . '/tokyo_location_config.json';
    
    // Create directory if it doesn't exist (though likely it does since we are in cms)
    $dir = dirname($configFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Encode data to JSON with pretty print and unicode support
    $jsonData = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($jsonData === false) {
        error_log("JSON Encode Warning: " . json_last_error_msg());
        return false;
    }
    
    // Write to file
    return file_put_contents($configFile, $jsonData) !== false;
}
?>

<?php
// Session Refresh API
// Used to keep the session alive by making a request
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    // Update last activity time
    $_SESSION['last_activity'] = time();
    echo json_encode(['success' => true, 'message' => 'Session refreshed']);
} else {
    echo json_encode(['success' => false, 'code' => 'SESSION_EXPIRED', 'message' => 'Session expired']);
}
?>

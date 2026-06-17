<?php
/**
 * Verification script for Session Branch Fix
 * This script simulates different session/cookie states to verify branch recovery.
 */

function test_branch_recovery($session_branch, $cookie_branch, $required_branch) {
    echo "Testing with Session Branch: [" . ($session_branch ?: 'NULL') . "], Cookie Branch: [" . ($cookie_branch ?: 'NULL') . "], Required: [$required_branch]\n";
    
    // Simulate logic from API
    $current_session_branch = $session_branch;
    if (empty($current_session_branch)) {
        if (!empty($cookie_branch)) {
            $current_session_branch = strtoupper($cookie_branch);
            echo "  -> Recovered from cookie: $current_session_branch\n";
        }
    }
    
    $is_authorized = ($current_session_branch === 'KH' || $current_session_branch === $required_branch);
    
    if ($is_authorized) {
        echo "  [PASS] Authorized\n";
        return true;
    } else {
        echo "  [FAIL] Access Denied (Final Branch: " . ($current_session_branch ?: 'NULL') . ")\n";
        return false;
    }
}

echo "--- J1 API Verification ---\n";
test_branch_recovery('J1', '', 'J1');          // Session exists
test_branch_recovery('', 'J1', 'J1');          // Recover from cookie
test_branch_recovery('KH', '', 'J1');          // HQ access
test_branch_recovery('', 'KH', 'J1');          // HQ recover from cookie
test_branch_recovery('J2', '', 'J1');          // Wrong branch (Deny)
test_branch_recovery('', '', 'J1');            // Missing all (Deny)

echo "\n--- J2 API Verification ---\n";
test_branch_recovery('J2', '', 'J2');          // Session exists
test_branch_recovery('', 'J2', 'J2');          // Recover from cookie
test_branch_recovery('KH', '', 'J2');          // HQ access
test_branch_recovery('', 'KH', 'J2');          // HQ recover from cookie
test_branch_recovery('J1', '', 'J2');          // Wrong branch (Deny)
test_branch_recovery('', '', 'J2');            // Missing all (Deny)
?>

<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = get_pdo_connection();
    $stmt = $pdo->query("SELECT id, username, position, branch FROM users ORDER BY created_at DESC, id DESC LIMIT 15");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

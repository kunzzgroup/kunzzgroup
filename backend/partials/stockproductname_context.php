<?php

$currentApplicant = '';

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = get_pdo_connection();
        $stmt = $pdo->prepare('SELECT nickname, username_cn, username FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $nickname = trim((string) ($userRow['nickname'] ?? ''));
        $usernameCn = trim((string) ($userRow['username_cn'] ?? ''));
        $username = trim((string) ($userRow['username'] ?? ''));
        $currentApplicant = $nickname !== '' ? $nickname : ($usernameCn !== '' ? $usernameCn : $username);
    } catch (PDOException $e) {
        $currentApplicant = '';
    }
}

<?php

function qna_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function qna_prepareViewData() {
    return [
        'username' => $_SESSION['username'] ?? 'User',
        'position' => (!empty($_SESSION['position'])) ? $_SESSION['position'] : 'User',
        'backendWebBase' => qna_getBackendWebBase(),
    ];
}

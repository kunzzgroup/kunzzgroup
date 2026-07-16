<?php

function evaluation_form_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function evaluation_form_prepareViewData() {
    return [
        'evaluationDate' => date('Y-m-d'),
        'backendWebBase' => evaluation_form_getBackendWebBase(),
    ];
}

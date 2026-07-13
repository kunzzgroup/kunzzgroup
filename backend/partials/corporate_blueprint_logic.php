<?php

function corporate_blueprint_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function corporate_blueprint_loadStrategyData() {
    $jsonFile = dirname(__DIR__) . '/corporate_strategy.json';

    if (!file_exists($jsonFile)) {
        return null;
    }

    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $strategyData;
}

function corporate_blueprint_bezierQuad($t, $p0, $p1, $p2) {
    $mt = 1 - $t;

    return [
        $mt * $mt * $p0[0] + 2 * $mt * $t * $p1[0] + $t * $t * $p2[0],
        $mt * $mt * $p0[1] + 2 * $mt * $t * $p1[1] + $t * $t * $p2[1],
    ];
}

function corporate_blueprint_convertToOrgChartFormat($orgStructure) {
    if (empty($orgStructure['ceo'])) {
        return null;
    }

    $ceoTitle = $orgStructure['ceo']['title'] ?? $orgStructure['ceo']['fullTitle'] ?? 'CEO';
    $ceoName = $orgStructure['ceo']['name'] ?? '';

    $ceoNode = [
        'id' => 'ceo',
        'name' => $ceoName ?: '—',
        'title' => $ceoTitle,
        'level' => 'ceo',
        'children' => [],
    ];

    if (!empty($orgStructure['cLevel']) && is_array($orgStructure['cLevel'])) {
        foreach ($orgStructure['cLevel'] as $index => $member) {
            $memberTitle = $member['title'] ?? $member['fullTitle'] ?? '';
            $memberName = $member['name'] ?? '';

            $cLevelNode = [
                'id' => 'clevel_' . $index,
                'name' => $memberName ?: '—',
                'title' => $memberTitle,
                'level' => 'clevel',
                'children' => [],
            ];

            if (!empty($member['subordinates']) && is_array($member['subordinates'])) {
                foreach ($member['subordinates'] as $subIndex => $sub) {
                    $subTitle = $sub['title'] ?? $sub['fullTitle'] ?? '';
                    $subName = $sub['name'] ?? '';

                    $cLevelNode['children'][] = [
                        'id' => 'sub_' . $index . '_' . $subIndex,
                        'name' => $subName ?: '—',
                        'title' => $subTitle,
                        'level' => 'subordinate',
                    ];
                }
            }

            $ceoNode['children'][] = $cLevelNode;
        }
    }

    if (!empty($orgStructure['pa'])) {
        $paTitle = $orgStructure['pa']['title'] ?? $orgStructure['pa']['fullTitle'] ?? 'PA';
        $paName = $orgStructure['pa']['name'] ?? '';

        $ceoNode['children'][] = [
            'id' => 'pa',
            'name' => $paName ?: '—',
            'title' => $paTitle,
            'level' => 'pa',
        ];
    }

    return $ceoNode;
}

function corporate_blueprint_convertInternalOrgToOrgChartFormat($internalOrgData) {
    if (empty($internalOrgData) || empty($internalOrgData['departments'])) {
        return [];
    }

    $departmentTrees = [];

    foreach ($internalOrgData['departments'] as $deptIndex => $dept) {
        $deptName = $dept['name'] ?? '';
        $positions = $dept['positions'] ?? [];

        if (empty($positions)) {
            continue;
        }

        $firstPosition = $positions[0];
        $deptTitle = $firstPosition['title'] ?? $deptName;
        $deptNameValue = $firstPosition['name'] ?? '';

        $deptRootNode = [
            'id' => 'dept_' . $deptIndex,
            'name' => $deptNameValue ?: '—',
            'title' => $deptTitle,
            'level' => 'department',
            'departmentName' => $deptName,
            'children' => [],
        ];

        for ($i = 1, $count = count($positions); $i < $count; $i++) {
            $pos = $positions[$i];
            $deptRootNode['children'][] = [
                'id' => 'dept_' . $deptIndex . '_pos_' . $i,
                'name' => ($pos['name'] ?? '') ?: '—',
                'title' => $pos['title'] ?? '',
                'level' => 'position',
            ];
        }

        $departmentTrees[] = $deptRootNode;
    }

    return $departmentTrees;
}

function corporate_blueprint_prepareViewData() {
    $strategyData = corporate_blueprint_loadStrategyData();
    $orgChartData = null;
    $internalOrgChartData = [];
    $allObjectives = [];
    $ultimateGoal = '';
    $strategyEndYear = (int) date('Y') + 5;
    $backendRoot = dirname(__DIR__);
    $logoPath = '../images/images/logo.png';
    $logoFullPath = $backendRoot . '/../images/images/logo.png';

    if ($strategyData) {
        if (!empty($strategyData['organizationStructure'])) {
            $orgChartData = corporate_blueprint_convertToOrgChartFormat($strategyData['organizationStructure']);
        }

        $internalOrgData = $strategyData['internalOrganization'] ?? null;
        $internalOrgChartData = $internalOrgData
            ? corporate_blueprint_convertInternalOrgToOrgChartFormat($internalOrgData)
            : [];

        $strategicObjectives = $strategyData['strategicObjectives'] ?? [];
        $ultimateGoal = $strategyData['companyOverview']['ultimateGoal'] ?? '';
        $strategyEndYear = (int) ($strategyData['companyOverview']['strategyEndYear'] ?? $strategyEndYear);

        foreach ($strategicObjectives as $year => $objectives) {
            foreach ($objectives as $obj) {
                $allObjectives[] = array_merge($obj, ['year' => $year]);
            }
        }
    }

    return compact(
        'strategyData',
        'orgChartData',
        'internalOrgChartData',
        'allObjectives',
        'ultimateGoal',
        'strategyEndYear',
        'logoPath',
        'logoFullPath'
    );
}

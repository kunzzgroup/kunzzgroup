<?php

require_once __DIR__ . '/../../media_config.php';

function joinpage3upload_getLanguage() {
    if (isset($_POST['lang'])) {
        return ((string)$_POST['lang'] === 'en') ? 'en' : 'zh';
    }
    if (isset($_GET['lang'])) {
        return ((string)$_GET['lang'] === 'en') ? 'en' : 'zh';
    }

    return 'zh';
}

function joinpage3upload_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function joinpage3upload_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function joinpage3upload_getPageUrl($returnTo = null) {
    $returnTo = $returnTo ?? joinpage3upload_getReturnTo();
    $base = joinpage3upload_getBackendWebBase();

    if ($returnTo === 'v2') {
        return $base . '/joinpage3upload-v2';
    }

    return 'joinpage3upload.php';
}

function joinpage3upload_getUploadActionUrl($returnTo = null) {
    $returnTo = $returnTo ?? joinpage3upload_getReturnTo();
    $base = joinpage3upload_getBackendWebBase();

    if ($returnTo === 'v2') {
        return $base . '/joinpage3upload.php';
    }

    return 'joinpage3upload.php';
}

function joinpage3upload_buildUrl($pageUrl, array $params = []) {
    if (empty($params)) {
        return $pageUrl;
    }

    return $pageUrl . '?' . http_build_query($params);
}

function joinpage3upload_redirectAfterAction($successMsg = null) {
    $lang = joinpage3upload_getLanguage();
    $params = ['lang' => $lang];

    if ($successMsg !== null && $successMsg !== '') {
        $params['success'] = $successMsg;
    }

    $returnTo = joinpage3upload_getReturnTo();
    if ($returnTo === 'v2') {
        $base = joinpage3upload_getBackendWebBase();
        header('Location: ' . $base . '/joinpage3upload-v2?' . http_build_query($params));
        exit();
    }

    header('Location: joinpage3upload.php?' . http_build_query($params));
    exit();
}

function joinpage3upload_loadJobs(PDO $pdo, $language) {
    $stmt = $pdo->prepare('SELECT * FROM job_positions WHERE language = ? ORDER BY publish_date DESC, id DESC');
    $stmt->execute([$language]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function joinpage3upload_findEditJob(array $jobs, $editId, $language) {
    foreach ($jobs as $job) {
        if ((string)$job['id'] === (string)$editId && ($job['language'] ?? '') === $language) {
            return $job;
        }
    }

    return null;
}

function joinpage3upload_handlePost(PDO $pdo, &$success, &$error) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
        return;
    }

    $language = joinpage3upload_getLanguage();
    $isEnglish = ($language === 'en');
    $action = $_POST['action'];

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO job_positions
                (job_title, work_experience, recruitment_count, publish_date, company_category, company_department, salary, job_description, company_location, language)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $result = $stmt->execute([
                trim($_POST['job_title'] ?? ''),
                trim($_POST['job_experience'] ?? ''),
                trim($_POST['job_count'] ?? ''),
                $_POST['publish_date'] ?? date('Y-m-d'),
                $_POST['job_category'] ?? '',
                $_POST['company_department'] ?? '',
                $_POST['salary'] ?? '',
                trim($_POST['job_description'] ?? ''),
                $_POST['company_location'] ?? '',
                $language,
            ]);

            if ($result) {
                $successMsg = $isEnglish ? 'Job position added successfully!' : '职位添加成功！';
                joinpage3upload_redirectAfterAction($successMsg);
            }

            $error = $isEnglish ? 'Failed to add job position!' : '职位添加失败！';
        } catch (PDOException $e) {
            $error = $isEnglish ? 'Failed to add job position: ' . $e->getMessage() : '添加职位失败：' . $e->getMessage();
        }

        return;
    }

    if ($action === 'edit') {
        try {
            $stmt = $pdo->prepare('
                UPDATE job_positions
                SET job_title = ?, work_experience = ?, recruitment_count = ?, publish_date = ?,
                    company_category = ?, company_department = ?, salary = ?, job_description = ?, company_location = ?
                WHERE id = ? AND language = ?
            ');

            $result = $stmt->execute([
                trim($_POST['job_title'] ?? ''),
                trim($_POST['job_experience'] ?? ''),
                trim($_POST['job_count'] ?? ''),
                $_POST['publish_date'] ?? date('Y-m-d'),
                $_POST['job_category'] ?? '',
                $_POST['company_department'] ?? '',
                $_POST['salary'] ?? '',
                trim($_POST['job_description'] ?? ''),
                $_POST['company_location'] ?? '',
                $_POST['job_id'] ?? 0,
                $language,
            ]);

            if ($result) {
                $successMsg = $isEnglish ? 'Job position updated successfully!' : '职位更新成功！';
                joinpage3upload_redirectAfterAction($successMsg);
            }

            $error = $isEnglish ? 'Failed to update job position!' : '职位更新失败！';
        } catch (PDOException $e) {
            $error = $isEnglish ? 'Failed to update job position: ' . $e->getMessage() : '更新职位失败：' . $e->getMessage();
        }

        return;
    }

    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare('DELETE FROM job_positions WHERE id = ? AND language = ?');
            $result = $stmt->execute([$_POST['job_id'] ?? 0, $language]);

            if ($result) {
                $successMsg = $isEnglish ? 'Job position deleted successfully!' : '职位删除成功！';
                joinpage3upload_redirectAfterAction($successMsg);
            }

            $error = $isEnglish ? 'Failed to delete job position!' : '职位删除失败！';
        } catch (PDOException $e) {
            $error = $isEnglish ? 'Failed to delete job position: ' . $e->getMessage() : '删除职位失败：' . $e->getMessage();
        }
    }
}

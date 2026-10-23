<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/flash.php';

$pageTitle = $pageTitle ?? 'EventSpace';
$includeCharts = $includeCharts ?? false;
$includeAi = $includeAi ?? false;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | EventSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(eventspace_url('assets/css/theme.css')) ?>" rel="stylesheet">
    <link href="<?= e(eventspace_url('assets/css/components.css')) ?>" rel="stylesheet">
</head>
<body class="app-body">
<?php require __DIR__ . '/navbar.php'; ?>
<main class="app-main">
<?php foreach (consume_flash() as $type => $messages): foreach ($messages as $message): ?>
    <div class="alert alert-<?= e($type) ?> alert-dismissible fade show app-flash" role="alert">
        <?= e($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
    </div>
<?php endforeach; endforeach; ?>

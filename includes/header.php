<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BudgetMaster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset_url('assets/css/custom.css'); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= url_for('public/index.php'); ?>">BudgetMaster</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($user): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/index.php'); ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/transactions.php'); ?>">Transactions</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/recurring.php'); ?>">Recurring</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/budgets.php'); ?>">Budgets</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/categories.php'); ?>">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/accounts.php'); ?>">Accounts</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/goals.php'); ?>">Goals</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/bills.php'); ?>">Bills</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/reports.php'); ?>">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/settings.php'); ?>">Settings</a></li>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Hello, <?= htmlspecialchars($user['name']); ?></span>
                <a href="<?= url_for('public/logout.php'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
            <?php else: ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/login.php'); ?>">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url_for('public/register.php'); ?>">Register</a></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4">

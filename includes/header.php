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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/public/index.php">BudgetMaster</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($user): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/public/index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/transactions.php">Transactions</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/recurring.php">Recurring</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/budgets.php">Budgets</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/accounts.php">Accounts</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/goals.php">Goals</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/bills.php">Bills</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/reports.php">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/settings.php">Settings</a></li>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Hello, <?= htmlspecialchars($user['name']); ?></span>
                <a href="/public/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
            <?php else: ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/public/login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="/public/register.php">Register</a></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4">

<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$netWorth = get_net_worth($user['id']);
$cashFlow = get_cash_flow($user['id'], date('Y-m-01'), date('Y-m-t'));
$categorySummary = get_category_summary($user['id'], date('Y-m-01'), date('Y-m-t'));
$goals = get_goals($user['id']);
$bills = get_bills($user['id']);
$trends = get_monthly_trends($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-kpi shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Net Worth</h6>
                <h2 class="fw-bold">$<?= number_format($netWorth, 2); ?></h2>
                <p class="text-muted mb-0">Across all accounts</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-kpi shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Income (This Month)</h6>
                <h2 class="fw-bold text-success">$<?= number_format($cashFlow['income'], 2); ?></h2>
                <p class="text-muted mb-0">Money coming in</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-kpi shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Expenses (This Month)</h6>
                <h2 class="fw-bold text-danger">$<?= number_format($cashFlow['expense'], 2); ?></h2>
                <p class="text-muted mb-0">Money going out</p>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Spending by Category (This Month)</span>
            </div>
            <div class="card-body">
                <?php if ($categorySummary): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorySummary as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td><span class="badge bg-<?= $row['type'] === 'income' ? 'success' : 'danger'; ?> text-uppercase"><?= htmlspecialchars($row['type']); ?></span></td>
                                <td class="text-end">$<?= number_format($row['total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">Add transactions to see category insights.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">Cashflow Trends (12 months)</div>
            <div class="card-body">
                <?php if ($trends): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expense</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trends as $trend): ?>
                            <tr>
                                <td><?= htmlspecialchars($trend['month']); ?></td>
                                <td class="text-end text-success">$<?= number_format($trend['income'], 2); ?></td>
                                <td class="text-end text-danger">$<?= number_format($trend['expense'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">Capture more history to visualize trends.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">Upcoming Bills</div>
            <div class="card-body">
                <?php if ($bills): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($bills as $bill): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($bill['name']); ?></strong>
                            <div class="small text-muted">Due <?= htmlspecialchars($bill['due_date']); ?></div>
                        </div>
                        <span class="badge bg-secondary">$<?= number_format($bill['amount'], 2); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">No bills scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">Savings Goals</div>
            <div class="card-body">
                <?php if ($goals): ?>
                <?php foreach ($goals as $goal): $progress = min(100, ($goal['current_amount'] / $goal['target_amount']) * 100); ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($goal['name']); ?></strong>
                        <span class="text-muted">$<?= number_format($goal['current_amount'], 2); ?> / $<?= number_format($goal['target_amount'], 2); ?></span>
                    </div>
                    <div class="progress progress-goal">
                        <div class="progress-bar" role="progressbar" style="width: <?= $progress; ?>%" aria-valuenow="<?= $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">Target: <?= htmlspecialchars($goal['target_date']); ?></small>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted mb-0">Set a goal to start saving.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

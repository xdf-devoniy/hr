<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');
$cashFlow = get_cash_flow($user['id'], $start, $end);
$categorySummary = get_category_summary($user['id'], $start, $end);
$trends = get_monthly_trends($user['id']);
$transactions = get_transactions($user['id'], ['start_date' => $start, 'end_date' => $end]);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Reports & Insights</h1>
    <form class="d-flex gap-2">
        <input type="date" name="start" value="<?= htmlspecialchars($start); ?>" class="form-control">
        <input type="date" name="end" value="<?= htmlspecialchars($end); ?>" class="form-control">
        <button class="btn btn-outline-primary">Apply</button>
    </form>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Income</h6>
                <h2 class="fw-bold text-success">$<?= number_format($cashFlow['income'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Expenses</h6>
                <h2 class="fw-bold text-danger">$<?= number_format($cashFlow['expense'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Net Cash Flow</h6>
                <h2 class="fw-bold <?= ($cashFlow['income'] - $cashFlow['expense']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                    $<?= number_format($cashFlow['income'] - $cashFlow['expense'], 2); ?>
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-3">
    <div class="card-header">Category Breakdown</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = array_sum(array_column($categorySummary, 'total')); ?>
                    <?php foreach ($categorySummary as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['type']); ?></td>
                        <td class="text-end">$<?= number_format($row['total'], 2); ?></td>
                        <td class="text-end"><?= $total ? round(($row['total'] / $total) * 100, 1) : 0; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$categorySummary): ?>
                    <tr><td colspan="4" class="text-center text-muted">No data for selected range.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-3">
    <div class="card-header">Monthly Trends</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class="text-end">Income</th>
                        <th class="text-end">Expense</th>
                        <th class="text-end">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trends as $trend): ?>
                    <tr>
                        <td><?= htmlspecialchars($trend['month']); ?></td>
                        <td class="text-end text-success">$<?= number_format($trend['income'], 2); ?></td>
                        <td class="text-end text-danger">$<?= number_format($trend['expense'], 2); ?></td>
                        <td class="text-end <?= ($trend['income'] - $trend['expense']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            $<?= number_format($trend['income'] - $trend['expense'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$trends): ?>
                    <tr><td colspan="4" class="text-center text-muted">Add transactions to see trends.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Detailed Transactions</span>
        <a href="<?= url_for('public/transactions.php'); ?>" class="btn btn-sm btn-outline-primary">Manage</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Merchant</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= htmlspecialchars($transaction['date']); ?></td>
                        <td><?= htmlspecialchars($transaction['merchant'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($transaction['category_name']); ?></td>
                        <td><?= htmlspecialchars($transaction['account_name']); ?></td>
                        <td><span class="badge bg-<?= $transaction['type'] === 'income' ? 'success' : 'danger'; ?> text-uppercase"><?= htmlspecialchars($transaction['type']); ?></span></td>
                        <td class="text-end">$<?= number_format($transaction['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$transactions): ?>
                    <tr><td colspan="6" class="text-center text-muted">No transactions for selected range.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

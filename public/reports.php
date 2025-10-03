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
$settings = get_user_settings($user['id']);
$currency = $settings['currency'] ?? 'USD';
$netCashFlow = $cashFlow['income'] - $cashFlow['expense'];
$netClass = $netCashFlow >= 0 ? 'text-success' : 'text-danger';
$categoryTotal = array_sum(array_map(fn($row) => (float) $row['total'], $categorySummary));
$trendDisplay = array_map(function (array $trend) {
    $date = DateTime::createFromFormat('Y-m', $trend['month']);
    $trend['label'] = $date ? $date->format('M Y') : $trend['month'];
    $trend['income'] = (float) $trend['income'];
    $trend['expense'] = (float) $trend['expense'];
    $trend['net'] = $trend['income'] - $trend['expense'];
    return $trend;
}, $trends);
$chartPalette = ['#6366f1', '#14b8a6', '#f97316', '#22d3ee', '#a855f7', '#f43f5e', '#0ea5e9', '#84cc16', '#f59e0b', '#64748b'];
$categoryColors = [];
foreach ($categorySummary as $index => $row) {
    $categoryColors[] = $chartPalette[$index % count($chartPalette)];
}
$chartConfig = [
    'currency' => $currency,
    'category' => [
        'labels' => array_map(fn($row) => $row['name'], $categorySummary),
        'data' => array_map(fn($row) => (float) $row['total'], $categorySummary),
        'colors' => $categoryColors,
    ],
    'trends' => [
        'labels' => array_map(fn($trend) => $trend['label'], $trendDisplay),
        'income' => array_map(fn($trend) => $trend['income'], $trendDisplay),
        'expense' => array_map(fn($trend) => $trend['expense'], $trendDisplay),
        'net' => array_map(fn($trend) => $trend['net'], $trendDisplay),
    ],
];

include __DIR__ . '/../includes/header.php';
?>
<div class="report-hero d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-4 mb-4">
    <div>
        <span class="badge rounded-pill bg-white text-primary shadow-sm mb-2">Reports &amp; Insights</span>
        <h1 class="display-6 fw-semibold text-white mb-2">Financial overview</h1>
        <p class="lead text-white-50 mb-0">Stay on top of your income, expenses, and cash flow trends.</p>
    </div>
    <form class="d-flex flex-wrap gap-2 report-filter">
        <input type="date" name="start" value="<?= htmlspecialchars($start); ?>" class="form-control">
        <input type="date" name="end" value="<?= htmlspecialchars($end); ?>" class="form-control">
        <button class="btn btn-light fw-semibold">Update range</button>
    </form>
</div>
<div class="row g-3 g-lg-4 mb-4">
    <div class="col-md-4">
        <div class="card card-modern kpi-card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="kpi-label text-uppercase">Income</span>
                        <h2 class="kpi-value text-success mb-0"><?= format_currency((float) $cashFlow['income'], $currency); ?></h2>
                    </div>
                    <span class="kpi-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern kpi-card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="kpi-label text-uppercase">Expenses</span>
                        <h2 class="kpi-value text-danger mb-0"><?= format_currency((float) $cashFlow['expense'], $currency); ?></h2>
                    </div>
                    <span class="kpi-icon bg-danger-subtle text-danger"><i class="bi bi-cash-coin"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern kpi-card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="kpi-label text-uppercase">Net Cash Flow</span>
                        <h2 class="kpi-value <?= $netClass; ?> mb-0"><?= format_currency($netCashFlow, $currency); ?></h2>
                    </div>
                    <span class="kpi-icon bg-primary-subtle text-primary"><i class="bi bi-speedometer2"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card card-modern mb-4 shadow-sm border-0">
    <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row gap-2">
            <div>
                <h5 class="card-title mb-1">Spending breakdown</h5>
                <small class="text-muted">Understand where your money goes by category.</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <?php if ($categorySummary): ?>
                <div class="report-chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
                <?php else: ?>
                <p class="text-muted small mb-0">Add transactions to see spending distribution.</p>
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorySummary as $index => $row): ?>
                            <?php $share = $categoryTotal ? round(($row['total'] / $categoryTotal) * 100, 1) : 0; ?>
                            <tr>
                                <td class="fw-semibold">
                                    <span class="category-dot" style="background-color: <?= $categoryColors[$index] ?? '#0ea5e9'; ?>"></span>
                                    <?= htmlspecialchars($row['name']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-soft-<?= $row['type'] === 'income' ? 'success' : 'danger'; ?> text-uppercase"><?= htmlspecialchars($row['type']); ?></span>
                                </td>
                                <td class="text-end fw-semibold"><?= format_currency((float) $row['total'], $currency); ?></td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center gap-2 justify-content-end">
                                        <span class="fw-semibold"><?= $share; ?>%</span>
                                        <div class="progress progress-thin flex-grow-1">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $share; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$categorySummary): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No data for selected range.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card card-modern mb-4 shadow-sm border-0">
    <div class="card-header border-0 bg-transparent pb-0">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row gap-2">
            <div>
                <h5 class="card-title mb-1">Monthly trends</h5>
                <small class="text-muted">Track income and expenses over the past months.</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($trendDisplay): ?>
                <div class="report-chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
                <?php else: ?>
                <p class="text-muted small mb-0">Add transactions to see trends.</p>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <?php if ($trendDisplay): ?>
                <div class="list-group list-group-flush list-group-modern">
                    <?php foreach ($trendDisplay as $trend): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($trend['label']); ?></div>
                                <div class="small text-muted">Net <?= format_currency($trend['net'], $currency); ?></div>
                            </div>
                            <div class="text-end small">
                                <div class="text-success"><?= format_currency($trend['income'], $currency); ?></div>
                                <div class="text-danger"><?= format_currency($trend['expense'], $currency); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted small mb-0">Add transactions to see monthly highlights.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="card card-modern shadow-sm border-0">
    <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0">Detailed transactions</h5>
            <small class="text-muted">A quick view of activity for the selected period.</small>
        </div>
        <a href="<?= url_for('public/transactions.php'); ?>" class="btn btn-outline-primary btn-sm fw-semibold">Manage</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern table-hover align-middle mb-0">
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
                        <td>
                            <span class="badge badge-soft-<?= $transaction['type'] === 'income' ? 'success' : 'danger'; ?> text-uppercase"><?= htmlspecialchars($transaction['type']); ?></span>
                        </td>
                        <td class="text-end fw-semibold"><?= format_currency((float) $transaction['amount'], $currency); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$transactions): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No transactions for selected range.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
window.reportChartsData = <?= json_encode($chartConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

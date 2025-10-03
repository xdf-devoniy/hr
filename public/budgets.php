<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$categories = array_filter(get_categories($user['id']), fn($c) => $c['type'] === 'expense');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_budget((int) $_POST['id'], $user['id']);
    } else {
        save_budget([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'category_id' => $_POST['category_id'],
            'amount' => $_POST['amount'],
            'period_start' => $_POST['period_start'],
            'period_end' => $_POST['period_end'],
            'notes' => $_POST['notes'] ?? null
        ]);
    }
    redirect('public/budgets.php');
}
$budgets = get_budgets($user['id']);
$transactions = get_transactions($user['id'], ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]);
$spendByCategory = [];
foreach ($transactions as $transaction) {
    if ($transaction['type'] !== 'expense') continue;
    $spendByCategory[$transaction['category_id']] = ($spendByCategory[$transaction['category_id']] ?? 0) + $transaction['amount'];
}
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Budgets</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#budgetModal">Add Budget</button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Period</th>
                        <th class="text-end">Budgeted</th>
                        <th class="text-end">Spent (This Month)</th>
                        <th class="text-end">Remaining</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budgets as $budget):
                        $spent = $spendByCategory[$budget['category_id']] ?? 0;
                        $remaining = $budget['amount'] - $spent;
                        $progress = min(100, $budget['amount'] > 0 ? ($spent / $budget['amount']) * 100 : 0);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($budget['category_name']); ?></td>
                        <td><?= htmlspecialchars($budget['period_start']); ?> - <?= htmlspecialchars($budget['period_end']); ?></td>
                        <td class="text-end">$<?= number_format($budget['amount'], 2); ?></td>
                        <td class="text-end text-danger">$<?= number_format($spent, 2); ?></td>
                        <td class="text-end <?= $remaining < 0 ? 'text-danger' : 'text-success'; ?>">$<?= number_format($remaining, 2); ?></td>
                        <td class="table-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#budgetModal"
                                data-id="<?= $budget['id']; ?>"
                                data-category="<?= $budget['category_id']; ?>"
                                data-amount="<?= $budget['amount']; ?>"
                                data-start="<?= $budget['period_start']; ?>"
                                data-end="<?= $budget['period_end']; ?>"
                                data-notes="<?= htmlspecialchars($budget['notes'] ?? '', ENT_QUOTES); ?>">Edit</button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this budget?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $budget['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            <div class="progress progress-goal">
                                <div class="progress-bar <?= $progress > 100 ? 'bg-danger' : 'bg-success'; ?>" style="width: <?= min($progress, 100); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$budgets): ?>
                    <tr><td colspan="6" class="text-center text-muted">Set a budget to track spending.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="budgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="budgetId">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="budgetCategory" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="budgetAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period Start</label>
                        <input type="date" name="period_start" id="budgetStart" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period End</label>
                        <input type="date" name="period_end" id="budgetEnd" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="budgetNotes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
const budgetModal = document.getElementById('budgetModal');
budgetModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        budgetModal.querySelector('form').reset();
        document.getElementById('budgetId').value = '';
        return;
    }
    document.getElementById('budgetId').value = button.getAttribute('data-id');
    document.getElementById('budgetCategory').value = button.getAttribute('data-category');
    document.getElementById('budgetAmount').value = button.getAttribute('data-amount');
    document.getElementById('budgetStart').value = button.getAttribute('data-start');
    document.getElementById('budgetEnd').value = button.getAttribute('data-end');
    document.getElementById('budgetNotes').value = button.getAttribute('data-notes');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

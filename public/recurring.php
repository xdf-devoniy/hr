<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$categories = get_categories($user['id']);
$accounts = get_accounts($user['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_recurring_transaction((int) $_POST['id'], $user['id']);
    } else {
        save_recurring_transaction([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'account_id' => $_POST['account_id'],
            'category_id' => $_POST['category_id'],
            'type' => $_POST['type'],
            'amount' => $_POST['amount'],
            'frequency' => $_POST['frequency'],
            'next_run' => $_POST['next_run'],
            'description' => $_POST['description'] ?? null
        ]);
    }
    redirect('public/recurring.php');
}
$recurring = get_recurring_transactions($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Recurring Transactions</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recurringModal">Add Recurring</button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Account</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Frequency</th>
                        <th>Next Run</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recurring as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['description'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($item['account_name']); ?></td>
                        <td><?= htmlspecialchars($item['category_name']); ?></td>
                        <td><span class="badge bg-<?= $item['type'] === 'income' ? 'success' : 'danger'; ?> text-uppercase"><?= htmlspecialchars($item['type']); ?></span></td>
                        <td class="text-end">$<?= number_format($item['amount'], 2); ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($item['frequency']); ?></td>
                        <td><?= htmlspecialchars($item['next_run']); ?></td>
                        <td class="table-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#recurringModal"
                                data-id="<?= $item['id']; ?>"
                                data-account="<?= $item['account_id']; ?>"
                                data-category="<?= $item['category_id']; ?>"
                                data-type="<?= $item['type']; ?>"
                                data-amount="<?= $item['amount']; ?>"
                                data-frequency="<?= $item['frequency']; ?>"
                                data-next_run="<?= $item['next_run']; ?>"
                                data-description="<?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES); ?>">
                                Edit
                            </button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this recurring transaction?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$recurring): ?>
                    <tr><td colspan="8" class="text-center text-muted">No recurring transactions configured.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="recurringModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Recurring Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="recurringId">
                    <div class="mb-3">
                        <label class="form-label">Account</label>
                        <select name="account_id" id="recurringAccount" class="form-select" required>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id']; ?>"><?= htmlspecialchars($account['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="recurringCategory" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="recurringType" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="recurringAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" id="recurringFrequency" class="form-select" required>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Biweekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Next Run</label>
                        <input type="date" name="next_run" id="recurringNextRun" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="recurringDescription" class="form-control"></textarea>
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
const recurringModal = document.getElementById('recurringModal');
recurringModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        recurringModal.querySelector('form').reset();
        document.getElementById('recurringId').value = '';
        return;
    }
    document.getElementById('recurringId').value = button.getAttribute('data-id');
    document.getElementById('recurringAccount').value = button.getAttribute('data-account');
    document.getElementById('recurringCategory').value = button.getAttribute('data-category');
    document.getElementById('recurringType').value = button.getAttribute('data-type');
    document.getElementById('recurringAmount').value = button.getAttribute('data-amount');
    document.getElementById('recurringFrequency').value = button.getAttribute('data-frequency');
    document.getElementById('recurringNextRun').value = button.getAttribute('data-next_run');
    document.getElementById('recurringDescription').value = button.getAttribute('data-description');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

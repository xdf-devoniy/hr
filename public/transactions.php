<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$categories = get_categories($user['id']);
$accounts = get_accounts($user['id']);
$import = null;
$filters = [
    'category_id' => $_GET['category_id'] ?? '',
    'account_id' => $_GET['account_id'] ?? '',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'type' => $_GET['type'] ?? ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_transaction((int) $_POST['id'], $user['id']);
        redirect('public/transactions.php');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'import') {
        if (!empty($_FILES['transactions_csv']['tmp_name'])) {
            $rows = array_map('str_getcsv', file($_FILES['transactions_csv']['tmp_name']));
            $import = import_transactions($user['id'], $rows);
        }
    } else {
        save_transaction([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'account_id' => $_POST['account_id'],
            'category_id' => $_POST['category_id'],
            'type' => $_POST['type'],
            'amount' => $_POST['amount'],
            'date' => $_POST['date'],
            'merchant' => $_POST['merchant'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ]);
        redirect('public/transactions.php');
    }
}
$transactions = get_transactions($user['id'], array_filter($filters));
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Transactions</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal">Add Transaction</button>
</div>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row gy-2 gx-3 align-items-end">
            <div class="col-sm-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id']; ?>" <?= $filters['category_id'] == $category['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Account</label>
                <select name="account_id" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id']; ?>" <?= $filters['account_id'] == $account['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($account['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="income" <?= $filters['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
                    <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label">From</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date']); ?>" class="form-control">
            </div>
            <div class="col-sm-2">
                <label class="form-label">To</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date']); ?>" class="form-control">
            </div>
            <div class="col-sm-12 d-flex gap-2">
                <button class="btn btn-outline-primary">Filter</button>
                <a href="<?= url_for('public/transactions.php'); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
<?php if (!empty($import)): ?>
<div class="alert alert-info">
    Imported <?= $import['inserted']; ?> transactions.
    <?php if ($import['errors']): ?>
    <ul class="mb-0">
        <?php foreach ($import['errors'] as $error): ?>
        <li><?= htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <span>All Transactions</span>
        <form method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
            <input type="hidden" name="action" value="import">
            <input type="file" name="transactions_csv" id="transactionImport" class="d-none" accept=".csv">
            <label for="transactionImport" class="btn btn-outline-secondary btn-sm mb-0">Import CSV</label>
            <button type="submit" class="btn btn-outline-primary btn-sm">Upload</button>
            <a href="<?= url_for('samples/transactions_template.csv'); ?>" class="btn btn-outline-success btn-sm">Template</a>
        </form>
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
                        <th></th>
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
                        <td class="table-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#transactionModal"
                                data-id="<?= $transaction['id']; ?>"
                                data-account="<?= $transaction['account_id']; ?>"
                                data-category="<?= $transaction['category_id']; ?>"
                                data-type="<?= $transaction['type']; ?>"
                                data-amount="<?= $transaction['amount']; ?>"
                                data-date="<?= $transaction['date']; ?>"
                                data-merchant="<?= htmlspecialchars($transaction['merchant'] ?? '', ENT_QUOTES); ?>"
                                data-notes="<?= htmlspecialchars($transaction['notes'] ?? '', ENT_QUOTES); ?>">
                                Edit
                            </button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this transaction?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $transaction['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$transactions): ?>
                    <tr><td colspan="7" class="text-center text-muted">No transactions yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="transactionId">
                    <div class="mb-3">
                        <label class="form-label">Account</label>
                        <select name="account_id" id="transactionAccount" class="form-select" required>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id']; ?>"><?= htmlspecialchars($account['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="transactionCategory" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="transactionType" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="transactionAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="transactionDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Merchant</label>
                        <input type="text" name="merchant" id="transactionMerchant" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="transactionNotes" class="form-control"></textarea>
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
const transactionModal = document.getElementById('transactionModal');
transactionModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        transactionModal.querySelector('form').reset();
        document.getElementById('transactionId').value = '';
        return;
    }
    document.getElementById('transactionId').value = button.getAttribute('data-id');
    document.getElementById('transactionAccount').value = button.getAttribute('data-account');
    document.getElementById('transactionCategory').value = button.getAttribute('data-category');
    document.getElementById('transactionType').value = button.getAttribute('data-type');
    document.getElementById('transactionAmount').value = button.getAttribute('data-amount');
    document.getElementById('transactionDate').value = button.getAttribute('data-date');
    document.getElementById('transactionMerchant').value = button.getAttribute('data-merchant');
    document.getElementById('transactionNotes').value = button.getAttribute('data-notes');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

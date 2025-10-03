<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_account((int) $_POST['id'], $user['id']);
    } else {
        save_account([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'balance' => $_POST['balance'] ?? 0,
            'interest_rate' => $_POST['interest_rate'] ?? 0
        ]);
    }
    redirect('public/accounts.php');
}
$accounts = get_accounts($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Accounts</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal">Add Account</button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th class="text-end">Balance</th>
                        <th class="text-end">Interest %</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?= htmlspecialchars($account['name']); ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($account['type']); ?></td>
                        <td class="text-end">$<?= number_format($account['balance'], 2); ?></td>
                        <td class="text-end"><?= number_format($account['interest_rate'], 2); ?>%</td>
                        <td class="table-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#accountModal"
                                data-id="<?= $account['id']; ?>"
                                data-name="<?= htmlspecialchars($account['name'], ENT_QUOTES); ?>"
                                data-type="<?= $account['type']; ?>"
                                data-balance="<?= $account['balance']; ?>"
                                data-interest="<?= $account['interest_rate']; ?>">Edit</button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this account?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $account['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$accounts): ?>
                    <tr><td colspan="5" class="text-center text-muted">Add your first financial account.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="accountId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="accountName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="accountType" class="form-select" required>
                            <option value="checking">Checking</option>
                            <option value="savings">Savings</option>
                            <option value="credit">Credit</option>
                            <option value="cash">Cash</option>
                            <option value="investment">Investment</option>
                            <option value="loan">Loan</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Balance</label>
                        <input type="number" step="0.01" name="balance" id="accountBalance" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" id="accountInterest" class="form-control">
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
const accountModal = document.getElementById('accountModal');
accountModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        accountModal.querySelector('form').reset();
        document.getElementById('accountId').value = '';
        return;
    }
    document.getElementById('accountId').value = button.getAttribute('data-id');
    document.getElementById('accountName').value = button.getAttribute('data-name');
    document.getElementById('accountType').value = button.getAttribute('data-type');
    document.getElementById('accountBalance').value = button.getAttribute('data-balance');
    document.getElementById('accountInterest').value = button.getAttribute('data-interest');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

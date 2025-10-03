<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_bill((int) $_POST['id'], $user['id']);
    } else {
        save_bill([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'name' => $_POST['name'],
            'amount' => $_POST['amount'],
            'due_date' => $_POST['due_date'],
            'frequency' => $_POST['frequency'],
            'auto_pay' => isset($_POST['auto_pay']) ? 1 : 0,
            'notes' => $_POST['notes'] ?? null
        ]);
    }
    redirect('/public/bills.php');
}
$bills = get_bills($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Bills & Subscriptions</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#billModal">Add Bill</button>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Frequency</th>
                        <th>Auto Pay</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $bill): ?>
                    <tr>
                        <td><?= htmlspecialchars($bill['name']); ?></td>
                        <td>$<?= number_format($bill['amount'], 2); ?></td>
                        <td><?= htmlspecialchars($bill['due_date']); ?></td>
                        <td class="text-uppercase"><?= htmlspecialchars($bill['frequency']); ?></td>
                        <td><?= $bill['auto_pay'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                        <td><?= htmlspecialchars($bill['notes']); ?></td>
                        <td class="table-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#billModal"
                                data-id="<?= $bill['id']; ?>"
                                data-name="<?= htmlspecialchars($bill['name'], ENT_QUOTES); ?>"
                                data-amount="<?= $bill['amount']; ?>"
                                data-due="<?= $bill['due_date']; ?>"
                                data-frequency="<?= $bill['frequency']; ?>"
                                data-auto_pay="<?= $bill['auto_pay']; ?>"
                                data-notes="<?= htmlspecialchars($bill['notes'] ?? '', ENT_QUOTES); ?>">Edit</button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this bill?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $bill['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$bills): ?>
                    <tr><td colspan="7" class="text-center text-muted">Add your recurring bills.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="billModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="billId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="billName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" id="billAmount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="billDue" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" id="billFrequency" class="form-select" required>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Biweekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                            <option value="once">Once</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="billAutoPay" name="auto_pay">
                        <label class="form-check-label" for="billAutoPay">Auto Pay Enabled</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="billNotes" class="form-control"></textarea>
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
const billModal = document.getElementById('billModal');
billModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        billModal.querySelector('form').reset();
        document.getElementById('billId').value = '';
        document.getElementById('billAutoPay').checked = false;
        return;
    }
    document.getElementById('billId').value = button.getAttribute('data-id');
    document.getElementById('billName').value = button.getAttribute('data-name');
    document.getElementById('billAmount').value = button.getAttribute('data-amount');
    document.getElementById('billDue').value = button.getAttribute('data-due');
    document.getElementById('billFrequency').value = button.getAttribute('data-frequency');
    document.getElementById('billAutoPay').checked = button.getAttribute('data-auto_pay') === '1';
    document.getElementById('billNotes').value = button.getAttribute('data-notes');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

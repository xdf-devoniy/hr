<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_goal((int) $_POST['id'], $user['id']);
    } else {
        save_goal([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'name' => $_POST['name'],
            'target_amount' => $_POST['target_amount'],
            'target_date' => $_POST['target_date'],
            'current_amount' => $_POST['current_amount'] ?? 0,
            'notes' => $_POST['notes'] ?? null
        ]);
    }
    redirect('/public/goals.php');
}
$goals = get_goals($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Savings Goals</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goalModal">Add Goal</button>
</div>
<div class="row g-3">
    <?php foreach ($goals as $goal):
        $progress = min(100, $goal['target_amount'] > 0 ? ($goal['current_amount'] / $goal['target_amount']) * 100 : 0);
    ?>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($goal['name']); ?></h5>
                    <span class="badge bg-secondary">Target <?= htmlspecialchars($goal['target_date']); ?></span>
                </div>
                <p class="mb-2 text-muted">$<?= number_format($goal['current_amount'], 2); ?> saved of $<?= number_format($goal['target_amount'], 2); ?></p>
                <div class="progress progress-goal mb-2">
                    <div class="progress-bar" role="progressbar" style="width: <?= $progress; ?>%"></div>
                </div>
                <p class="mb-3 small text-muted"><?= nl2br(htmlspecialchars($goal['notes'] ?? '')); ?></p>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#goalModal"
                        data-id="<?= $goal['id']; ?>"
                        data-name="<?= htmlspecialchars($goal['name'], ENT_QUOTES); ?>"
                        data-target="<?= $goal['target_amount']; ?>"
                        data-current="<?= $goal['current_amount']; ?>"
                        data-date="<?= $goal['target_date']; ?>"
                        data-notes="<?= htmlspecialchars($goal['notes'] ?? '', ENT_QUOTES); ?>">
                        Update
                    </button>
                    <form method="post" onsubmit="return confirm('Delete this goal?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $goal['id']; ?>">
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$goals): ?>
    <div class="col-12 text-center text-muted">Create a savings goal to stay motivated.</div>
    <?php endif; ?>
</div>
<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Savings Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="goalId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="goalName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Amount</label>
                        <input type="number" step="0.01" name="target_amount" id="goalTarget" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Amount</label>
                        <input type="number" step="0.01" name="current_amount" id="goalCurrent" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" id="goalDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="goalNotes" class="form-control"></textarea>
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
const goalModal = document.getElementById('goalModal');
goalModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        goalModal.querySelector('form').reset();
        document.getElementById('goalId').value = '';
        return;
    }
    document.getElementById('goalId').value = button.getAttribute('data-id');
    document.getElementById('goalName').value = button.getAttribute('data-name');
    document.getElementById('goalTarget').value = button.getAttribute('data-target');
    document.getElementById('goalCurrent').value = button.getAttribute('data-current');
    document.getElementById('goalDate').value = button.getAttribute('data-date');
    document.getElementById('goalNotes').value = button.getAttribute('data-notes');
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

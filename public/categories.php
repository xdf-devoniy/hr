<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        delete_category((int) $_POST['id'], $user['id']);
    } else {
        save_category([
            'id' => $_POST['id'] ?? null,
            'user_id' => $user['id'],
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'color' => $_POST['color'] ?? '#0d6efd'
        ]);
    }
    redirect('/public/categories.php');
}
$categories = get_categories($user['id']);
include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Categories</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Add Category</button>
</div>
<div class="row g-3">
    <?php foreach ($categories as $category): ?>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <span class="badge" style="background-color: <?= htmlspecialchars($category['color']); ?>;">&nbsp;</span>
                            <?= htmlspecialchars($category['name']); ?>
                        </h5>
                        <small class="text-muted text-uppercase"><?= htmlspecialchars($category['type']); ?></small>
                    </div>
                    <div class="table-actions">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#categoryModal"
                            data-id="<?= $category['id']; ?>"
                            data-name="<?= htmlspecialchars($category['name'], ENT_QUOTES); ?>"
                            data-type="<?= $category['type']; ?>"
                            data-color="<?= htmlspecialchars($category['color']); ?>">
                            Edit
                        </button>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $category['id']; ?>">
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$categories): ?>
    <div class="col-12 text-center text-muted">Create categories to organize your spending.</div>
    <?php endif; ?>
</div>
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="categoryId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="categoryName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="categoryType" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" id="categoryColor" class="form-control form-control-color" value="#0d6efd">
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
const categoryModal = document.getElementById('categoryModal');
categoryModal?.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    if (!button) {
        categoryModal.querySelector('form').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryColor').value = '#0d6efd';
        return;
    }
    document.getElementById('categoryId').value = button.getAttribute('data-id');
    document.getElementById('categoryName').value = button.getAttribute('data-name');
    document.getElementById('categoryType').value = button.getAttribute('data-type');
    document.getElementById('categoryColor').value = button.getAttribute('data-color') || '#0d6efd';
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../models/finance.php';
$user = current_user();
$settingsStmt = $pdo->prepare('SELECT * FROM settings WHERE user_id = ?');
$settingsStmt->execute([$user['id']]);
$settingsRow = $settingsStmt->fetch();
$settings = $settingsRow ?: ['currency' => 'USD', 'locale' => 'en_US', 'notifications' => 1, 'dark_mode' => 0];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'currency' => $_POST['currency'],
        'locale' => $_POST['locale'],
        'notifications' => isset($_POST['notifications']) ? 1 : 0,
        'dark_mode' => isset($_POST['dark_mode']) ? 1 : 0
    ];
    if ($settingsRow) {
        $stmt = $pdo->prepare('UPDATE settings SET currency = ?, locale = ?, notifications = ?, dark_mode = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$settings['currency'], $settings['locale'], $settings['notifications'], $settings['dark_mode'], $settingsRow['id'], $user['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO settings (user_id, currency, locale, notifications, dark_mode, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE currency = VALUES(currency), locale = VALUES(locale), notifications = VALUES(notifications), dark_mode = VALUES(dark_mode), updated_at = NOW()');
        $stmt->execute([$user['id'], $settings['currency'], $settings['locale'], $settings['notifications'], $settings['dark_mode']]);
    }
    $settingsStmt->execute([$user['id']]);
    $settingsRow = $settingsStmt->fetch();
    $settings = $settingsRow ?: $settings;
    $message = 'Settings saved successfully.';
}
include __DIR__ . '/../includes/header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Preferences</div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select" required>
                            <?php foreach (['USD','EUR','GBP','CAD','AUD','JPY','INR'] as $currency): ?>
                            <option value="<?= $currency; ?>" <?= $settings['currency'] === $currency ? 'selected' : ''; ?>><?= $currency; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Locale</label>
                        <select name="locale" class="form-select" required>
                            <?php foreach (['en_US','en_GB','fr_FR','de_DE','es_ES','hi_IN'] as $locale): ?>
                            <option value="<?= $locale; ?>" <?= $settings['locale'] === $locale ? 'selected' : ''; ?>><?= $locale; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notifications" name="notifications" <?= $settings['notifications'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="notifications">Email reminders for bills and budgets</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="darkMode" name="dark_mode" <?= $settings['dark_mode'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="darkMode">Enable dark mode (beta)</label>
                    </div>
                    <button class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header">Profile</div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                <p class="mb-0 text-muted">Member since <?= date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">Data Management</div>
            <div class="card-body">
                <p class="text-muted">Export your transactions as CSV for backup or analysis.</p>
                <form method="post" action="<?= url_for('public/tools/export.php'); ?>">
                    <button class="btn btn-outline-primary w-100">Export Transactions</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

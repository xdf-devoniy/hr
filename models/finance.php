<?php
require_once __DIR__ . '/../config/config.php';

function get_categories(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY type, name');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_category(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE categories SET name = ?, type = ?, color = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['name'], $data['type'], $data['color'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, type, color, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['name'], $data['type'], $data['color']]);
    }
}

function delete_category(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_accounts(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE user_id = ? ORDER BY type, name');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_account(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE accounts SET name = ?, type = ?, balance = ?, interest_rate = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['name'], $data['type'], $data['balance'], $data['interest_rate'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO accounts (user_id, name, type, balance, interest_rate, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['name'], $data['type'], $data['balance'], $data['interest_rate']]);
    }
}

function delete_account(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM accounts WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_budgets(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT b.*, c.name AS category_name FROM budgets b JOIN categories c ON b.category_id = c.id WHERE b.user_id = ? ORDER BY period_start DESC');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_budget(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE budgets SET category_id = ?, amount = ?, period_start = ?, period_end = ?, notes = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['category_id'], $data['amount'], $data['period_start'], $data['period_end'], $data['notes'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO budgets (user_id, category_id, amount, period_start, period_end, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['category_id'], $data['amount'], $data['period_start'], $data['period_end'], $data['notes']]);
    }
}

function delete_budget(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM budgets WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_transactions(int $user_id, array $filters = []): array
{
    global $pdo;
    $sql = 'SELECT t.*, c.name AS category_name, a.name AS account_name FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN accounts a ON t.account_id = a.id
            WHERE t.user_id = ?';
    $params = [$user_id];

    if (!empty($filters['category_id'])) {
        $sql .= ' AND t.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['account_id'])) {
        $sql .= ' AND t.account_id = ?';
        $params[] = $filters['account_id'];
    }
    if (!empty($filters['start_date'])) {
        $sql .= ' AND t.date >= ?';
        $params[] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= ' AND t.date <= ?';
        $params[] = $filters['end_date'];
    }
    if (!empty($filters['type'])) {
        $sql .= ' AND t.type = ?';
        $params[] = $filters['type'];
    }

    $sql .= ' ORDER BY t.date DESC, t.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function save_transaction(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE transactions SET account_id = ?, category_id = ?, type = ?, amount = ?, date = ?, merchant = ?, notes = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['account_id'], $data['category_id'], $data['type'], $data['amount'], $data['date'], $data['merchant'], $data['notes'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, category_id, type, amount, date, merchant, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['account_id'], $data['category_id'], $data['type'], $data['amount'], $data['date'], $data['merchant'], $data['notes']]);
    }
}

function delete_transaction(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_recurring_transactions(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT rt.*, c.name AS category_name, a.name AS account_name FROM recurring_transactions rt
            LEFT JOIN categories c ON rt.category_id = c.id
            LEFT JOIN accounts a ON rt.account_id = a.id
            WHERE rt.user_id = ? ORDER BY rt.next_run ASC');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_recurring_transaction(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE recurring_transactions SET account_id = ?, category_id = ?, type = ?, amount = ?, frequency = ?, next_run = ?, description = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['account_id'], $data['category_id'], $data['type'], $data['amount'], $data['frequency'], $data['next_run'], $data['description'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO recurring_transactions (user_id, account_id, category_id, type, amount, frequency, next_run, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['account_id'], $data['category_id'], $data['type'], $data['amount'], $data['frequency'], $data['next_run'], $data['description']]);
    }
}

function delete_recurring_transaction(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM recurring_transactions WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_goals(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM savings_goals WHERE user_id = ? ORDER BY target_date');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_goal(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE savings_goals SET name = ?, target_amount = ?, target_date = ?, current_amount = ?, notes = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['name'], $data['target_amount'], $data['target_date'], $data['current_amount'], $data['notes'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO savings_goals (user_id, name, target_amount, target_date, current_amount, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['name'], $data['target_amount'], $data['target_date'], $data['current_amount'], $data['notes']]);
    }
}

function delete_goal(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM savings_goals WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_bills(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM bills WHERE user_id = ? ORDER BY due_date');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function save_bill(array $data): void
{
    global $pdo;
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare('UPDATE bills SET name = ?, amount = ?, due_date = ?, frequency = ?, auto_pay = ?, notes = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$data['name'], $data['amount'], $data['due_date'], $data['frequency'], $data['auto_pay'], $data['notes'], $data['id'], $data['user_id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO bills (user_id, name, amount, due_date, frequency, auto_pay, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$data['user_id'], $data['name'], $data['amount'], $data['due_date'], $data['frequency'], $data['auto_pay'], $data['notes']]);
    }
}

function delete_bill(int $id, int $user_id): void
{
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM bills WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user_id]);
}

function get_net_worth(int $user_id): float
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT SUM(balance) as total FROM accounts WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return (float) ($stmt->fetch()['total'] ?? 0);
}

function get_cash_flow(int $user_id, string $start, string $end): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT type, SUM(amount) as total FROM transactions WHERE user_id = ? AND date BETWEEN ? AND ? GROUP BY type');
    $stmt->execute([$user_id, $start, $end]);
    $data = ['income' => 0, 'expense' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $data[$row['type']] = (float) $row['total'];
    }
    return $data;
}

function get_category_summary(int $user_id, string $start, string $end): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT c.name, c.type, SUM(t.amount) as total FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? AND t.date BETWEEN ? AND ? GROUP BY c.id ORDER BY total DESC');
    $stmt->execute([$user_id, $start, $end]);
    return $stmt->fetchAll();
}

function get_monthly_trends(int $user_id): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense FROM transactions WHERE user_id = ? GROUP BY month ORDER BY month DESC LIMIT 12");
    $stmt->execute([$user_id]);
    return array_reverse($stmt->fetchAll());
}

function import_transactions(int $user_id, array $rows): array
{
    global $pdo;
    $inserted = 0;
    $errors = [];
    foreach ($rows as $index => $row) {
        if (count($row) < 6) {
            $errors[] = 'Row ' . ($index + 1) . ' missing columns';
            continue;
        }
        [$date, $amount, $type, $category_name, $account_name, $merchant, $notes] = array_pad($row, 7, null);
        if (!in_array($type, ['income', 'expense'])) {
            $errors[] = 'Row ' . ($index + 1) . ' invalid type';
            continue;
        }
        $pdo->beginTransaction();
        try {
            $category = ensure_category($user_id, $category_name, $type);
            $account = ensure_account($user_id, $account_name);
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, category_id, type, amount, date, merchant, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$user_id, $account['id'], $category['id'], $type, $amount, $date, $merchant, $notes]);
            $pdo->commit();
            $inserted++;
        } catch (Throwable $th) {
            $pdo->rollBack();
            $errors[] = 'Row ' . ($index + 1) . ': ' . $th->getMessage();
        }
    }
    return ['inserted' => $inserted, 'errors' => $errors];
}

function ensure_category(int $user_id, string $name, string $type): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = ? AND name = ?');
    $stmt->execute([$user_id, $name]);
    $category = $stmt->fetch();
    if (!$category) {
        $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, type, color, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$user_id, $name, $type, '#0d6efd']);
        $category = ['id' => $pdo->lastInsertId(), 'name' => $name, 'type' => $type];
    }
    return $category;
}

function ensure_account(int $user_id, string $name): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE user_id = ? AND name = ?');
    $stmt->execute([$user_id, $name]);
    $account = $stmt->fetch();
    if (!$account) {
        $stmt = $pdo->prepare('INSERT INTO accounts (user_id, name, type, balance, interest_rate, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$user_id, $name, 'checking', 0, 0]);
        $account = ['id' => $pdo->lastInsertId(), 'name' => $name];
    }
    return $account;
}

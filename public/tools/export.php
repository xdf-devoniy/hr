<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../models/finance.php';
$user = current_user();
$transactions = get_transactions($user['id']);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions.csv"');
$fp = fopen('php://output', 'w');
fputcsv($fp, ['Date', 'Amount', 'Type', 'Category', 'Account', 'Merchant', 'Notes']);
foreach ($transactions as $transaction) {
    fputcsv($fp, [
        $transaction['date'],
        $transaction['amount'],
        $transaction['type'],
        $transaction['category_name'],
        $transaction['account_name'],
        $transaction['merchant'],
        $transaction['notes']
    ]);
}
fclose($fp);
exit;

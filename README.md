# BudgetMaster PHP

BudgetMaster is a feature-rich personal budgeting web application built with PHP, MySQL and Bootstrap. It helps you manage accounts, transactions, budgets, recurring payments, bills, savings goals and financial reports from a single dashboard.

## Features

- User authentication with registration and login
- Dashboard with KPIs, category insights, bills and savings progress
- Unlimited accounts with balances and interest tracking
- Income and expense categories with custom colors
- Monthly budgets with progress tracking vs. actual spending
- Transaction management with filtering, CSV import and export
- Recurring transactions scheduler (income or expenses)
- Bills and subscriptions reminder board with auto-pay flag
- Savings goals with progress bars
- Financial reports with cash flow summary, category breakdown, trends and transaction drill-down
- User settings for currency, locale, notifications and dark mode preference

## Project Structure

```
config/          Database configuration
includes/        Layout and authentication helpers
models/          Data access helpers for finance entities
public/          Application pages (dashboard, CRUD screens, reports)
assets/          CSS and JavaScript assets
samples/         CSV templates
sql/             Database schema
```

## Requirements

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10+
- Web server (Apache/Nginx) configured to serve the `public` directory

## Installation

1. Clone the repository to your web server root.
2. Create a MySQL database (default name `budget_app`).
3. Import the schema:
   ```sql
   SOURCE sql/schema.sql;
   ```
4. Configure database credentials by setting environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` or editing `config/config.php`.
5. Point your web server document root to the `public` directory.
6. If the project is served from a subdirectory (for example `http://localhost/bdgt/public/index.php`), set the `APP_BASE_URL`
   environment variable or ensure your server exposes the correct path so links resolve properly.
7. Visit `http://localhost/bdgt/public/register.php` (adjusting the host/path as needed) to create your first account.

## CSV Import Format

Use the sample in `samples/transactions_template.csv`. The expected columns are:

1. Date (YYYY-MM-DD)
2. Amount (decimal)
3. Type (`income` or `expense`)
4. Category name
5. Account name
6. Merchant
7. Notes

## Security Notes

- Passwords are stored using PHP's `password_hash` with bcrypt.
- Basic session-based authentication is provided; consider adding HTTPS and hardened session management for production.
- User input is sanitized for output using `htmlspecialchars`.

## License

This project is provided as-is for demonstration purposes.

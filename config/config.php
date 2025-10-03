<?php
session_start();

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'budget_app');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function base_path(string $path = ''): string
{
    return rtrim(dirname(__DIR__), '/') . ($path ? '/' . ltrim($path, '/') : '');
}

function detect_base_url(): string
{
    $configured = getenv('APP_BASE_URL');
    if ($configured !== false && $configured !== '') {
        return rtrim($configured, '/');
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $publicSegment = '/public/';
    $position = strpos($scriptName, $publicSegment);

    if ($position !== false) {
        $base = substr($scriptName, 0, $position);
        return rtrim($base, '/');
    }

    return '';
}

define('APP_BASE_URL', detect_base_url());

function url_for(string $path = ''): string
{
    $base = APP_BASE_URL;
    $normalized = ltrim($path, '/');
    $suffix = $normalized !== '' ? '/' . $normalized : '';

    return ($base !== '' ? $base : '') . $suffix;
}

function asset_url(string $path): string
{
    return url_for($path);
}

function currency_symbol(string $currency): string
{
    $map = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'JPY' => '¥',
        'INR' => '₹',
        'SUM' => "so'm",
    ];

    $currency = strtoupper($currency);

    return $map[$currency] ?? $currency . ' ';
}

function format_currency(float $amount, string $currency, ?int $precision = null): string
{
    $currency = strtoupper($currency);
    $precision = $precision ?? ($currency === 'JPY' ? 0 : 2);
    $isNegative = $amount < 0;
    $absolute = abs($amount);
    $formatted = number_format($absolute, $precision);
    $symbol = currency_symbol($currency);

    $positionAfter = in_array($currency, ['INR', 'SUM'], true);
    $value = $positionAfter ? $formatted . ' ' . $symbol : $symbol . $formatted;

    return $isNegative ? '-' . $value : $value;
}

function redirect(string $path): void
{
    if (!preg_match('/^https?:\/\//i', $path)) {
        $path = url_for($path);
    }

    header('Location: ' . $path);
    exit;
}

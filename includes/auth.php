<?php
require_once __DIR__ . '/../config/config.php';

function current_user(): ?array
{
    if (!empty($_SESSION['user_id'])) {
        global $pdo;
        static $user;
        if (!$user) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
        return $user ?: null;
    }
    return null;
}

function require_auth(): void
{
    if (!current_user()) {
        redirect('public/login.php');
    }
}

function login(string $email, string $password): bool
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function register_user(array $data): array
{
    global $pdo;
    $errors = [];
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }
    if ($errors) {
        return $errors;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['Email already registered.'];
    }

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT)
    ]);

    return [];
}

function logout(): void
{
    session_destroy();
    redirect('public/login.php');
}

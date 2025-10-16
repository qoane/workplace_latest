<?php
require __DIR__ . '/../includes/init.php';

if (is_admin_authenticated()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login_admin($pdo, $username, $password)) {
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    }
    $error = 'Invalid credentials.';
}

$siteName = site_setting($pdo, 'site_name', 'Workplace Solutions');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($siteName) ?> &mdash; Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    <style>body { max-width: 420px; margin: 4rem auto; padding: 2rem; }</style>
</head>
<body>
    <h1><?= h($siteName) ?> &mdash; Admin Login</h1>
    <?php if ($error): ?><article class="contrast"><?= h($error) ?></article><?php endif; ?>
    <form method="post" action="">
        <label>Username<input type="text" name="username" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button type="submit">Login</button>
    </form>
</body>
</html>

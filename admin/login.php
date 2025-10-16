<?php
require __DIR__ . '/../includes/init.php';

if (is_admin_authenticated()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$error = '';
$username = '';
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
$logoSetting = site_setting($pdo, 'logo_path', '');
$logoAsset = $logoSetting !== '' ? $logoSetting : 'images/logo.svg';
$logoUrl = base_url($logoAsset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($siteName) ?> &mdash; Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/admin-login.css') ?>">
</head>
<body class="wp-login">
    <div class="login-wrapper">
        <div id="login">
            <div class="login-logo">
                <a href="<?= base_url('/') ?>" aria-label="Back to homepage">
                    <?php if ($logoAsset): ?>
                        <img src="<?= h($logoUrl) ?>" alt="<?= h($siteName) ?> logo">
                    <?php else: ?>
                        <span><?= h($siteName) ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <?php if ($error): ?>
                <div class="login-message message-error" role="alert">
                    <strong>Error:</strong> <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form name="loginform" id="loginform" method="post" action="">
                <p>
                    <label for="user_login">Username</label>
                    <input type="text" name="username" id="user_login" class="input" value="<?= h($username) ?>" size="20" autocomplete="username" required>
                </p>
                <p>
                    <label for="user_pass">Password</label>
                    <input type="password" name="password" id="user_pass" class="input" size="20" autocomplete="current-password" required>
                </p>
                <p class="forgetmenot">
                    <label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever" disabled> Remember Me</label>
                </p>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="wp-submit">Log In</button>
                </p>
            </form>

            <p id="backtoblog"><a href="<?= base_url('/') ?>">&larr; Back to <?= h($siteName) ?></a></p>
        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('user_login');
            if (input) {
                input.focus();
            }
        });
    </script>
</body>
</html>

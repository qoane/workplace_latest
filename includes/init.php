<?php
if (!file_exists(__DIR__ . '/../config.php')) {
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "Configuration file missing. Please run install.php.\n");
    } else {
        header('Location: install.php');
    }
    exit;
}

$config = require __DIR__ . '/../config.php';

session_name($config['app']['session_name'] ?? 'workplace_admin');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$pdo = get_pdo($config['db']);

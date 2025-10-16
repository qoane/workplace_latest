<?php require __DIR__ . '/../includes/init.php'; ?>
<?php require_admin(); ?>
<?php
$siteName = site_setting($pdo, 'site_name', 'Workplace Solutions');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($siteName) ?> &mdash; Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    <style>
        body { padding: 2rem; }
        nav a { margin-right: 1rem; }
        textarea { min-height: 250px; }
        .menu-tree ul { list-style: none; padding-left: 1.5rem; }
    </style>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.querySelector('.rich-editor')) {
                tinymce.init({
                    selector: '.rich-editor',
                    plugins: 'link lists table code image media',
                    toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | code',
                    height: 400,
                    convert_urls: false
                });
            }
        });
    </script>
</head>
<body>
<header>
    <h1><?= h($siteName) ?> &mdash; Admin</h1>
    <nav>
        <a href="<?= base_url('admin/dashboard.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/pages.php') ?>">Pages</a>
        <a href="<?= base_url('admin/site_sections.php') ?>">Site Sections</a>
        <a href="<?= base_url('admin/menus.php') ?>">Menus</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
        <a href="<?= base_url('admin/logout.php') ?>">Logout</a>
    </nav>
</header>
<main>

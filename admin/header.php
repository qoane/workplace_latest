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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-oBWdlEYUoMRn8DcRbTy9VnE3NQIdlZwIYdMb9RfMge+leP4YDbi0wzfopWn7UXQ8sGdzAFhlqmcuP5HTh3YtKQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>" />
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editors = document.querySelectorAll('.rich-editor');
            if (!editors.length || !window.ClassicEditor) {
                return;
            }

            editors.forEach((textarea) => {
                ClassicEditor.create(textarea, {
                    toolbar: {
                        items: [
                            'undo',
                            'redo',
                            '|',
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            '|',
                            'bulletedList',
                            'numberedList',
                            '|',
                            'blockQuote',
                            'insertTable'
                        ]
                    },
                    link: {
                        decorators: {
                            addTargetToExternalLinks: {
                                mode: 'automatic',
                                callback: (url) => /^https?:\/\//.test(url),
                                attributes: {
                                    target: '_blank',
                                    rel: 'noopener'
                                }
                            }
                        }
                    }
                }).catch((error) => console.error('CKEditor init failed', error));
            });
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

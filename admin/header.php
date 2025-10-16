<?php require __DIR__ . '/../includes/init.php'; ?>
<?php require_admin(); ?>
<?php
$siteName = site_setting($pdo, 'site_name', 'Workplace Solutions');
$logoSetting = site_setting($pdo, 'logo_path', '');
$logoAsset = $logoSetting !== '' ? $logoSetting : 'images/logo.svg';
$logoUrl = base_url($logoAsset);
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$navItems = [
    ['label' => 'Dashboard', 'icon' => 'fa-gauge', 'path' => 'admin/dashboard.php'],
    ['label' => 'Pages', 'icon' => 'fa-file-lines', 'path' => 'admin/pages.php'],
    ['label' => 'Site Sections', 'icon' => 'fa-layer-group', 'path' => 'admin/site_sections.php'],
    ['label' => 'Menus', 'icon' => 'fa-bars', 'path' => 'admin/menus.php'],
    ['label' => 'Settings', 'icon' => 'fa-gear', 'path' => 'admin/settings.php'],
];
$adminUser = $_SESSION['admin_username'] ?? 'Administrator';
$initials = strtoupper(substr($adminUser, 0, 1));
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
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <a href="<?= base_url('admin/dashboard.php') ?>" class="brand-link">
                <img src="<?= h($logoUrl) ?>" alt="<?= h($siteName) ?> logo" class="brand-logo">
                <span class="brand-name"><?= h($siteName) ?></span>
            </a>
            <a class="visit-site" href="<?= base_url('/') ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <span>View site</span>
            </a>
        </div>
        <nav class="admin-nav" aria-label="Admin navigation">
            <?php foreach ($navItems as $item):
                $isActive = $currentScript === basename($item['path']);
            ?>
                <a class="nav-item<?= $isActive ? ' is-active' : '' ?>" href="<?= base_url($item['path']) ?>">
                    <i class="fa-solid <?= h($item['icon']) ?>" aria-hidden="true"></i>
                    <span><?= h($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-meta">
            <p class="sidebar-version">CMS status: <span class="badge badge-live">Live</span></p>
            <p class="sidebar-help">Need assistance? <a href="mailto:info@workplacesolutions.co.ls">Contact support</a></p>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-content">
                <div class="topbar-context">
                    <h1><?= h($siteName) ?> Admin Console</h1>
                    <p>Manage your pages, menus, sections and branding from a single, unified hub.</p>
                </div>
                <div class="topbar-actions">
                    <a class="btn btn-primary" href="<?= base_url('admin/page_edit.php') ?>">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <span>New Page</span>
                    </a>
                    <a class="btn btn-secondary" href="<?= base_url('admin/settings.php') ?>">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                        <span>Site Settings</span>
                    </a>
                </div>
                <div class="topbar-user" aria-label="Administrator profile">
                    <span class="user-avatar" aria-hidden="true"><?= h($initials) ?></span>
                    <div class="user-meta">
                        <span class="user-name"><?= h($adminUser) ?></span>
                        <a class="user-logout" href="<?= base_url('admin/logout.php') ?>">Sign out</a>
                    </div>
                </div>
            </div>
        </header>
        <main class="admin-content">

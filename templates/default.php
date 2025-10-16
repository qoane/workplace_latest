<?php
/** @var array $page */
/** @var array $pageSections */
/** @var array $siteSections */
/** @var array|null $primaryMenu */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($page['meta_title'] ?: $page['title']) ?></title>
    <?php if (!empty($page['meta_description'])): ?>
        <meta name="description" content="<?= h($page['meta_description']) ?>" />
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-oBWdlEYUoMRn8DcRbTy9VnE3NQIdlZwIYdMb9RfMge+leP4YDbi0wzfopWn7UXQ8sGdzAFhlqmcuP5HTh3YtKQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>" />
    <?= $siteSections['head_assets'] ?? '' ?>
</head>
<body class="<?= h($page['body_class'] ?? '') ?>">
<?= $siteSections['body_start'] ?? '' ?>
<?= $siteSections['top_bar'] ?? '' ?>
<nav class="navbar">
    <div class="container">
        <a href="<?= base_url('index.php') ?>" class="logo">
            <img src="<?= h(site_setting($pdo, 'logo_path', 'images/logo.png')) ?>" alt="<?= h(site_setting($pdo, 'site_name', $page['title'])) ?>" />
        </a>
        <button class="nav-toggle" id="navToggle"><span></span><span></span><span></span></button>
        <?= isset($primaryMenu['items']) ? render_menu($primaryMenu['items']) : '' ?>
    </div>
</nav>
<main>
    <?= $pageSections['body'] ?? '' ?>
</main>
<?= $siteSections['footer'] ?? '' ?>
<?= $siteSections['body_end'] ?? '' ?>
</body>
</html>

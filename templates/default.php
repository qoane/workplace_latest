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

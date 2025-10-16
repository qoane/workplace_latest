<?php include __DIR__ . '/header.php'; ?>
<section>
    <h2>Welcome, <?= h($_SESSION['admin_username'] ?? 'admin') ?></h2>
    <p>Use the navigation above to manage the website content, menus, and settings.</p>
</section>
<?php include __DIR__ . '/footer.php'; ?>

<?php include __DIR__ . '/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['site_name', 'logo_path'] as $key) {
        $stmt = $pdo->prepare('UPDATE site_settings SET value=:value WHERE `key`=:key');
        $stmt->execute([
            'value' => trim($_POST[$key] ?? ''),
            'key' => $key,
        ]);
    }
    echo '<article class="contrast">Settings saved.</article>';
}

$settings = [
    'site_name' => site_setting($pdo, 'site_name', ''),
    'logo_path' => site_setting($pdo, 'logo_path', ''),
];
?>
<section>
    <h2>Site Settings</h2>
    <form method="post">
        <label>Site Name<input type="text" name="site_name" value="<?= h($settings['site_name']) ?>" required></label>
        <label>Logo Path<input type="text" name="logo_path" value="<?= h($settings['logo_path']) ?>" placeholder="images/logo.png"></label>
        <button type="submit">Save Settings</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>

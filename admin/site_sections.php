<?php include __DIR__ . '/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['sections'] as $slug => $content) {
        $stmt = $pdo->prepare('UPDATE site_sections SET content=:content WHERE slug=:slug');
        $stmt->execute([
            'content' => $content,
            'slug' => $slug,
        ]);
    }
    echo '<article class="contrast">Site sections updated.</article>';
}

$sections = $pdo->query('SELECT * FROM site_sections ORDER BY slug')->fetchAll();
?>
<section>
    <h2>Site Sections</h2>
    <form method="post">
        <?php foreach ($sections as $section): ?>
            <article>
                <h3><?= h($section['slug']) ?></h3>
                <textarea class="rich-editor" name="sections[<?= h($section['slug']) ?>]"><?= h($section['content']) ?></textarea>
            </article>
        <?php endforeach; ?>
        <button type="submit">Save Changes</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>

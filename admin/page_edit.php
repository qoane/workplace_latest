<?php include __DIR__ . '/header.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page = [
    'title' => '',
    'slug' => '',
    'meta_title' => '',
    'meta_description' => '',
    'body_class' => '',
    'status' => 'draft',
    'template' => 'default',
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $page = $stmt->fetch();
    if (!$page) {
        echo '<p>Page not found.</p>';
        include __DIR__ . '/footer.php';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => sanitize_slug($_POST['slug'] ?? ''),
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'body_class' => trim($_POST['body_class'] ?? ''),
        'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
        'template' => trim($_POST['template'] ?? 'default'),
    ];

    if ($id) {
        $data['id'] = $id;
        $stmt = $pdo->prepare('UPDATE pages SET title=:title, slug=:slug, meta_title=:meta_title, meta_description=:meta_description, body_class=:body_class, status=:status, template=:template, updated_at=NOW() WHERE id=:id');
        $stmt->execute($data);
    } else {
        $stmt = $pdo->prepare('INSERT INTO pages (title, slug, meta_title, meta_description, body_class, status, template, created_at, updated_at) VALUES (:title, :slug, :meta_title, :meta_description, :body_class, :status, :template, NOW(), NOW())');
        $stmt->execute($data);
        $id = (int)$pdo->lastInsertId();
    }

    header('Location: ' . base_url('admin/page_sections.php?id=' . $id));
    exit;
}
?>
<section>
    <h2><?= $id ? 'Edit Page' : 'Create Page' ?></h2>
    <form method="post">
        <label>Title<input type="text" name="title" value="<?= h($page['title']) ?>" required></label>
        <label>Slug<input type="text" name="slug" value="<?= h($page['slug']) ?>" required></label>
        <label>Meta Title<input type="text" name="meta_title" value="<?= h($page['meta_title']) ?>"></label>
        <label>Meta Description<textarea name="meta_description" rows="3"><?= h($page['meta_description']) ?></textarea></label>
        <label>Body class<input type="text" name="body_class" value="<?= h($page['body_class']) ?>"></label>
        <label>Template
            <select name="template">
                <option value="default" <?= $page['template'] === 'default' ? 'selected' : '' ?>>Default</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </label>
        <button type="submit">Save</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>

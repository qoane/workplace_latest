<?php include __DIR__ . '/header.php'; ?>
<?php
$pages = $pdo->query('SELECT * FROM pages ORDER BY title')->fetchAll();
?>
<section>
    <header class="grid">
        <div><h2>Pages</h2></div>
        <div class="align-right"><a class="secondary" href="<?= base_url('admin/page_edit.php') ?>">Create Page</a></div>
    </header>
    <table>
        <thead>
            <tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($pages as $page): ?>
            <tr>
                <td><?= h($page['title']) ?></td>
                <td><?= h($page['slug']) ?></td>
                <td><?= h($page['status']) ?></td>
                <td><?= h($page['updated_at']) ?></td>
                <td>
                    <a href="<?= base_url('admin/page_edit.php?id=' . $page['id']) ?>">Edit</a> |
                    <a href="<?= base_url('admin/page_sections.php?id=' . $page['id']) ?>">Sections</a> |
                    <a href="<?= base_url('index.php?page=' . $page['slug']) ?>" target="_blank">View</a> |
                    <a href="<?= base_url('admin/page_delete.php?id=' . $page['id']) ?>" onclick="return confirm('Delete this page?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/footer.php'; ?>

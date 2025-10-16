<?php include __DIR__ . '/header.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM pages WHERE id = :id');
$stmt->execute(['id' => $id]);
$page = $stmt->fetch();
if (!$page) {
    echo '<p>Page not found.</p>';
    include __DIR__ . '/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        $existing = $_POST['existing'] ?? [];
        $sort = 0;
        foreach ($existing as $sectionId => $sectionData) {
            $sort++;
            if (!empty($sectionData['delete'])) {
                $stmt = $pdo->prepare('DELETE FROM page_sections WHERE id = :id AND page_id = :page_id');
                $stmt->execute(['id' => $sectionId, 'page_id' => $id]);
                continue;
            }
            $stmt = $pdo->prepare('UPDATE page_sections SET name=:name, content=:content, sort_order=:sort WHERE id=:id AND page_id=:page_id');
            $stmt->execute([
                'name' => trim($sectionData['name'] ?? ''),
                'content' => $sectionData['content'] ?? '',
                'sort' => $sort,
                'id' => $sectionId,
                'page_id' => $id,
            ]);
        }

        $newSections = $_POST['new'] ?? [];
        foreach ($newSections as $sectionData) {
            if (empty($sectionData['name']) && empty($sectionData['content'])) {
                continue;
            }
            $sort++;
            $stmt = $pdo->prepare('INSERT INTO page_sections (page_id, name, content, sort_order) VALUES (:page_id, :name, :content, :sort)');
            $stmt->execute([
                'page_id' => $id,
                'name' => trim($sectionData['name'] ?? ''),
                'content' => $sectionData['content'] ?? '',
                'sort' => $sort,
            ]);
        }

        $pdo->commit();
        header('Location: ' . base_url('admin/page_sections.php?id=' . $id));
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo '<article class="contrast">' . h($e->getMessage()) . '</article>';
    }
}

$stmt = $pdo->prepare('SELECT * FROM page_sections WHERE page_id = :id ORDER BY sort_order');
$stmt->execute(['id' => $id]);
$sections = $stmt->fetchAll();
?>
<section>
    <h2>Sections for <?= h($page['title']) ?></h2>
    <form method="post">
        <?php foreach ($sections as $section): ?>
            <article>
                <header class="grid">
                    <div>
                        <label>Section Name<input type="text" name="existing[<?= $section['id'] ?>][name]" value="<?= h($section['name']) ?>" required></label>
                    </div>
                    <div>
                        <label>Remove?
                            <input type="checkbox" name="existing[<?= $section['id'] ?>][delete]" value="1">
                        </label>
                    </div>
                </header>
                <textarea class="rich-editor" name="existing[<?= $section['id'] ?>][content]"><?= h($section['content']) ?></textarea>
            </article>
        <?php endforeach; ?>
        <article>
            <h3>Add New Section</h3>
            <label>Name<input type="text" name="new[0][name]"></label>
            <textarea class="rich-editor" name="new[0][content]"></textarea>
        </article>
        <button type="submit">Save Sections</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>

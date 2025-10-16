<?php include __DIR__ . '/header.php'; ?>
<?php
$menuStmt = $pdo->prepare('SELECT * FROM menus WHERE slug = :slug');
$menuStmt->execute(['slug' => 'primary']);
$menu = $menuStmt->fetch();
if (!$menu) {
    echo '<p>Primary menu missing.</p>';
    include __DIR__ . '/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_existing'])) {
        foreach ($_POST['items'] as $id => $itemData) {
            if (!empty($itemData['delete'])) {
                $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = :id');
                $stmt->execute(['id' => $id]);
                continue;
            }
            $stmt = $pdo->prepare('UPDATE menu_items SET label=:label, url=:url, url_type=:url_type, target=:target, css_class=:css_class, parent_id=:parent_id, sort_order=:sort_order WHERE id=:id');
            $stmt->execute([
                'label' => trim($itemData['label'] ?? ''),
                'url' => trim($itemData['url'] ?? ''),
                'url_type' => $itemData['url_type'] === 'external' ? 'external' : 'internal',
                'target' => trim($itemData['target'] ?? ''),
                'css_class' => trim($itemData['css_class'] ?? ''),
                'parent_id' => $itemData['parent_id'] !== '' ? (int)$itemData['parent_id'] : null,
                'sort_order' => (int)($itemData['sort_order'] ?? 0),
                'id' => $id,
            ]);
        }
        echo '<article class="contrast">Menu updated.</article>';
    } elseif (isset($_POST['create_item'])) {
        $stmt = $pdo->prepare('INSERT INTO menu_items (menu_id, parent_id, label, url, url_type, target, css_class, sort_order) VALUES (:menu_id, :parent_id, :label, :url, :url_type, :target, :css_class, :sort_order)');
        $stmt->execute([
            'menu_id' => $menu['id'],
            'parent_id' => $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null,
            'label' => trim($_POST['label'] ?? ''),
            'url' => trim($_POST['url'] ?? ''),
            'url_type' => $_POST['url_type'] === 'external' ? 'external' : 'internal',
            'target' => trim($_POST['target'] ?? ''),
            'css_class' => trim($_POST['css_class'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);
        echo '<article class="contrast">Menu item added.</article>';
    }
}

$stmt = $pdo->prepare('SELECT * FROM menu_items WHERE menu_id = :menu_id ORDER BY sort_order');
$stmt->execute(['menu_id' => $menu['id']]);
$items = $stmt->fetchAll();
?>
<section>
    <h2>Menus</h2>
    <h3>Primary Navigation</h3>
    <form method="post">
        <input type="hidden" name="save_existing" value="1">
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>URL</th>
                    <th>Type</th>
                    <th>Parent</th>
                    <th>Target</th>
                    <th>CSS Class</th>
                    <th>Sort</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><input type="text" name="items[<?= $item['id'] ?>][label]" value="<?= h($item['label']) ?>" required></td>
                    <td><input type="text" name="items[<?= $item['id'] ?>][url]" value="<?= h($item['url']) ?>" required></td>
                    <td>
                        <select name="items[<?= $item['id'] ?>][url_type]">
                            <option value="internal" <?= $item['url_type'] === 'internal' ? 'selected' : '' ?>>Internal</option>
                            <option value="external" <?= $item['url_type'] === 'external' ? 'selected' : '' ?>>External</option>
                        </select>
                    </td>
                    <td>
                        <select name="items[<?= $item['id'] ?>][parent_id]">
                            <option value="">None</option>
                            <?php foreach ($items as $potentialParent): if ($potentialParent['id'] == $item['id']) continue; ?>
                                <option value="<?= $potentialParent['id'] ?>" <?= $item['parent_id'] == $potentialParent['id'] ? 'selected' : '' ?>><?= h($potentialParent['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="items[<?= $item['id'] ?>][target]" value="<?= h($item['target']) ?>" placeholder="_blank"></td>
                    <td><input type="text" name="items[<?= $item['id'] ?>][css_class]" value="<?= h($item['css_class']) ?>"></td>
                    <td><input type="number" name="items[<?= $item['id'] ?>][sort_order]" value="<?= h($item['sort_order']) ?>" style="width:80px;"></td>
                    <td><input type="checkbox" name="items[<?= $item['id'] ?>][delete]" value="1"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Save Menu</button>
    </form>

    <h3>Add Menu Item</h3>
    <form method="post">
        <input type="hidden" name="create_item" value="1">
        <label>Label<input type="text" name="label" required></label>
        <label>URL<input type="text" name="url" required placeholder="index.php?page=index"></label>
        <label>Type
            <select name="url_type">
                <option value="internal">Internal</option>
                <option value="external">External</option>
            </select>
        </label>
        <label>Parent
            <select name="parent_id">
                <option value="">None</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= h($item['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Target<input type="text" name="target" placeholder="_blank"></label>
        <label>CSS Class<input type="text" name="css_class" placeholder="btn btn-primary"></label>
        <label>Sort Order<input type="number" name="sort_order" value="0"></label>
        <button type="submit">Add Item</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>

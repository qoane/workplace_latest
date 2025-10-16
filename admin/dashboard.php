<?php include __DIR__ . '/header.php'; ?>
<?php
$quickDraftMessage = '';
$quickDraftError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'quick-draft') {
    $draftTitle = trim($_POST['draft_title'] ?? '');
    $draftSlugInput = trim($_POST['draft_slug'] ?? '');
    if ($draftTitle === '') {
        $quickDraftError = 'Please provide a title for your draft page.';
    } else {
        $draftSlug = $draftSlugInput !== '' ? sanitize_slug($draftSlugInput) : sanitize_slug($draftTitle);
        $stmt = $pdo->prepare('INSERT INTO pages (title, slug, meta_title, meta_description, body_class, status, template, created_at, updated_at) VALUES (:title, :slug, :meta_title, :meta_description, :body_class, :status, :template, NOW(), NOW())');
        $stmt->execute([
            'title' => $draftTitle,
            'slug' => $draftSlug,
            'meta_title' => $draftTitle,
            'meta_description' => '',
            'body_class' => '',
            'status' => 'draft',
            'template' => 'default',
        ]);
        $quickDraftMessage = 'Draft created successfully. You can continue editing it from the Pages screen.';
    }
}

$totalPages = (int)$pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn();
$publishedPages = (int)$pdo->query("SELECT COUNT(*) FROM pages WHERE status = 'published'")->fetchColumn();
$draftPages = (int)$pdo->query("SELECT COUNT(*) FROM pages WHERE status = 'draft'")->fetchColumn();
$totalMenus = (int)$pdo->query('SELECT COUNT(*) FROM menus')->fetchColumn();
$menuItems = (int)$pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
$siteSections = (int)$pdo->query('SELECT COUNT(*) FROM site_sections')->fetchColumn();

$recentPagesStmt = $pdo->query('SELECT id, title, slug, status, updated_at FROM pages ORDER BY updated_at DESC LIMIT 5');
$recentPages = $recentPagesStmt->fetchAll();

$settingsSnapshot = [
    'Site name' => site_setting($pdo, 'site_name', 'Workplace Solutions'),
    'Logo path' => site_setting($pdo, 'logo_path', 'images/logo.svg'),
];
?>

<section class="card-panel welcome-card">
    <h2>Welcome back, <?= h($adminUser) ?>!</h2>
    <p>You&apos;re in full control. Use the cards below to monitor content health and jump straight into your most common tasks.</p>
    <div class="quick-actions" aria-label="Quick actions">
        <a href="<?= base_url('admin/page_edit.php') ?>">Create a new page</a>
        <a href="<?= base_url('admin/menus.php') ?>">Organise the menu</a>
        <a href="<?= base_url('admin/site_sections.php') ?>">Update site sections</a>
        <a href="<?= base_url('admin/settings.php') ?>">Review branding</a>
    </div>
</section>

<section class="dashboard-grid" aria-label="Site status">
    <article class="stat-card">
        <h3>Total Pages</h3>
        <span class="stat-value"><?= h($totalPages) ?></span>
        <span class="stat-meta">Published: <?= h($publishedPages) ?> &middot; Drafts: <?= h($draftPages) ?></span>
    </article>
    <article class="stat-card">
        <h3>Menus</h3>
        <span class="stat-value"><?= h($totalMenus) ?></span>
        <span class="stat-meta">Menu items configured: <?= h($menuItems) ?></span>
    </article>
    <article class="stat-card">
        <h3>Site Sections</h3>
        <span class="stat-value"><?= h($siteSections) ?></span>
        <span class="stat-meta">Reusable content blocks ready to publish</span>
    </article>
</section>

<div class="dashboard-columns">
    <article class="card-panel">
        <header>
            <h2>Quick Draft</h2>
        </header>
        <p>Capture an idea in seconds. Your draft is saved instantly and can be refined later in the page editor.</p>
        <?php if ($quickDraftMessage): ?>
            <div class="alert" role="status"><?= h($quickDraftMessage) ?></div>
        <?php elseif ($quickDraftError): ?>
            <div class="alert alert-error" role="alert"><?= h($quickDraftError) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="quick-draft">
            <label for="draft_title">Title</label>
            <input type="text" id="draft_title" name="draft_title" placeholder="New page idea" required>
            <label for="draft_slug">Custom slug (optional)</label>
            <input type="text" id="draft_slug" name="draft_slug" placeholder="leave blank to auto-generate">
            <button type="submit" class="btn btn-primary">Save Draft</button>
        </form>
    </article>

    <article class="card-panel">
        <header>
            <h2>Recent Activity</h2>
        </header>
        <?php if ($recentPages): ?>
            <ul class="activity-list">
                <?php foreach ($recentPages as $page): ?>
                    <li class="activity-item">
                        <strong><?= h($page['title']) ?></strong>
                        <span>Slug: <?= h($page['slug']) ?> &middot; Status: <?= h(ucfirst($page['status'])) ?></span>
                        <span>Updated: <?= h(date('d M Y, H:i', strtotime($page['updated_at']))) ?></span>
                        <div class="quick-actions">
                            <a href="<?= base_url('admin/page_edit.php?id=' . $page['id']) ?>">Edit page</a>
                            <a href="<?= base_url('admin/page_sections.php?id=' . $page['id']) ?>">Edit sections</a>
                            <a href="<?= base_url('index.php?page=' . $page['slug']) ?>" target="_blank" rel="noopener">Preview</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No recent page updates yet. Start by creating your first page.</p>
        <?php endif; ?>
    </article>

    <article class="card-panel">
        <header>
            <h2>Site Snapshot</h2>
        </header>
        <p>Key brand settings pulled straight from your configuration.</p>
        <dl>
            <?php foreach ($settingsSnapshot as $label => $value): ?>
                <div>
                    <dt><?= h($label) ?></dt>
                    <dd><?= h($value) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </article>
</div>

<?php include __DIR__ . '/footer.php'; ?>

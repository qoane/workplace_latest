<?php
if (file_exists(__DIR__ . '/config.php')) {
    echo '<p>Configuration already exists. <a href="index.php">Go to site</a> or <a href="admin/login.php">login</a>.</p>';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $basePath = trim($_POST['base_path'] ?? '', '/');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminEmail = trim($_POST['admin_email'] ?? '');

    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $config = [
            'db' => [
                'host' => $dbHost,
                'name' => $dbName,
                'user' => $dbUser,
                'pass' => $dbPass,
                'charset' => 'utf8mb4',
            ],
            'app' => [
                'base_path' => $basePath,
                'session_name' => 'workplace_admin',
            ],
        ];

        $configExport = "<?php\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents(__DIR__ . '/config.php', $configExport) === false) {
            throw new RuntimeException('Unable to write config.php. Check folder permissions.');
        }

        setup_database($pdo);
        import_content($pdo);
        create_admin($pdo, $adminUser, $adminPass, $adminEmail);

        echo '<p>Installation complete. <a href="admin/login.php">Login to the admin</a>.</p>';
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function setup_database(PDO $pdo): void
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS admins (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) UNIQUE, password_hash VARCHAR(255) NOT NULL, email VARCHAR(150) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "CREATE TABLE IF NOT EXISTS site_settings (id INT AUTO_INCREMENT PRIMARY KEY, `key` VARCHAR(100) UNIQUE, value TEXT)",
        "CREATE TABLE IF NOT EXISTS site_sections (id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(100) UNIQUE, content LONGTEXT)",
        "CREATE TABLE IF NOT EXISTS pages (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, slug VARCHAR(150) UNIQUE, meta_title VARCHAR(255) NULL, meta_description TEXT NULL, body_class VARCHAR(255) NULL, template VARCHAR(100) DEFAULT 'default', status ENUM('draft','published') DEFAULT 'draft', created_at DATETIME NULL, updated_at DATETIME NULL)",
        "CREATE TABLE IF NOT EXISTS page_sections (id INT AUTO_INCREMENT PRIMARY KEY, page_id INT NOT NULL, name VARCHAR(100) NOT NULL, content LONGTEXT, sort_order INT DEFAULT 0, CONSTRAINT fk_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE)",
        "CREATE TABLE IF NOT EXISTS menus (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, slug VARCHAR(100) UNIQUE)",
        "CREATE TABLE IF NOT EXISTS menu_items (id INT AUTO_INCREMENT PRIMARY KEY, menu_id INT NOT NULL, parent_id INT NULL, label VARCHAR(150) NOT NULL, url VARCHAR(255) NOT NULL, url_type ENUM('internal','external') DEFAULT 'internal', target VARCHAR(20) NULL, css_class VARCHAR(150) NULL, sort_order INT DEFAULT 0, CONSTRAINT fk_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE, CONSTRAINT fk_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE)"
    ];

    foreach ($queries as $sql) {
        $pdo->exec($sql);
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach (['admins','site_settings','site_sections','page_sections','pages','menu_items','menus'] as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

function create_admin(PDO $pdo, string $username, string $password, string $email): void
{
    if ($username === '' || $password === '') {
        throw new RuntimeException('Admin username and password are required.');
    }
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, email) VALUES (:username, :password_hash, :email)');
    $stmt->execute([
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'email' => $email,
    ]);
}

function import_content(PDO $pdo): void
{
    $primarySections = extract_global_sections(__DIR__ . '/index.html');
    $settings = [
        ['key' => 'site_name', 'value' => $primarySections['site_title'] ?? 'Workplace Solutions'],
        ['key' => 'logo_path', 'value' => 'images/logo.png'],
    ];
    foreach ($settings as $setting) {
        $stmt = $pdo->prepare('INSERT INTO site_settings (`key`, value) VALUES (:key, :value)');
        $stmt->execute($setting);
    }

    foreach (['head_assets','body_start','top_bar','footer','body_end'] as $slug) {
        $stmt = $pdo->prepare('INSERT INTO site_sections (slug, content) VALUES (:slug, :content)');
        $stmt->execute([
            'slug' => $slug,
            'content' => $primarySections[$slug] ?? '',
        ]);
    }

    $pages = [];
    foreach (glob(__DIR__ . '/*.html') as $file) {
        $basename = basename($file);
        if ($basename === 'install.html') {
            continue;
        }
        $pageData = parse_html_page($file);
        if (!$pageData) {
            continue;
        }
        $pages[] = $pageData;
    }

    $stmt = $pdo->prepare('INSERT INTO pages (title, slug, meta_title, meta_description, body_class, template, status, created_at, updated_at) VALUES (:title, :slug, :meta_title, :meta_description, :body_class, :template, :status, NOW(), NOW())');
    $sectionStmt = $pdo->prepare('INSERT INTO page_sections (page_id, name, content, sort_order) VALUES (:page_id, :name, :content, :sort_order)');

    foreach ($pages as $page) {
        $stmt->execute([
            'title' => $page['title'],
            'slug' => $page['slug'],
            'meta_title' => $page['meta_title'],
            'meta_description' => $page['meta_description'],
            'body_class' => $page['body_class'],
            'template' => 'default',
            'status' => 'published',
        ]);
        $pageId = (int)$pdo->lastInsertId();
        $sectionStmt->execute([
            'page_id' => $pageId,
            'name' => 'body',
            'content' => $page['body'],
            'sort_order' => 1,
        ]);
    }

    create_primary_menu($pdo);
}

function extract_global_sections(string $file): array
{
    $html = file_get_contents($file);
    if ($html === false) {
        return [];
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    $result = [
        'site_title' => trim($xpath->evaluate('string(//title)')),
    ];

    $head = $dom->getElementsByTagName('head')->item(0);
    $headAssets = '';
    if ($head) {
        for ($i = 0; $i < $head->childNodes->length; $i++) {
            $child = $head->childNodes->item($i);
            if ($child->nodeName === 'title') {
                continue;
            }
            if ($child->nodeName === 'meta') {
                $name = strtolower($child->getAttribute('name'));
                if ($name === 'viewport') {
                    continue;
                }
                if ($child->hasAttribute('charset')) {
                    continue;
                }
            }
            $headAssets .= $dom->saveHTML($child);
        }
    }
    $result['head_assets'] = $headAssets;

    $bodyStart = '';
    $topBar = '';
    $footer = '';
    $bodyEnd = '';

    $body = $dom->getElementsByTagName('body')->item(0);
    if ($body) {
        $afterFooter = false;
        for ($i = 0; $i < $body->childNodes->length; $i++) {
            $child = $body->childNodes->item($i);
            if ($child->nodeType !== XML_ELEMENT_NODE && trim($child->textContent) === '') {
                continue;
            }
            if ($afterFooter) {
                $bodyEnd .= $dom->saveHTML($child);
                continue;
            }
            if ($child->nodeName === 'div' && $child->hasAttribute('id') && $child->getAttribute('id') === 'preloader') {
                $bodyStart .= $dom->saveHTML($child);
                continue;
            }
            if ($child->nodeName === 'div' && strpos($child->getAttribute('class'), 'top-bar') !== false) {
                $topBar = $dom->saveHTML($child);
                continue;
            }
            if ($child->nodeName === 'nav') {
                continue;
            }
            if ($child->nodeName === 'footer') {
                $footer = $dom->saveHTML($child);
                $afterFooter = true;
                continue;
            }
        }
    }
    $result['body_start'] = $bodyStart;
    $result['top_bar'] = $topBar;
    $result['footer'] = $footer;
    $result['body_end'] = $bodyEnd;

    return $result;
}

function parse_html_page(string $file): ?array
{
    $html = file_get_contents($file);
    if ($html === false) {
        return null;
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    $title = trim($xpath->evaluate('string(//title)'));
    $metaDescription = trim($xpath->evaluate('string(//meta[@name="description"]/@content)'));
    $body = $dom->getElementsByTagName('body')->item(0);
    $bodyClass = $body && $body->hasAttribute('class') ? $body->getAttribute('class') : '';
    $main = $dom->getElementsByTagName('main')->item(0);
    $bodyHtml = '';
    if ($main) {
        foreach ($main->childNodes as $child) {
            $bodyHtml .= $dom->saveHTML($child);
        }
    } elseif ($body) {
        foreach ($body->childNodes as $child) {
            $bodyHtml .= $dom->saveHTML($child);
        }
    }

    $slug = pathinfo($file, PATHINFO_FILENAME);

    return [
        'title' => $title ?: ucfirst(str_replace('-', ' ', $slug)),
        'slug' => $slug,
        'meta_title' => $title,
        'meta_description' => $metaDescription,
        'body_class' => $bodyClass,
        'body' => $bodyHtml,
    ];
}

function create_primary_menu(PDO $pdo): void
{
    $stmt = $pdo->prepare('INSERT INTO menus (name, slug) VALUES (:name, :slug)');
    $stmt->execute(['name' => 'Primary Navigation', 'slug' => 'primary']);
    $menuId = (int)$pdo->lastInsertId();

    $items = [
        [
            'label' => 'Home',
            'url' => 'index.php?page=index',
            'url_type' => 'internal',
            'children' => [],
        ],
        [
            'label' => 'Candidates',
            'url' => 'index.php?page=candidates',
            'url_type' => 'internal',
            'children' => [
                [
                    'label' => 'Submit your CV',
                    'url' => 'https://webapp.placementpartner.com/wi/application_form.php?id=workplacesolutions',
                    'url_type' => 'external',
                    'target' => '_blank',
                ],
                [
                    'label' => 'How to apply',
                    'url' => 'index.php?page=how-to-apply',
                    'url_type' => 'internal',
                ],
                [
                    'label' => 'Vacancies',
                    'url' => 'https://webapp.placementpartner.com/wi/weblinks.php?id=workplacesolutions',
                    'url_type' => 'external',
                    'target' => '_blank',
                ],
            ],
        ],
        [
            'label' => 'Clients',
            'url' => 'index.php?page=clients',
            'url_type' => 'internal',
        ],
        [
            'label' => 'Events',
            'url' => 'index.php?page=events',
            'url_type' => 'internal',
        ],
        [
            'label' => 'Blog',
            'url' => 'index.php?page=blog',
            'url_type' => 'internal',
        ],
        [
            'label' => 'Contact Us',
            'url' => 'index.php?page=contact',
            'url_type' => 'internal',
        ],
        [
            'label' => 'Downloads',
            'url' => 'index.php?page=downloads',
            'url_type' => 'internal',
            'children' => [
                [
                    'label' => 'Legal Documents',
                    'url' => 'index.php?page=legal-documents',
                    'url_type' => 'internal',
                ],
                [
                    'label' => 'Papers',
                    'url' => 'index.php?page=papers',
                    'url_type' => 'internal',
                ],
                [
                    'label' => 'Company Profile',
                    'url' => 'index.php?page=company-profile',
                    'url_type' => 'internal',
                ],
            ],
        ],
        [
            'label' => 'Available posts',
            'url' => 'https://webapp.placementpartner.com/wi/weblinks.php?id=workplacesolutions',
            'url_type' => 'external',
            'target' => '_blank',
            'css_class' => 'btn btn-primary btn-small',
        ],
    ];

    insert_menu_items($pdo, $menuId, $items, null);
}

function insert_menu_items(PDO $pdo, int $menuId, array $items, ?int $parentId): void
{
    $sort = 0;
    foreach ($items as $item) {
        $sort++;
        $stmt = $pdo->prepare('INSERT INTO menu_items (menu_id, parent_id, label, url, url_type, target, css_class, sort_order) VALUES (:menu_id, :parent_id, :label, :url, :url_type, :target, :css_class, :sort_order)');
        $stmt->execute([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'label' => $item['label'],
            'url' => $item['url'],
            'url_type' => $item['url_type'] ?? 'internal',
            'target' => $item['target'] ?? null,
            'css_class' => $item['css_class'] ?? null,
            'sort_order' => $sort,
        ]);
        $childId = (int)$pdo->lastInsertId();
        if (!empty($item['children'])) {
            insert_menu_items($pdo, $menuId, $item['children'], $childId);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Install Workplace CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
</head>
<body>
<main class="container">
    <h1>Workplace CMS Installer</h1>
    <?php if ($error): ?><article class="contrast"><?= htmlspecialchars($error, ENT_QUOTES) ?></article><?php endif; ?>
    <form method="post">
        <h2>Database</h2>
        <label>Host<input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost', ENT_QUOTES) ?>" required></label>
        <label>Database Name<input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '', ENT_QUOTES) ?>" required></label>
        <label>Username<input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '', ENT_QUOTES) ?>" required></label>
        <label>Password<input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '', ENT_QUOTES) ?>"></label>

        <h2>Application</h2>
        <label>Base Path (optional, e.g. work)
            <input type="text" name="base_path" value="<?= htmlspecialchars($_POST['base_path'] ?? '', ENT_QUOTES) ?>">
        </label>

        <h2>Admin User</h2>
        <label>Username<input type="text" name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin', ENT_QUOTES) ?>" required></label>
        <label>Password<input type="password" name="admin_pass" required></label>
        <label>Email<input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '', ENT_QUOTES) ?>"></label>

        <button type="submit">Install</button>
    </form>
</main>
</body>
</html>

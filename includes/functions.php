<?php
function base_path(): string
{
    global $config;

    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $configured = trim($config['app']['base_path'] ?? '', '/');
    if ($configured !== '') {
        return $cached = $configured;
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $appRoot = realpath(__DIR__ . '/..');

    if ($documentRoot && $appRoot) {
        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $appRoot = str_replace('\\', '/', $appRoot);

        if (strpos($appRoot, $documentRoot) === 0) {
            $detected = trim(substr($appRoot, strlen($documentRoot)), '/');
            return $cached = $detected;
        }
    }

    return $cached = '';
}

function base_url(string $path = ''): string
{
    $prefix = base_path();
    $segments = [];
    if ($prefix !== '') {
        $segments[] = $prefix;
    }
    if ($path !== '') {
        $segments[] = ltrim($path, '/');
    }

    $url = '/' . implode('/', $segments);
    return rtrim($url, '/') ?: '/';
}

function site_setting(PDO $pdo, string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = $pdo->query('SELECT `key`, `value` FROM site_settings');
        foreach ($stmt as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }

    return $cache[$key] ?? $default;
}

function get_site_sections(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT slug, content FROM site_sections');
    $sections = [];
    foreach ($stmt as $row) {
        $sections[$row['slug']] = $row['content'];
    }
    return $sections;
}

function get_page_by_slug(PDO $pdo, string $slug)
{
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = :slug AND status = "published" LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch();
}

function get_page_sections(PDO $pdo, int $pageId): array
{
    $stmt = $pdo->prepare('SELECT name, content FROM page_sections WHERE page_id = :id ORDER BY sort_order ASC');
    $stmt->execute(['id' => $pageId]);
    $sections = [];
    foreach ($stmt as $row) {
        $sections[$row['name']] = $row['content'];
    }
    return $sections;
}

function get_menu(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $menu = $stmt->fetch();
    if (!$menu) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE menu_id = :id ORDER BY sort_order ASC');
    $stmt->execute(['id' => $menu['id']]);
    $items = $stmt->fetchAll();
    $tree = build_menu_tree($items);
    $menu['items'] = $tree;
    return $menu;
}

function build_menu_tree(array $items, $parentId = null): array
{
    $tree = [];
    foreach ($items as $item) {
        if ((string)$item['parent_id'] === (string)$parentId) {
            $item['children'] = build_menu_tree($items, $item['id']);
            $tree[] = $item;
        }
    }
    return $tree;
}

function render_menu(array $items, string $class = 'nav-menu'): string
{
    if (empty($items)) {
        return '';
    }

    $html = '<ul class="' . htmlspecialchars($class, ENT_QUOTES) . '">';
    foreach ($items as $item) {
        $hasChildren = !empty($item['children']);
        $classes = $class === 'nav-menu' ? ['nav-item'] : [];
        if ($hasChildren && $class === 'nav-menu') {
            $classes[] = 'dropdown';
        }
        if (!empty($item['css_class'])) {
            $classes[] = $item['css_class'];
        }
        $classAttr = $classes ? ' class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES) . '"' : '';

        $url = $item['url_type'] === 'internal'
            ? base_url($item['url'])
            : $item['url'];
        $target = $item['target'] ? ' target="' . htmlspecialchars($item['target'], ENT_QUOTES) . '" rel="noopener"' : '';
        $html .= '<li' . $classAttr . '>';
        $html .= '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '">' . htmlspecialchars($item['label']) . '</a>';
        if ($hasChildren) {
            $html .= render_menu($item['children'], 'dropdown-menu');
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

function require_admin(): void
{
    if (!is_admin_authenticated()) {
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

function sanitize_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'page-' . time();
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES);
}

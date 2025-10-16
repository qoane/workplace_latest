<?php
require __DIR__ . '/includes/init.php';

$slug = $_GET['page'] ?? 'index';
$slug = strtolower(trim($slug));
$slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
$slug = $slug ?: 'index';
$page = get_page_by_slug($pdo, $slug);
if (!$page) {
    http_response_code(404);
    echo '<h1>Page not found</h1>';
    exit;
}

$pageSections = get_page_sections($pdo, (int)$page['id']);
$siteSections = get_site_sections($pdo);
$primaryMenu = get_menu($pdo, 'primary');

include __DIR__ . '/templates/' . ($page['template'] ?: 'default') . '.php';

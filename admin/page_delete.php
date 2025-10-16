<?php
require __DIR__ . '/../includes/init.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

header('Location: ' . base_url('admin/pages.php'));
exit;

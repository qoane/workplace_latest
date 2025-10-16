<?php
require __DIR__ . '/../includes/init.php';
logout_admin();
header('Location: ' . base_url('admin/login.php'));
exit;

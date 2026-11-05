<?php
require_once __DIR__ . '/config/auth.php';
header('Location: ' . (!empty($_SESSION['admin_id']) ? 'dashboard.php' : 'login.php'));
exit;

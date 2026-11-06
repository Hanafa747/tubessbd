<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function wajib_login(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}
function adalah_administrator(): bool {
    return ($_SESSION['admin_role'] ?? '') === 'administrator';
}
function wajib_administrator(): void {
    wajib_login();
    if (!adalah_administrator()) { http_response_code(403); exit('Akses ditolak.'); }
}
function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

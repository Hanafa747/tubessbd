<?php
require_once __DIR__ . '/../config/auth.php';
wajib_login();
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Dashboard') ?> | RSCare</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-layout">
<aside class="sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-symbol">✚</span><span><b>RS<span class="brand-light">Care</span></b><small>Hospital Management</small></span></a>
    <div class="nav-caption">MENU</div>
    <a class="nav-item <?= $currentFile === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><span class="nav-icon">▦</span>Dashboard</a>
    <div class="nav-caption">PENGELOLAAN DATA</div>
    <a class="nav-item <?= ($activeTable ?? '') === 'admin' ? 'active' : '' ?>" href="data.php?t=admin"><span class="nav-icon">♙</span>Data Admin</a>
    <a class="nav-item <?= ($activeTable ?? '') === 'pasien' ? 'active' : '' ?>" href="data.php?t=pasien"><span class="nav-icon">♡</span>Data Pasien</a>
    <a class="nav-item <?= ($activeTable ?? '') === 'poli' ? 'active' : '' ?>" href="data.php?t=poli"><span class="nav-icon">⌂</span>Data Poli</a>
    <a class="nav-item <?= ($activeTable ?? '') === 'dokter' ? 'active' : '' ?>" href="data.php?t=dokter"><span class="nav-icon">✚</span>Data Dokter</a>
    <a class="nav-item <?= ($activeTable ?? '') === 'pendaftaran' ? 'active' : '' ?>" href="data.php?t=pendaftaran"><span class="nav-icon">▤</span>Pendaftaran</a>
    <a class="nav-item <?= ($activeTable ?? '') === 'rekam_medis' ? 'active' : '' ?>" href="data.php?t=rekam_medis"><span class="nav-icon">▧</span>Rekam Medis</a>
    <div class="sidebar-bottom">
        <div class="profile-row"><div class="profile-avatar">A</div><div><b><?= h($_SESSION['admin_name'] ?? 'Admin') ?></b><small>Administrator</small></div></div>
        <a class="logout-link" href="logout.php">↪ <span>Keluar dari sistem</span></a>
    </div>
</aside>
<main class="main-area">
<header class="topbar">
    <div><div class="breadcrumb">RSCare <span>/</span> <?= h($pageTitle ?? 'Dashboard') ?></div><h1><?= h($pageTitle ?? 'Dashboard') ?></h1></div>
    <div class="topbar-right"><span class="online-dot"></span><span>Administrator</span><div class="profile-avatar">A</div></div>
</header>
<div class="page-content">

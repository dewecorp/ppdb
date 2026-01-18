<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

function active_menu(string $page, string $current): string
{
    return $page === $current ? 'active' : '';
}
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <?php if (!empty($madrasah_logo)): ?>
            <img src="<?= esc(base_url('uploads/' . $madrasah_logo)); ?>" alt="Logo" style="height:32px" class="mr-2">
        <?php else: ?>
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-graduation-cap"></i>
            </div>
        <?php endif; ?>
        <div class="sidebar-brand-text mx-3"><?= esc($madrasah_nama); ?></div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= active_menu('dashboard', $current_page); ?>">
        <a class="nav-link" href="index.php?page=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Master Data
    </div>

    <li class="nav-item <?= active_menu('madrasah', $current_page); ?>">
        <a class="nav-link" href="index.php?page=madrasah">
            <i class="fas fa-fw fa-school"></i>
            <span>Data Madrasah</span></a>
    </li>

    <li class="nav-item <?= active_menu('pendaftar', $current_page); ?>">
        <a class="nav-link" href="index.php?page=pendaftar">
            <i class="fas fa-fw fa-users"></i>
            <span>Data Pendaftar</span></a>
    </li>

    <li class="nav-item <?= active_menu('pengaturan', $current_page); ?>">
        <a class="nav-link" href="index.php?page=pengaturan">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Pengaturan</span></a>
    </li>

    <li class="nav-item <?= active_menu('pengguna', $current_page); ?>">
        <a class="nav-link" href="index.php?page=pengguna">
            <i class="fas fa-fw fa-user-cog"></i>
            <span>Pengguna</span></a>
    </li>

    <li class="nav-item <?= active_menu('backup', $current_page); ?>">
        <a class="nav-link" href="index.php?page=backup">
            <i class="fas fa-fw fa-database"></i>
            <span>Backup &amp; Restore</span></a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

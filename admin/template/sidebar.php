<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$is_admin_user = is_admin();

function active_menu(string $page, string $current): string
{
    return $page === $current ? 'active' : '';
}
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-start" href="<?= base_url('admin/dashboard'); ?>">
        <?php if (!empty($madrasah_logo)): ?>
            <img src="<?= esc(base_url('uploads/' . $madrasah_logo)); ?>" alt="Logo" style="height:32px" class="mr-2">
        <?php else: ?>
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-graduation-cap"></i>
            </div>
        <?php endif; ?>
        <?php
        $sidebar_app_name = 'PPDB';
        $madrasah_line_top = $madrasah_nama;
        $madrasah_line_bottom = '';

        if (stripos($madrasah_nama, 'MI SULTAN FATTAH SUKOSONO') !== false) {
            $madrasah_line_top = 'MI SULTAN FATTAH';
            $madrasah_line_bottom = 'SUKOSONO';
        } else {
            $last_space_pos = strrpos($madrasah_nama, ' ');
            if ($last_space_pos !== false) {
                $madrasah_line_top = substr($madrasah_nama, 0, $last_space_pos);
                $madrasah_line_bottom = substr($madrasah_nama, $last_space_pos + 1);
            }
        }
        ?>
        <div class="sidebar-brand-text mx-0 text-left">
            <div class="sidebar-app-name"><?= esc($sidebar_app_name); ?></div>
            <div class="sidebar-school-name"><?= esc($madrasah_line_top); ?></div>
            <?php if ($madrasah_line_bottom !== ''): ?>
                <div class="sidebar-school-name"><?= esc($madrasah_line_bottom); ?></div>
            <?php endif; ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= active_menu('dashboard', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/dashboard'); ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Master Data
    </div>

    <li class="nav-item <?= active_menu('madrasah', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/madrasah'); ?>">
            <i class="fas fa-fw fa-school"></i>
            <span>Data Madrasah</span></a>
    </li>

    <li class="nav-item <?= active_menu('pendaftar', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/pendaftar'); ?>">
            <i class="fas fa-fw fa-users"></i>
            <span>Data Pendaftar</span></a>
    </li>

    <?php if ($is_admin_user): ?>
    <li class="nav-item <?= active_menu('pengaturan', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/pengaturan'); ?>">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Pengaturan</span></a>
    </li>

    <li class="nav-item <?= active_menu('pengguna', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/pengguna'); ?>">
            <i class="fas fa-fw fa-user-cog"></i>
            <span>Pengguna</span></a>
    </li>
    <li class="nav-item <?= active_menu('backup', $current_page); ?>">
        <a class="nav-link" href="<?= base_url('admin/backup'); ?>">
            <i class="fas fa-fw fa-database"></i>
            <span>Backup &amp; Restore</span></a>
    </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

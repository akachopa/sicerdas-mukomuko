<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Notification;

$user = Auth::user();
$role = Auth::role();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$isActive = fn(string $prefix): string => str_starts_with($currentPath, $prefix) ? ' active' : '';
$notifCount = Notification::unreadCount(Auth::id());
$notifs = Notification::unreadFor(Auth::id(), 8);
?>
<!DOCTYPE html>
<html lang="<?= Lang::current() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'SICERDAS') ?> — SICERDAS Mukomuko</title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/vendor/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="sc-backdrop" onclick="document.body.classList.remove('sidebar-open')"></div>

<aside class="sc-sidebar">
    <a href="/dashboard" class="brand">
        <span class="logo">SC</span>
        <span>
            <h1>SICERDAS</h1>
            <small>Kabupaten Mukomuko</small>
        </span>
    </a>

    <nav class="nav flex-column pb-4">
        <div class="nav-section"><?= __t('common.dashboard') ?></div>
        <a class="nav-link<?= $isActive('/dashboard') ?>" href="/dashboard"><i class="bi bi-speedometer2"></i> <?= __t('common.dashboard') ?></a>

        <?php if (in_array($role, ['super_admin', 'admin_bapperida', 'verifikator', 'pimpinan'], true)): ?>
            <div class="nav-section">Data &amp; Program</div>
            <?php if ($role !== 'verifikator'): ?>
                <a class="nav-link<?= $isActive('/organisasi') ?>" href="/organisasi"><i class="bi bi-buildings"></i> <?= __t('menu.organizations') ?></a>
            <?php endif; ?>
            <a class="nav-link<?= $isActive('/program') ?>" href="/program"><i class="bi bi-kanban"></i> <?= __t('menu.programs') ?></a>
            <a class="nav-link<?= $isActive('/komitmen') ?>" href="/komitmen"><i class="bi bi-hand-thumbs-up"></i> <?= __t('menu.commitments') ?></a>
            <a class="nav-link<?= $isActive('/realisasi') ?>" href="/realisasi"><i class="bi bi-cash-coin"></i> <?= __t('menu.realizations') ?></a>
            <a class="nav-link<?= $isActive('/laporan') ?>" href="/laporan"><i class="bi bi-file-earmark-text"></i> <?= __t('menu.reports') ?></a>
            <a class="nav-link<?= $isActive('/rekap') ?>" href="/rekap"><i class="bi bi-clipboard-data"></i> <?= __t('menu.recap') ?></a>
        <?php endif; ?>

        <?php if ($role === 'opd'): ?>
            <div class="nav-section">Program</div>
            <a class="nav-link<?= $isActive('/program') ?>" href="/program"><i class="bi bi-kanban"></i> <?= __t('menu.my_programs') ?></a>
            <a class="nav-link<?= $isActive('/laporan') ?>" href="/laporan"><i class="bi bi-file-earmark-text"></i> <?= __t('menu.reports') ?></a>
        <?php endif; ?>

        <?php if ($role === 'mitra'): ?>
            <div class="nav-section">CSR</div>
            <a class="nav-link<?= $isActive('/profil-perusahaan') ?>" href="/profil-perusahaan"><i class="bi bi-building-gear"></i> <?= __t('menu.company_profile') ?></a>
            <a class="nav-link<?= $isActive('/mitra/katalog') ?>" href="/mitra/katalog"><i class="bi bi-grid-3x3-gap"></i> <?= __t('menu.catalog') ?></a>
            <a class="nav-link<?= $isActive('/komitmen') ?>" href="/komitmen"><i class="bi bi-hand-thumbs-up"></i> <?= __t('menu.my_commitments') ?></a>
            <a class="nav-link<?= $isActive('/realisasi') ?>" href="/realisasi"><i class="bi bi-cash-coin"></i> <?= __t('menu.realizations') ?></a>
            <a class="nav-link<?= $isActive('/laporan') ?>" href="/laporan"><i class="bi bi-file-earmark-text"></i> <?= __t('menu.my_reports') ?></a>
        <?php endif; ?>

        <?php if (in_array($role, ['super_admin', 'admin_bapperida'], true)): ?>
            <div class="nav-section">Administrasi</div>
            <a class="nav-link<?= $isActive('/master') ?>" href="/master/tahun-anggaran"><i class="bi bi-database-gear"></i> <?= __t('menu.master_data') ?></a>
            <a class="nav-link<?= $isActive('/pengguna') ?>" href="/pengguna"><i class="bi bi-people"></i> <?= __t('menu.users') ?></a>
            <a class="nav-link<?= $isActive('/audit') ?>" href="/audit"><i class="bi bi-shield-check"></i> <?= __t('menu.audit') ?></a>
        <?php endif; ?>

        <div class="nav-section">Lainnya</div>
        <a class="nav-link<?= $isActive('/notifikasi') ?>" href="/notifikasi"><i class="bi bi-bell"></i> <?= __t('menu.notifications') ?>
            <?php if ($notifCount > 0): ?><span class="badge text-bg-danger ms-auto"><?= $notifCount ?></span><?php endif; ?>
        </a>
        <a class="nav-link" href="/" target="_blank"><i class="bi bi-globe2"></i> <?= __t('menu.public_portal') ?></a>
    </nav>
</aside>

<div class="sc-main">
    <header class="sc-topbar">
        <button class="btn btn-light d-lg-none" onclick="document.body.classList.add('sidebar-open')" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="d-none d-md-block text-muted small"><?= e($title ?? '') ?></div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if ($role === 'mitra'): ?>
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-translate"></i> <?= strtoupper(Lang::current()) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item<?= Lang::current() === 'id' ? ' active' : '' ?>" href="/lang/id">Bahasa Indonesia</a></li>
                    <li><a class="dropdown-item<?= Lang::current() === 'en' ? ' active' : '' ?>" href="/lang/en">English</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
                    <i class="bi bi-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger" style="font-size:.6rem"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow" style="width: 320px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <?= __t('notif.title') ?>
                        <a href="/notifikasi" class="small text-decoration-none"><?= __t('common.all') ?></a>
                    </div>
                    <?php if ($notifs === []): ?>
                        <div class="px-3 py-2 text-muted small"><?= __t('notif.empty') ?></div>
                    <?php else: foreach ($notifs as $n): ?>
                        <a class="dropdown-item text-wrap small<?= $n['is_read'] ? ' text-muted' : '' ?>" href="/notifikasi/<?= $n['id'] ?>/buka">
                            <strong><?= e($n['title']) ?></strong><br>
                            <span><?= e(mb_strimwidth($n['message'], 0, 90, '…')) ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-sm-inline"><?= e($user['full_name']) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted"><?= e($user['role_name']) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="/logout"><?= Csrf::field() ?>
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-1"></i><?= __t('auth.logout') ?></button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="sc-content">
        <?php foreach (get_flashes() as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($f['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</div>

<script src="/assets/vendor/jquery.min.js"></script>
<script src="/assets/vendor/bootstrap.bundle.min.js"></script>
<script src="/assets/vendor/dataTables.min.js"></script>
<script src="/assets/vendor/dataTables.bootstrap5.min.js"></script>
<script src="/assets/vendor/chart.umd.min.js"></script>
<script>
window.CSRF_TOKEN = '<?= Csrf::token() ?>';
window.DT_LANG = <?= json_encode([
    'search' => __t('common.search'),
    'lengthMenu' => Lang::current() === 'id' ? 'Tampilkan _MENU_ data' : 'Show _MENU_ entries',
    'info' => Lang::current() === 'id' ? 'Menampilkan _START_–_END_ dari _TOTAL_ data' : 'Showing _START_ to _END_ of _TOTAL_ entries',
    'infoEmpty' => Lang::current() === 'id' ? 'Tidak ada data' : 'No entries',
    'infoFiltered' => Lang::current() === 'id' ? '(difilter dari _MAX_ total data)' : '(filtered from _MAX_ total entries)',
    'zeroRecords' => Lang::current() === 'id' ? 'Data tidak ditemukan' : 'No matching records found',
    'emptyTable' => Lang::current() === 'id' ? 'Belum ada data' : 'No data available',
    'paginate' => ['first' => '«', 'last' => '»', 'next' => '›', 'previous' => '‹'],
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/app.js"></script>
</body>
</html>

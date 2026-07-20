<?php use App\Core\Auth; use App\Core\Lang; ?>
<!DOCTYPE html>
<html lang="<?= Lang::current() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'SICERDAS') ?> — SICERDAS Mukomuko</title>
    <meta name="description" content="<?= __t('public.hero_subtitle') ?>">
    <link rel="stylesheet" href="/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body style="background:#fff">
<nav class="navbar navbar-expand-lg navbar-dark pub-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/">
            <span class="d-inline-flex align-items-center justify-content-center"
                  style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#c9a227,#e6c65a);color:#0f2a52;font-weight:800;">SC</span>
            <span>
                <strong>SICERDAS</strong>
                <small class="d-block" style="font-size:.65rem;color:#9db1cf">Kabupaten Mukomuko</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pubNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="pubNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="/"><?= __t('public.transparency') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/katalog"><?= __t('menu.catalog') ?></a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="bi bi-translate me-1"></i><?= strtoupper(Lang::current()) ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/lang/id">Bahasa Indonesia</a></li>
                        <li><a class="dropdown-item" href="/lang/en">English</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="btn btn-gold btn-sm px-3" href="<?= Auth::check() ? '/dashboard' : '/login' ?>">
                        <i class="bi bi-box-arrow-in-right me-1"></i><?= Auth::check() ? __t('common.dashboard') : __t('public.partner_login') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?= $content ?>

<footer class="pub-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="d-inline-flex align-items-center justify-content-center"
                          style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#c9a227,#e6c65a);color:#0f2a52;font-weight:800;">SC</span>
                    <strong class="text-white">SICERDAS Mukomuko</strong>
                </div>
                <p class="mb-0"><?= __t('app.tagline') ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1">Badan Perencanaan, Penelitian, dan Pengembangan Daerah</p>
                <p class="mb-0">Kabupaten Mukomuko, Provinsi Bengkulu</p>
            </div>
        </div>
        <hr style="border-color:#26436e">
        <div class="text-center small">&copy; <?= date('Y') ?> Pemerintah Kabupaten Mukomuko</div>
    </div>
</footer>

<script src="/assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

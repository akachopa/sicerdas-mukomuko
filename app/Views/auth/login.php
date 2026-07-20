<?php use App\Core\Csrf; use App\Core\Lang; ?>
<div class="login-wrap">
    <div class="card login-card shadow-lg">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <span class="logo d-inline-flex align-items-center justify-content-center mb-3"
                      style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#c9a227,#e6c65a);color:#0f2a52;font-weight:800;font-size:1.4rem;">SC</span>
                <h1 class="h4 fw-bold mb-1" style="color:#0f2a52"><?= __t('auth.login_title') ?></h1>
                <p class="text-muted small mb-0"><?= __t('auth.login_subtitle') ?></p>
            </div>

            <?php foreach (get_flashes() as $f): ?>
                <div class="alert alert-<?= e($f['type']) ?> py-2 small"><?= e($f['message']) ?></div>
            <?php endforeach; ?>

            <form method="post" action="/login">
                <?= Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label required"><?= __t('auth.email') ?></label>
                    <input type="email" name="email" class="form-control" required autofocus value="<?= old('email') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label required"><?= __t('auth.password') ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit">
                    <i class="bi bi-box-arrow-in-right me-1"></i><?= __t('auth.login') ?>
                </button>
            </form>

            <div class="text-center mt-4 d-flex justify-content-center gap-3 small">
                <a href="/" class="text-decoration-none"><i class="bi bi-globe2 me-1"></i><?= __t('menu.public_portal') ?></a>
                <span class="text-muted">|</span>
                <a href="/lang/<?= Lang::current() === 'id' ? 'en' : 'id' ?>" class="text-decoration-none">
                    <i class="bi bi-translate me-1"></i><?= Lang::current() === 'id' ? 'English' : 'Bahasa Indonesia' ?>
                </a>
            </div>
        </div>
    </div>
</div>

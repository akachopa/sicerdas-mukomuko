<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="page-title"><?= e($title) ?></h2>
    <a href="/pengguna" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<div class="card" style="max-width: 760px;">
    <div class="card-body">
        <form method="post" action="<?= $user ? '/pengguna/' . $user['id'] . '/update' : '/pengguna/simpan' ?>" class="row g-3">
            <?= Csrf::field() ?>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('user.full_name') ?></label>
                <input class="form-control" name="full_name" required value="<?= e($user['full_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('common.email') ?></label>
                <input type="email" class="form-control" name="email" required value="<?= e($user['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('user.role') ?></label>
                <select class="form-select" name="role_id" id="selRole" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['roles'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('user.position') ?></label>
                <input class="form-control" name="position" value="<?= e($user['position'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('user.organization') ?></label>
                <select class="form-select" name="organization_id">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['organizations'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($user['organization_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('user.department') ?></label>
                <select class="form-select" name="department_id">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['departments'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($user['department_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('common.phone') ?></label>
                <input class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('user.password') ?><?= $user ? ' <small class="text-muted fw-normal">(' . __t('user.password_hint') . ')</small>' : '' ?></label>
                <input type="password" class="form-control" name="password" minlength="8" <?= $user ? '' : 'required' ?>>
            </div>
            <div class="col-12">
                <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
            </div>
        </form>
    </div>
</div>

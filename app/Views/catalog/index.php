<?php use App\Core\Csrf; ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('menu.catalog') ?></h2>
        <p class="text-muted small mb-0"><?= __t('public.catalog_subtitle') ?></p>
    </div>
</div>

<form class="card mb-3" method="get">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label"><?= __t('program.field') ?></label>
            <select class="form-select" name="field">
                <option value="0"><?= __t('common.all') ?></option>
                <?php foreach ($fields as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $filterField == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label"><?= __t('common.district') ?></label>
            <select class="form-select" name="district">
                <option value="0"><?= __t('common.all') ?></option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $filterDistrict == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i><?= __t('common.apply') ?></button>
        </div>
    </div>
</form>

<div class="row g-3">
    <?php if ($programs === []): ?>
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5"><?= __t('common.none') ?></div></div></div>
    <?php endif; ?>
    <?php foreach ($programs as $p):
        $gap = max(0, (float) $p['budget_needed'] - (float) $p['committed']);
        $pct = (float) $p['budget_needed'] > 0 ? min(100, round((float) $p['committed'] / (float) $p['budget_needed'] * 100)) : 0;
        $hasInterest = in_array($p['id'], $myInterests);
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card program-card">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge text-bg-light border"><?= e($p['field_name']) ?></span>
                    <span class="badge text-bg-<?= match ($p['priority_level']) { 'mendesak' => 'danger', 'tinggi' => 'warning', 'sedang' => 'info', default => 'secondary' } ?>">
                        <?= __t('priority.' . $p['priority_level']) ?>
                    </span>
                </div>
                <h5 class="fw-bold mb-1" style="color:#0f2a52"><?= e($p['name']) ?></h5>
                <p class="small text-muted mb-2">
                    <i class="bi bi-geo-alt me-1"></i><?= e($p['district_name'] ?? '-') ?>
                    &middot; <?= e($p['dept_short']) ?>
                    &middot; <i class="bi bi-people ms-1 me-1"></i><?= number_format((int) $p['beneficiary_count'], 0, ',', '.') ?>
                </p>
                <p class="small mb-3 flex-grow-1"><?= e(mb_strimwidth($p['description'] ?? '', 0, 120, '…')) ?></p>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= __t('public.needed') ?>: <strong><?= format_rupiah($p['budget_needed']) ?></strong></span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="progress" style="height:7px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                    <small class="text-muted"><?= __t('public.still_needed') ?>: <?= format_rupiah($gap) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary flex-grow-1" href="/program/<?= $p['id'] ?>"><?= __t('common.detail') ?></a>
                    <?php if ($hasInterest): ?>
                        <button class="btn btn-sm btn-success flex-grow-1" disabled><i class="bi bi-check-lg me-1"></i><?= __t('status.diajukan') ?></button>
                    <?php else: ?>
                        <form method="post" action="/mitra/katalog/<?= $p['id'] ?>/minat" class="flex-grow-1"><?= Csrf::field() ?>
                            <button class="btn btn-sm btn-gold w-100"><i class="bi bi-star me-1"></i><?= __t('program.express_interest') ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

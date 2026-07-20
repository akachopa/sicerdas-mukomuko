<section class="py-5" style="background:linear-gradient(135deg,#0f2a52,#0a1f3d)">
    <div class="container text-white">
        <h1 class="h3 fw-bold mb-1"><?= __t('public.catalog_title') ?></h1>
        <p class="mb-0" style="color:#c3d1e6"><?= __t('public.catalog_subtitle') ?></p>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <form class="card mb-4" method="get">
            <div class="card-body row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label"><?= __t('common.search') ?></label>
                    <input class="form-control" name="q" value="<?= e($search) ?>" placeholder="<?= __t('program.name') ?>...">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __t('program.field') ?></label>
                    <select class="form-select" name="field">
                        <option value="0"><?= __t('common.all') ?></option>
                        <?php foreach ($fields as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= $filterField == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i><?= __t('common.search') ?></button>
                </div>
            </div>
        </form>

        <div class="row g-3">
            <?php if ($programs === []): ?>
                <div class="col-12 text-center text-muted py-5"><?= __t('common.none') ?></div>
            <?php endif; ?>
            <?php foreach ($programs as $p):
                $gap = max(0, (float) $p['budget_needed'] - (float) $p['committed']);
                $pct = (float) $p['budget_needed'] > 0 ? min(100, round((float) $p['committed'] / (float) $p['budget_needed'] * 100)) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card program-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge text-bg-light border"><?= e($p['field_name']) ?></span>
                            <?= status_badge($p['status']) ?>
                        </div>
                        <h5 class="fw-bold" style="color:#0f2a52"><?= e($p['name']) ?></h5>
                        <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($p['district_name'] ?? '-') ?>
                            &middot; <i class="bi bi-people ms-1 me-1"></i><?= number_format((int) $p['beneficiary_count'], 0, ',', '.') ?></p>
                        <p class="small flex-grow-1"><?= e(mb_strimwidth($p['description'] ?? '', 0, 110, '…')) ?></p>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?= __t('public.needed') ?>: <strong><?= format_rupiah($p['budget_needed']) ?></strong></span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div class="progress mb-2" style="height:7px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                            <div class="small text-muted mb-2"><?= $gap > 0 ? __t('public.still_needed') . ': ' . format_rupiah($gap) : '<span class="text-success fw-semibold">' . __t('public.fully_funded') . '</span>' ?></div>
                            <a href="/katalog/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary w-100"><?= __t('common.detail') ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

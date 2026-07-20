<section class="pub-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge text-bg-light border mb-3" style="color:#0f2a52 !important"><i class="bi bi-patch-check me-1"></i>Creative Financing Kabupaten Mukomuko</span>
                <h1><?= __t('public.hero_title') ?></h1>
                <p class="lead mt-3"><?= __t('public.hero_subtitle') ?></p>
                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <a href="/katalog" class="btn btn-gold btn-lg px-4"><i class="bi bi-grid-3x3-gap me-2"></i><?= __t('public.view_catalog') ?></a>
                    <a href="/login" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-box-arrow-in-right me-2"></i><?= __t('public.partner_login') ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4" style="margin-top:-2.5rem">
    <div class="container">
        <div class="card shadow">
            <div class="row g-0">
                <div class="col-6 col-lg-3 pub-stat">
                    <div class="value"><?= format_rupiah($stats['contribution']) ?></div>
                    <div class="label"><?= __t('public.total_contribution') ?></div>
                </div>
                <div class="col-6 col-lg-3 pub-stat">
                    <div class="value"><?= number_format($stats['programs'], 0, ',', '.') ?></div>
                    <div class="label"><?= __t('public.total_programs') ?></div>
                </div>
                <div class="col-6 col-lg-3 pub-stat">
                    <div class="value"><?= number_format($stats['partners'], 0, ',', '.') ?></div>
                    <div class="label"><?= __t('public.total_partners') ?></div>
                </div>
                <div class="col-6 col-lg-3 pub-stat">
                    <div class="value"><?= number_format($stats['beneficiaries'], 0, ',', '.') ?></div>
                    <div class="label"><?= __t('public.total_beneficiaries') ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color:#0f2a52"><?= __t('public.catalog_title') ?></h2>
            <p class="text-muted"><?= __t('public.catalog_subtitle') ?></p>
        </div>
        <div class="row g-3">
            <?php foreach ($featured as $p):
                $gap = max(0, (float) $p['budget_needed'] - (float) $p['committed']);
                $pct = (float) $p['budget_needed'] > 0 ? min(100, round((float) $p['committed'] / (float) $p['budget_needed'] * 100)) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card program-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge text-bg-light border"><?= e($p['field_name']) ?></span>
                            <span class="badge text-bg-<?= match ($p['priority_level']) { 'mendesak' => 'danger', 'tinggi' => 'warning', 'sedang' => 'info', default => 'secondary' } ?>">
                                <?= __t('priority.' . $p['priority_level']) ?>
                            </span>
                        </div>
                        <h5 class="fw-bold" style="color:#0f2a52"><?= e($p['name']) ?></h5>
                        <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($p['district_name'] ?? '-') ?>
                            &middot; <i class="bi bi-people ms-1 me-1"></i><?= number_format((int) $p['beneficiary_count'], 0, ',', '.') ?></p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?= __t('public.needed') ?>: <strong><?= format_rupiah($p['budget_needed']) ?></strong></span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div class="progress mb-2" style="height:7px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                            <a href="/katalog/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary w-100"><?= __t('common.detail') ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/katalog" class="btn btn-primary px-4"><?= __t('public.view_catalog') ?> <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

<section class="py-5" style="background:#f4f6fb">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3" style="color:#0f2a52"><?= __t('public.programs_by_field') ?></h3>
                <?php foreach ($byField as $f): ?>
                    <div class="d-flex justify-content-between align-items-center bg-white rounded-3 px-3 py-2 mb-2 shadow-sm">
                        <span><?= e($f['name']) ?></span>
                        <span class="badge text-bg-primary rounded-pill"><?= $f['total'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3" style="color:#0f2a52"><?= __t('public.partners_title') ?></h3>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($partners === []): ?>
                        <p class="text-muted"><?= __t('common.none') ?></p>
                    <?php else: foreach ($partners as $partner): ?>
                        <span class="badge text-bg-light border px-3 py-2" style="font-size:.85rem">
                            <i class="bi bi-building me-1 text-muted"></i><?= e($partner['name']) ?>
                        </span>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

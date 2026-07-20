<?php
$gap = max(0, (float) $program['budget_needed'] - (float) $program['committed']);
$pct = (float) $program['budget_needed'] > 0 ? min(100, round((float) $program['committed'] / (float) $program['budget_needed'] * 100)) : 0;
?>
<section class="py-5" style="background:linear-gradient(135deg,#0f2a52,#0a1f3d)">
    <div class="container text-white">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/" class="text-white-50"><?= __t('public.transparency') ?></a></li>
                <li class="breadcrumb-item"><a href="/katalog" class="text-white-50"><?= __t('menu.catalog') ?></a></li>
                <li class="breadcrumb-item active text-white"><?= e($program['code']) ?></li>
            </ol>
        </nav>
        <span class="badge text-bg-light border mb-2" style="color:#0f2a52 !important"><?= e($program['field_name']) ?></span>
        <h1 class="h3 fw-bold"><?= e($program['name']) ?></h1>
        <p class="mb-0" style="color:#c3d1e6">
            <i class="bi bi-geo-alt me-1"></i><?= e($program['district_name'] ?? '-') ?><?= $program['village_name'] ? ' — ' . e($program['village_name']) : '' ?>
            &middot; <i class="bi bi-building ms-2 me-1"></i><?= e($program['dept_name']) ?>
            &middot; <i class="bi bi-calendar3 ms-2 me-1"></i><?= e($program['year']) ?>
        </p>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="fw-bold" style="color:#0f2a52"><?= __t('common.description') ?></h5>
                        <p><?= e($program['description'] ?? '-') ?></p>
                        <?php if ($program['objective']): ?>
                            <h6 class="fw-bold" style="color:#0f2a52"><?= __t('program.objective') ?></h6>
                            <p><?= e($program['objective']) ?></p>
                        <?php endif; ?>
                        <?php if ($program['output']): ?>
                            <h6 class="fw-bold" style="color:#0f2a52"><?= __t('program.output') ?></h6>
                            <p><?= e($program['output']) ?></p>
                        <?php endif; ?>
                        <h6 class="fw-bold" style="color:#0f2a52"><?= __t('program.beneficiary_target') ?></h6>
                        <p class="mb-0"><?= e($program['beneficiary_target'] ?? '-') ?>
                            (<?= number_format((int) $program['beneficiary_count'], 0, ',', '.') ?> <?= strtolower(__t('dash.beneficiaries')) ?>)</p>
                    </div>
                </div>

                <?php if ($supporters !== []): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3" style="color:#0f2a52"><?= __t('public.partners_title') ?></h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($supporters as $s): ?>
                                <span class="badge text-bg-light border px-3 py-2"><i class="bi bi-building me-1 text-muted"></i><?= e($s['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3"><?= __t('program.funding_progress') ?></h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small"><?= __t('public.needed') ?></span>
                            <strong><?= format_rupiah($program['budget_needed']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small"><?= __t('program.committed') ?></span>
                            <strong class="text-success"><?= format_rupiah($program['committed']) ?></strong>
                        </div>
                        <div class="progress mb-2" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                        <div class="text-center small text-muted mb-3"><?= $pct ?>% — <?= $gap > 0 ? __t('public.still_needed') . ' ' . format_rupiah($gap) : __t('public.fully_funded') ?></div>
                        <a href="/login" class="btn btn-gold w-100"><i class="bi bi-hand-thumbs-up me-1"></i><?= __t('public.support_program') ?></a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr><th class="ps-3"><?= __t('program.code') ?></th><td><?= e($program['code']) ?></td></tr>
                            <tr><th class="ps-3"><?= __t('common.status') ?></th><td><?= status_badge($program['status']) ?></td></tr>
                            <tr><th class="ps-3"><?= __t('program.priority') ?></th><td><?= __t('priority.' . $program['priority_level']) ?></td></tr>
                            <tr><th class="ps-3"><?= __t('program.department') ?></th><td><?= e($program['dept_short']) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

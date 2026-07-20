<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('common.welcome') ?>, <?= e($org['name'] ?? '') ?></h2>
        <p class="text-muted small mb-0"><?= __t('app.tagline') ?></p>
    </div>
    <a href="/laporan/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('report.add') ?></a>
</div>

<?php if ($kpi['need_revision'] > 0): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
        <?= $kpi['need_revision'] ?> <?= strtolower(__t('status.perlu_perbaikan')) ?> — <a href="/laporan" class="alert-link"><?= __t('menu.my_reports') ?></a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-hand-thumbs-up"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['commitment']) ?></div><div class="kpi-label"><?= __t('dash.total_commitment') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['realization']) ?></div><div class="kpi-label"><?= __t('dash.total_realization') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-kanban"></i></div>
            <div><div class="kpi-value"><?= $kpi['running'] ?></div><div class="kpi-label"><?= __t('dash.my_running_programs') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-person-check"></i></div>
            <div>
                <div class="kpi-value"><?= $profilePct ?>%</div>
                <div class="kpi-label"><?= __t('dash.profile_completeness') ?></div>
                <div class="progress mt-1" style="height:5px;width:120px"><div class="progress-bar bg-gold" style="width:<?= $profilePct ?>%;background:#c9a227"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= __t('menu.my_reports') ?></span>
                <a href="/laporan" class="small text-decoration-none"><?= __t('common.all') ?></a>
            </div>
            <div class="list-group list-group-flush">
                <?php if ($myReports === []): ?>
                    <div class="list-group-item text-muted small"><?= __t('common.none') ?></div>
                <?php else: foreach ($myReports as $r): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="/laporan/<?= $r['id'] ?>">
                        <span class="small"><strong><?= e($r['number']) ?></strong><br><span class="text-muted"><?= e($r['year'] . ' — ' . $r['period']) ?></span></span>
                        <?= status_badge($r['status']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= __t('dash.urgent_programs') ?></span>
                <a href="/mitra/katalog" class="small text-decoration-none"><?= __t('menu.catalog') ?></a>
            </div>
            <div class="list-group list-group-flush">
                <?php if ($recommended === []): ?>
                    <div class="list-group-item text-muted small"><?= __t('common.none') ?></div>
                <?php else: foreach ($recommended as $p): $gap = max(0, (float) $p['budget_needed'] - (float) $p['committed']); ?>
                    <a class="list-group-item list-group-item-action" href="/program/<?= $p['id'] ?>">
                        <div class="small fw-semibold"><?= e($p['name']) ?></div>
                        <small class="text-muted"><?= e($p['field_name']) ?> · <?= __t('public.still_needed') ?>: <?= format_rupiah($gap) ?></small>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

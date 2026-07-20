<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('common.dashboard') ?> OPD</h2>
        <p class="text-muted small mb-0"><?= __t('app.tagline') ?></p>
    </div>
    <a href="/program/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('program.add') ?></a>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-kanban"></i></div>
            <div><div class="kpi-value"><?= $kpi['submitted'] ?></div><div class="kpi-label"><?= __t('menu.my_programs') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-patch-check"></i></div>
            <div><div class="kpi-value"><?= $kpi['verified'] ?></div><div class="kpi-label"><?= __t('status.terverifikasi') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-wallet2"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['needed']) ?></div><div class="kpi-label"><?= __t('dash.total_needed') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-hand-thumbs-up"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['committed']) ?></div><div class="kpi-label"><?= __t('dash.total_commitment') ?></div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><?= __t('menu.my_programs') ?></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th class="ps-3"><?= __t('common.no') ?></th>
                <th><?= __t('program.code') ?></th>
                <th><?= __t('program.name') ?></th>
                <th class="text-end"><?= __t('program.budget_needed') ?></th>
                <th><?= __t('common.status') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if ($myPrograms === []): ?>
                <tr><td colspan="5" class="text-center text-muted py-4"><?= __t('common.none') ?></td></tr>
            <?php else: foreach ($myPrograms as $i => $p): ?>
                <tr>
                    <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                    <td class="small text-muted"><?= e($p['code']) ?></td>
                    <td><a href="/program/<?= $p['id'] ?>"><?= e($p['name']) ?></a></td>
                    <td class="text-end"><?= format_rupiah($p['budget_needed']) ?></td>
                    <td><?= status_badge($p['status']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

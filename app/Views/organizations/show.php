<?php use App\Core\Csrf; use App\Core\Auth; ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= e($org['name']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/organisasi"><?= __t('menu.organizations') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('common.detail') ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (Auth::isAdmin()): ?>
            <a href="/organisasi/<?= $org['id'] ?>/ubah" class="btn btn-primary"><i class="bi bi-pencil me-1"></i><?= __t('common.edit') ?></a>
        <?php endif; ?>
        <a href="/organisasi" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-hand-thumbs-up"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($totals['committed']) ?></div><div class="kpi-label"><?= __t('program.committed') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($totals['realized']) ?></div><div class="kpi-label"><?= __t('program.realized') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-clipboard-check"></i></div>
            <div><div class="kpi-value"><?= count($reports) ?></div><div class="kpi-label"><?= __t('menu.reports') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-patch-check"></i></div>
            <div><div class="kpi-value" style="font-size:1rem"><?= status_badge($org['compliance_status']) ?></div><div class="kpi-label"><?= __t('org.compliance') ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-buildings me-1"></i> <?= __t('common.profile') ?></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th class="ps-3" style="width:42%"><?= __t('org.legal_name') ?></th><td><?= e($org['legal_name'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.entity_type') ?></th><td><?= e($org['entity_type'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.sector') ?></th><td><?= e($org['sector'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.nib') ?></th><td><?= e($org['nib'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.npwp') ?></th><td><?= e($org['npwp'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.address') ?></th><td><?= e($org['address'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.district') ?></th><td><?= e($org['district_name'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.village') ?></th><td><?= e($org['village_name'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.email') ?></th><td><?= e($org['email'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.phone') ?></th><td><?= e($org['phone'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.pic') ?></th><td><?= e($org['pic_name'] ?? '-') ?><?= $org['pic_position'] ? ' — ' . e($org['pic_position']) : '' ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.employee_count') ?></th><td><?= e($org['employee_count'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('org.csr_potential') ?></th><td><?= $org['csr_potential'] !== null ? format_rupiah($org['csr_potential']) : '-' ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder2-open me-1"></i> <?= __t('common.documents') ?></span>
            </div>
            <div class="card-body">
                <?php if (Auth::isAdmin()): ?>
                <form method="post" action="/organisasi/<?= $org['id'] ?>/dokumen" enctype="multipart/form-data" class="row g-2 mb-3">
                    <?= Csrf::field() ?>
                    <div class="col-5"><input class="form-control form-control-sm" name="doc_type" placeholder="<?= __t('upload.doc_type') ?>" required></div>
                    <div class="col-5"><input class="form-control form-control-sm" type="file" name="document" required></div>
                    <div class="col-2"><button class="btn btn-sm btn-primary w-100"><?= __t('common.upload') ?></button></div>
                </form>
                <?php endif; ?>
                <?php if ($documents === []): ?>
                    <p class="text-muted small mb-0"><?= __t('common.none') ?></p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-file-earmark me-1 text-muted"></i><?= e($doc['doc_type']) ?> — <small class="text-muted"><?= e($doc['file_name']) ?></small></span>
                                <a href="<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-hand-thumbs-up me-1"></i> <?= __t('commitment.title') ?></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead><tr><th class="ps-3"><?= __t('commitment.number') ?></th><th><?= __t('commitment.program') ?></th><th class="text-end"><?= __t('common.amount') ?></th><th><?= __t('common.status') ?></th></tr></thead>
                    <tbody>
                    <?php if ($commitments === []): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3"><?= __t('common.none') ?></td></tr>
                    <?php else: foreach ($commitments as $c): ?>
                        <tr>
                            <td class="ps-3"><a href="/komitmen/<?= $c['id'] ?>"><?= e($c['number']) ?></a></td>
                            <td><?= e($c['program_name']) ?></td>
                            <td class="text-end"><?= format_rupiah($c['amount']) ?></td>
                            <td><?= status_badge($c['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-text me-1"></i> <?= __t('report.title') ?></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead><tr><th class="ps-3"><?= __t('report.number') ?></th><th><?= __t('common.year') ?></th><th><?= __t('common.period') ?></th><th><?= __t('common.status') ?></th></tr></thead>
                    <tbody>
                    <?php if ($reports === []): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3"><?= __t('common.none') ?></td></tr>
                    <?php else: foreach ($reports as $r): ?>
                        <tr>
                            <td class="ps-3"><a href="/laporan/<?= $r['id'] ?>"><?= e($r['number']) ?></a></td>
                            <td><?= e($r['year']) ?></td>
                            <td><?= e($r['period']) ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

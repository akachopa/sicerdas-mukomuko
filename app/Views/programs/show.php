<?php
use App\Core\Auth;
use App\Core\Csrf;

$role = Auth::role();
$isAdmin = in_array($role, ['super_admin', 'admin_bapperida'], true);
$isVerifier = in_array($role, ['super_admin', 'admin_bapperida', 'verifikator'], true);
$canManage = in_array($role, ['super_admin', 'admin_bapperida', 'opd'], true);
$pct = (float) $program['budget_needed'] > 0
    ? min(100, round($funding['committed'] / (float) $program['budget_needed'] * 100)) : 0;
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= e($program['name']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/program"><?= __t('menu.programs') ?></a></li>
                <li class="breadcrumb-item active"><?= e($program['code']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($canManage && in_array($program['status'], ['draft', 'perlu_revisi'], true)): ?>
            <a href="/program/<?= $program['id'] ?>/ubah" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i><?= __t('common.edit') ?></a>
            <form method="post" action="/program/<?= $program['id'] ?>/ajukan"><?= Csrf::field() ?>
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i><?= __t('program.submit') ?></button>
            </form>
        <?php endif; ?>
        <?php if ($isVerifier && $program['status'] === 'menunggu_verifikasi'): ?>
            <form method="post" action="/program/<?= $program['id'] ?>/verifikasi" class="d-inline"><?= Csrf::field() ?>
                <input type="hidden" name="decision" value="approve">
                <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i><?= __t('program.approve') ?></button>
            </form>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalRevision">
                <i class="bi bi-arrow-counterclockwise me-1"></i><?= __t('program.request_revision') ?>
            </button>
        <?php endif; ?>
        <?php if ($isAdmin && $program['status'] === 'terverifikasi'): ?>
            <form method="post" action="/program/<?= $program['id'] ?>/publikasi"><?= Csrf::field() ?>
                <button class="btn btn-gold"><i class="bi bi-megaphone me-1"></i><?= __t('program.publish') ?></button>
            </form>
        <?php endif; ?>
        <a href="/program" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
    </div>
</div>

<?php if ($program['status'] === 'perlu_revisi' && $program['revision_note']): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
        <strong><?= __t('program.revision_note') ?>:</strong> <?= e($program['revision_note']) ?>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-wallet2"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($program['budget_needed']) ?></div><div class="kpi-label"><?= __t('program.budget_needed') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-hand-thumbs-up"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($funding['committed']) ?></div><div class="kpi-label"><?= __t('program.committed') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($funding['realized']) ?></div><div class="kpi-label"><?= __t('program.realized') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-danger"><i class="bi bi-exclamation-diamond"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($funding['gap']) ?></div><div class="kpi-label"><?= __t('program.funding_gap') ?></div></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between small mb-1">
            <span class="fw-semibold"><?= __t('program.funding_progress') ?></span>
            <span><?= $pct ?>%</span>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> <?= __t('common.detail') ?></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th class="ps-3" style="width:40%"><?= __t('program.code') ?></th><td><?= e($program['code']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.status') ?></th><td><?= status_badge($program['status']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.year') ?></th><td><?= e($program['year']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.department') ?></th><td><?= e($program['dept_name']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.field') ?></th><td><?= e($program['field_name']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.district') ?></th><td><?= e($program['district_name'] ?? '-') ?><?= $program['village_name'] ? ' / ' . e($program['village_name']) : '' ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.priority') ?></th><td><?= __t('priority.' . $program['priority_level']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.beneficiary_target') ?></th><td><?= e($program['beneficiary_target'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.beneficiary_count') ?></th><td><?= number_format((int) $program['beneficiary_count'], 0, ',', '.') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.objective') ?></th><td><?= e($program['objective'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.output') ?></th><td><?= e($program['output'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.outcome') ?></th><td><?= e($program['outcome'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.indicator') ?></th><td><?= e($program['indicator'] ?? '-') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-folder2-open me-1"></i> <?= __t('common.documents') ?></div>
            <div class="card-body">
                <?php if ($canManage && in_array($program['status'], ['draft', 'perlu_revisi'], true)): ?>
                <form method="post" action="/program/<?= $program['id'] ?>/dokumen" enctype="multipart/form-data" class="row g-2 mb-3">
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
                                <span class="small"><i class="bi bi-file-earmark me-1 text-muted"></i><?= e($doc['doc_type']) ?> — <?= e($doc['file_name']) ?></span>
                                <a href="<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-hand-thumbs-up me-1"></i> <?= __t('commitment.title') ?></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead><tr><th class="ps-3"><?= __t('commitment.organization') ?></th><th class="text-end"><?= __t('common.amount') ?></th><th><?= __t('common.status') ?></th></tr></thead>
                    <tbody>
                    <?php if ($commitments === []): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3"><?= __t('common.none') ?></td></tr>
                    <?php else: foreach ($commitments as $c): ?>
                        <tr>
                            <td class="ps-3"><?php if ($role !== 'mitra'): ?><a href="/komitmen/<?= $c['id'] ?>"><?= e($c['org_name']) ?></a><?php else: ?><?= e($c['org_name']) ?><?php endif; ?></td>
                            <td class="text-end"><?= format_rupiah($c['amount']) ?></td>
                            <td><?= status_badge($c['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($isVerifier || $isAdmin): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-stars me-1"></i> Minat Mitra</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead><tr><th class="ps-3"><?= __t('commitment.organization') ?></th><th>Jenis</th><th><?= __t('common.status') ?></th><th><?= __t('common.date') ?></th></tr></thead>
                    <tbody>
                    <?php if ($interests === []): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3"><?= __t('common.none') ?></td></tr>
                    <?php else: foreach ($interests as $i): ?>
                        <tr>
                            <td class="ps-3"><?= e($i['org_name']) ?></td>
                            <td><?= e(str_replace('_', ' ', $i['interest_type'])) ?></td>
                            <td><?= status_badge($i['status']) ?></td>
                            <td><?= format_date($i['created_at']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isVerifier): ?>
<div class="modal fade" id="modalRevision" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="/program/<?= $program['id'] ?>/verifikasi">
            <?= Csrf::field() ?>
            <input type="hidden" name="decision" value="revision">
            <div class="modal-header">
                <h5 class="modal-title"><?= __t('program.request_revision') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label required"><?= __t('program.revision_note') ?></label>
                <textarea class="form-control" name="note" rows="4" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __t('common.cancel') ?></button>
                <button class="btn btn-warning" type="submit"><?= __t('common.send') ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

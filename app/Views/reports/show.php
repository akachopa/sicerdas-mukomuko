<?php
use App\Core\Auth;
use App\Core\Csrf;

$role = Auth::role();
$isVerifier = in_array($role, ['super_admin', 'admin_bapperida', 'verifikator'], true);
$canEdit = in_array($role, ['super_admin', 'admin_bapperida', 'mitra'], true)
    && in_array($report['status'], ['draft', 'perlu_perbaikan'], true);
$totalPlanned = array_sum(array_map(fn($i) => (float) $i['planned_amount'], $items));
$totalRealized = array_sum(array_map(fn($i) => (float) $i['realized_amount'], $items));
$totalBeneficiaries = array_sum(array_map(fn($i) => (int) $i['beneficiary_count'], $items));
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= e($report['number']) ?> <?= status_badge($report['status']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/laporan"><?= __t('menu.reports') ?></a></li>
                <li class="breadcrumb-item active"><?= e($report['number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($canEdit): ?>
            <a href="/laporan/<?= $report['id'] ?>/ubah" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i><?= __t('common.edit') ?></a>
            <form method="post" action="/laporan/<?= $report['id'] ?>/kirim"><?= Csrf::field() ?>
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i>
                    <?= $report['status'] === 'perlu_perbaikan' ? __t('report.resubmit') : __t('report.submit') ?>
                </button>
            </form>
        <?php endif; ?>
        <?php if ($isVerifier && in_array($report['status'], ['dikirim', 'revisi_dikirim', 'sedang_diperiksa'], true)): ?>
            <form method="post" action="/laporan/<?= $report['id'] ?>/verifikasi"><?= Csrf::field() ?>
                <input type="hidden" name="decision" value="approve">
                <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i><?= __t('report.approve') ?></button>
            </form>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalRevision"><i class="bi bi-arrow-counterclockwise me-1"></i><?= __t('report.request_revision') ?></button>
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalReject"><i class="bi bi-x-lg me-1"></i><?= __t('report.reject') ?></button>
        <?php endif; ?>
        <a href="/laporan/<?= $report['id'] ?>/cetak" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i><?= __t('common.export_pdf') ?></a>
        <a href="/laporan" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-wallet2"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($totalPlanned) ?></div><div class="kpi-label"><?= __t('report.planned') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($totalRealized) ?></div><div class="kpi-label"><?= __t('report.realized') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-people"></i></div>
            <div><div class="kpi-value"><?= number_format($totalBeneficiaries, 0, ',', '.') ?></div><div class="kpi-label"><?= __t('dash.beneficiaries') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-collection"></i></div>
            <div><div class="kpi-value"><?= count($items) ?></div><div class="kpi-label"><?= __t('report.activities') ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-list-task me-1"></i> <?= __t('report.activities') ?></div>
            <div class="card-body">
                <?php foreach ($items as $idx => $item): ?>
                    <div class="border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <h6 class="fw-bold mb-1"><?= ($idx + 1) . '. ' . e($item['activity_name']) ?></h6>
                            <span class="badge text-bg-light border"><?= e($item['field_name'] ?? '-') ?></span>
                        </div>
                        <div class="row small g-2">
                            <div class="col-md-4"><span class="text-muted"><?= __t('common.location') ?>:</span> <?= e($item['district_name'] ?? '-') ?><?= $item['location_detail'] ? ', ' . e($item['location_detail']) : '' ?></div>
                            <div class="col-md-4"><span class="text-muted"><?= __t('report.planned') ?>:</span> <?= format_rupiah($item['planned_amount']) ?></div>
                            <div class="col-md-4"><span class="text-muted"><?= __t('report.realized') ?>:</span> <strong><?= format_rupiah($item['realized_amount']) ?></strong></div>
                            <div class="col-md-4"><span class="text-muted"><?= __t('commitment.funding_source') ?>:</span> <?= e($item['funding_source'] ?? '-') ?></div>
                            <div class="col-md-4"><span class="text-muted"><?= __t('commitment.contribution_type') ?>:</span> <?= e($item['contribution_type'] ?? '-') ?></div>
                            <div class="col-md-4"><span class="text-muted"><?= __t('dash.beneficiaries') ?>:</span> <?= number_format((int) $item['beneficiary_count'], 0, ',', '.') ?> <?= e($item['beneficiary_type'] ?? '') ?></div>
                            <div class="col-12"><span class="text-muted"><?= __t('report.benefit') ?>:</span> <?= e($item['benefit'] ?? '-') ?></div>
                            <?php if ($item['obstacles']): ?>
                                <div class="col-12"><span class="text-muted"><?= __t('report.obstacles') ?>:</span> <?= e($item['obstacles']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-chat-left-text me-1"></i> <?= __t('report.verification_notes') ?></div>
            <div class="card-body">
                <?php if ($notes === []): ?>
                    <p class="text-muted small mb-0"><?= __t('common.none') ?></p>
                <?php else: foreach ($notes as $n): ?>
                    <div class="d-flex gap-2 mb-3">
                        <i class="bi bi-person-circle text-muted fs-4"></i>
                        <div>
                            <div class="small">
                                <strong><?= e($n['full_name']) ?></strong>
                                <span class="badge text-bg-<?= match ($n['note_type']) { 'persetujuan' => 'success', 'revisi' => 'warning', 'penolakan' => 'danger', default => 'secondary' } ?> ms-1"><?= e($n['note_type']) ?></span>
                                <span class="text-muted ms-1"><?= format_date($n['created_at']) ?></span>
                            </div>
                            <div class="small"><?= e($n['note']) ?></div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> <?= __t('common.detail') ?></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th class="ps-3" style="width:48%"><?= __t('report.organization') ?></th><td><?= e($report['org_name']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.year') ?></th><td><?= e($report['year']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.period') ?></th><td><?= e($report['period']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('report.responsible') ?></th><td><?= e($report['responsible_name'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('report.reg_number') ?></th><td><?= e($report['registration_number'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3">Dikirim</th><td><?= $report['submitted_at'] ? format_date($report['submitted_at']) : '-' ?></td></tr>
                    <tr><th class="ps-3"><?= __t('menu.verification') ?></th><td><?= $report['verified_at'] ? format_date($report['verified_at']) . ' — ' . e($report['verifier_name']) : '-' ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-paperclip me-1"></i> <?= __t('report.attachments') ?></div>
            <div class="card-body">
                <?php if ($canEdit || (in_array($role, ['super_admin', 'admin_bapperida', 'mitra'], true) && !in_array($report['status'], ['dikunci', 'terverifikasi', 'selesai'], true))): ?>
                <form method="post" action="/laporan/<?= $report['id'] ?>/dokumen" enctype="multipart/form-data" class="mb-3">
                    <?= Csrf::field() ?>
                    <div class="mb-2"><input class="form-control form-control-sm" name="doc_type" placeholder="<?= __t('upload.doc_type') ?>" required></div>
                    <div class="input-group input-group-sm">
                        <input class="form-control" type="file" name="document" required>
                        <button class="btn btn-primary"><?= __t('common.upload') ?></button>
                    </div>
                </form>
                <?php endif; ?>
                <?php if ($documents === []): ?>
                    <p class="text-muted small mb-0"><?= __t('common.none') ?></p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="small"><i class="bi bi-file-earmark me-1 text-muted"></i><?= e($doc['doc_type']) ?></span>
                                <a href="<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($isVerifier): ?>
<div class="modal fade" id="modalRevision" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="/laporan/<?= $report['id'] ?>/verifikasi">
            <?= Csrf::field() ?>
            <input type="hidden" name="decision" value="revision">
            <div class="modal-header"><h5 class="modal-title"><?= __t('report.request_revision') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label required"><?= __t('common.notes') ?></label>
                <textarea class="form-control" name="note" rows="4" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __t('common.cancel') ?></button>
                <button class="btn btn-warning" type="submit"><?= __t('common.send') ?></button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="/laporan/<?= $report['id'] ?>/verifikasi">
            <?= Csrf::field() ?>
            <input type="hidden" name="decision" value="reject">
            <div class="modal-header"><h5 class="modal-title"><?= __t('report.reject') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label required"><?= __t('common.notes') ?></label>
                <textarea class="form-control" name="note" rows="4" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __t('common.cancel') ?></button>
                <button class="btn btn-danger" type="submit"><?= __t('common.send') ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

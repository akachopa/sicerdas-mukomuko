<?php
use App\Core\Auth;
use App\Core\Csrf;

$isAdmin = Auth::isAdmin();
$realizedTotal = array_sum(array_map(
    fn($r) => $r['status'] === 'terverifikasi' ? (float) $r['amount'] : 0,
    $realizations
));
$pct = (float) $commitment['amount'] > 0 ? min(100, round($realizedTotal / (float) $commitment['amount'] * 100)) : 0;
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= e($commitment['number']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/komitmen"><?= __t('menu.commitments') ?></a></li>
                <li class="breadcrumb-item active"><?= e($commitment['number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if ($isAdmin): ?>
            <?php if (in_array($commitment['status'], ['diajukan', 'dalam_pembahasan'], true)): ?>
                <form method="post" action="/komitmen/<?= $commitment['id'] ?>/status"><?= Csrf::field() ?>
                    <input type="hidden" name="status" value="disetujui">
                    <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i><?= __t('commitment.approve') ?></button>
                </form>
            <?php endif; ?>
            <?php if ($commitment['status'] === 'disetujui'): ?>
                <form method="post" action="/komitmen/<?= $commitment['id'] ?>/status"><?= Csrf::field() ?>
                    <input type="hidden" name="status" value="aktif">
                    <button class="btn btn-primary"><i class="bi bi-play me-1"></i><?= __t('status.aktif') ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <a href="/komitmen" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> <?= __t('common.detail') ?></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th class="ps-3" style="width:45%"><?= __t('common.status') ?></th><td><?= status_badge($commitment['status']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.organization') ?></th><td><?= e($commitment['org_name']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.program') ?></th><td><a href="/program/<?= $commitment['program_id'] ?>"><?= e($commitment['program_name']) ?></a></td></tr>
                    <tr><th class="ps-3"><?= __t('common.year') ?></th><td><?= e($commitment['year']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.amount') ?></th><td class="fw-bold"><?= format_rupiah($commitment['amount']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('program.realized') ?></th><td><?= format_rupiah($realizedTotal) ?> (<?= $pct ?>%)</td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.contribution_type') ?></th><td><?= e($commitment['contribution'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.funding_source') ?></th><td><?= e($commitment['funding_source'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.mou_number') ?></th><td><?= e($commitment['mou_number'] ?? '-') ?></td></tr>
                    <tr><th class="ps-3"><?= __t('commitment.mou_date') ?></th><td><?= format_date($commitment['mou_date']) ?></td></tr>
                    <tr><th class="ps-3"><?= __t('common.notes') ?></th><td><?= e($commitment['notes'] ?? '-') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cash-coin me-1"></i> <?= __t('realization.title') ?></span>
                <?php if (in_array($commitment['status'], ['disetujui', 'aktif', 'direalisasikan_sebagian'], true)): ?>
                    <a href="/realisasi/tambah?commitment=<?= $commitment['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('realization.add') ?></a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-3"><?= __t('realization.number') ?></th>
                        <th><?= __t('common.date') ?></th>
                        <th><?= __t('realization.stage') ?></th>
                        <th class="text-end"><?= __t('common.amount') ?></th>
                        <th><?= __t('common.status') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($realizations === []): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3"><?= __t('common.none') ?></td></tr>
                    <?php else: foreach ($realizations as $r): ?>
                        <tr>
                            <td class="ps-3"><?= e($r['number']) ?></td>
                            <td><?= format_date($r['realization_date']) ?></td>
                            <td><?= e($r['stage'] ?? '-') ?></td>
                            <td class="text-end"><?= format_rupiah($r['amount']) ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

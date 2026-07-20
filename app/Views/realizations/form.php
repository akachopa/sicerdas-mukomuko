<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="page-title"><?= e($title) ?></h2>
    <a href="/realisasi" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<div class="card" style="max-width: 760px;">
    <div class="card-body">
        <form method="post" action="/realisasi/simpan" enctype="multipart/form-data" class="row g-3">
            <?= Csrf::field() ?>
            <div class="col-12">
                <label class="form-label required"><?= __t('commitment.number') ?></label>
                <select class="form-select" name="commitment_id" id="selCommitment" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($commitments as $c): $remaining = max(0, (float) $c['amount'] - (float) $c['realized']); ?>
                        <option value="<?= $c['id'] ?>" data-remaining="<?= $remaining ?>" <?= $preselect == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['number'] . ' — ' . $c['org_name'] . ' — ' . $c['program_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted" id="remainingInfo"></small>
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('realization.date') ?></label>
                <input type="date" class="form-control" name="realization_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('realization.stage') ?></label>
                <input class="form-control" name="stage" placeholder="Tahap Pertama">
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('common.amount') ?> (Rp)</label>
                <input class="form-control" name="amount" data-money required>
            </div>
            <div class="col-md-8">
                <label class="form-label"><?= __t('common.description') ?></label>
                <textarea class="form-control" name="description" rows="2"></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('program.beneficiary_count') ?></label>
                <input type="number" class="form-control" name="beneficiary_count" min="0" value="0">
            </div>
            <div class="col-12">
                <label class="form-label"><?= __t('realization.evidence') ?> <small class="text-muted">(<?= __t('common.optional') ?>)</small></label>
                <input type="file" class="form-control" name="evidence">
            </div>
            <div class="col-12">
                <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const $sel = $('#selCommitment'), $info = $('#remainingInfo');
    function showRemaining() {
        const rem = $sel.find(':selected').data('remaining');
        $info.text(rem !== undefined && $sel.val() ? 'Sisa yang dapat direalisasikan: Rp ' + Number(rem).toLocaleString('id-ID') : '');
    }
    $sel.on('change', showRemaining);
    showRemaining();
});
</script>

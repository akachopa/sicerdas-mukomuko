<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="page-title"><?= e($title) ?></h2>
    <a href="/komitmen" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<div class="card" style="max-width: 860px;">
    <div class="card-body">
        <form method="post" action="<?= $commitment ? '/komitmen/' . $commitment['id'] . '/update' : '/komitmen/simpan' ?>" class="row g-3">
            <?= Csrf::field() ?>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('commitment.organization') ?></label>
                <select class="form-select" name="organization_id" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['organizations'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($commitment['organization_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('commitment.program') ?></label>
                <select class="form-select" name="program_id" id="selProgram" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['programs'] as $r): $gap = max(0, (float) $r['budget_needed'] - (float) $r['committed']); ?>
                        <option value="<?= $r['id'] ?>" data-gap="<?= $gap ?>" <?= ($commitment['program_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                            <?= e($r['code'] . ' — ' . $r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted" id="gapInfo"></small>
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('common.year') ?></label>
                <select class="form-select" name="fiscal_year_id" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['fiscal_years'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($commitment['fiscal_year_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['year']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('commitment.amount') ?> (Rp)</label>
                <input class="form-control" name="amount" data-money required
                       value="<?= $commitment ? number_format((float) $commitment['amount'], 0, ',', '.') : '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('commitment.contribution_type') ?></label>
                <select class="form-select" name="contribution_type_id">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['contribution_types'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($commitment['contribution_type_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('commitment.funding_source') ?></label>
                <select class="form-select" name="funding_source_id">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['funding_sources'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($commitment['funding_source_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('commitment.mou_number') ?></label>
                <input class="form-control" name="mou_number" value="<?= e($commitment['mou_number'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('commitment.mou_date') ?></label>
                <input type="date" class="form-control" name="mou_date" value="<?= e($commitment['mou_date'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label"><?= __t('common.notes') ?></label>
                <textarea class="form-control" name="notes" rows="2"><?= e($commitment['notes'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const $program = $('#selProgram'), $info = $('#gapInfo');
    function showGap() {
        const gap = $program.find(':selected').data('gap');
        $info.text(gap !== undefined && $program.val() ? '<?= __t('program.funding_gap') ?>: Rp ' + Number(gap).toLocaleString('id-ID') : '');
    }
    $program.on('change', showGap);
    showGap();
});
</script>

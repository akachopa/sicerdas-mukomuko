<?php use App\Core\Csrf; use App\Core\Auth; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="page-title"><?= e($title) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/program"><?= __t('menu.programs') ?></a></li>
                <li class="breadcrumb-item active"><?= e($title) ?></li>
            </ol>
        </nav>
    </div>
    <a href="/program" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<?php if ($program && $program['status'] === 'perlu_revisi' && $program['revision_note']): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
        <strong><?= __t('program.revision_note') ?>:</strong> <?= e($program['revision_note']) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $program ? '/program/' . $program['id'] . '/update' : '/program/simpan' ?>">
    <?= Csrf::field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-kanban me-1"></i> <?= __t('program.title') ?></div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label required"><?= __t('program.name') ?></label>
                        <input class="form-control" name="name" required value="<?= e($program['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required"><?= __t('common.year') ?></label>
                        <select class="form-select" name="fiscal_year_id" required>
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['fiscal_years'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($program['fiscal_year_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['year']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required"><?= __t('program.department') ?></label>
                        <select class="form-select" name="department_id" <?= Auth::role() === 'opd' ? 'disabled' : 'required' ?>>
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['departments'] as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                    <?= (($program['department_id'] ?? null) ?: (Auth::role() === 'opd' ? Auth::user()['department_id'] : null)) == $r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required"><?= __t('program.field') ?></label>
                        <select class="form-select" name="program_field_id" required>
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['fields'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($program['program_field_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('common.description') ?></label>
                        <textarea class="form-control" name="description" rows="2"><?= e($program['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('program.background') ?></label>
                        <textarea class="form-control" name="background" rows="2"><?= e($program['background'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('program.objective') ?></label>
                        <textarea class="form-control" name="objective" rows="2"><?= e($program['objective'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required"><?= __t('common.district') ?></label>
                        <select class="form-select" name="district_id" id="selDistrict" required>
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['districts'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($program['district_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('common.village') ?></label>
                        <select class="form-select" name="village_id" id="selVillage" data-selected="<?= e($program['village_id'] ?? '') ?>">
                            <option value=""><?= __t('common.choose') ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('common.location') ?></label>
                        <input class="form-control" name="location_detail" value="<?= e($program['location_detail'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required"><?= __t('program.beneficiary_target') ?></label>
                        <input class="form-control" name="beneficiary_target" required value="<?= e($program['beneficiary_target'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('program.beneficiary_count') ?></label>
                        <input type="number" class="form-control" name="beneficiary_count" min="0" value="<?= e($program['beneficiary_count'] ?? 0) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required"><?= __t('program.output') ?></label>
                        <textarea class="form-control" name="output" rows="2" required><?= e($program['output'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('program.outcome') ?></label>
                        <textarea class="form-control" name="outcome" rows="2"><?= e($program['outcome'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label required"><?= __t('program.indicator') ?></label>
                        <textarea class="form-control" name="indicator" rows="2" required><?= e($program['indicator'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-cash-stack me-1"></i> Anggaran &amp; Jadwal</div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label required"><?= __t('program.budget_needed') ?> (Rp)</label>
                        <input class="form-control" name="budget_needed" data-money required
                               value="<?= $program ? number_format((float) $program['budget_needed'], 0, ',', '.') : '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('program.priority') ?></label>
                        <select class="form-select" name="priority_level">
                            <?php foreach (['rendah', 'sedang', 'tinggi', 'mendesak'] as $p): ?>
                                <option value="<?= $p ?>" <?= ($program['priority_level'] ?? 'sedang') === $p ? 'selected' : '' ?>><?= __t('priority.' . $p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="<?= e($program['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Selesai</label>
                        <input type="date" class="form-control" name="end_date" value="<?= e($program['end_date'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2 mb-2" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
        </div>
    </div>
</form>

<script>
const VILLAGES = <?= json_encode($refs['villages'], JSON_UNESCAPED_UNICODE) ?>;
$(function () {
    const $district = $('#selDistrict'), $village = $('#selVillage');
    function fillVillages() {
        const did = $district.val(), sel = $village.data('selected');
        $village.find('option:not(:first)').remove();
        VILLAGES.filter(v => String(v.district_id) === String(did)).forEach(v => {
            $village.append($('<option>').val(v.id).text(v.name).prop('selected', String(v.id) === String(sel)));
        });
    }
    $district.on('change', fillVillages);
    fillVillages();
});
</script>

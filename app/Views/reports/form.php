<?php use App\Core\Csrf; use App\Core\Auth; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="page-title"><?= e($title) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/laporan"><?= __t('menu.reports') ?></a></li>
                <li class="breadcrumb-item active"><?= e($title) ?></li>
            </ol>
        </nav>
    </div>
    <a href="/laporan" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<form method="post" action="<?= $report ? '/laporan/' . $report['id'] . '/update' : '/laporan/simpan' ?>">
    <?= Csrf::field() ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-file-earmark-text me-1"></i> <?= __t('report.title') ?></div>
        <div class="card-body row g-3">
            <?php if (Auth::role() !== 'mitra'): ?>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('report.organization') ?></label>
                <select class="form-select" name="organization_id" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['organizations'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($report['organization_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('common.year') ?></label>
                <select class="form-select" name="fiscal_year_id" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['fiscal_years'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($report['fiscal_year_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['year']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('common.period') ?></label>
                <select class="form-select" name="reporting_period_id" required>
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['periods'] as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($report['reporting_period_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label required"><?= __t('report.responsible') ?></label>
                <input class="form-control" name="responsible_name" required value="<?= e($report['responsible_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('report.responsible_position') ?></label>
                <input class="form-control" name="responsible_position" value="<?= e($report['responsible_position'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><?= __t('common.notes') ?></label>
                <input class="form-control" name="notes" value="<?= e($report['notes'] ?? '') ?>">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-task me-1"></i> <?= __t('report.activities') ?></span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem">
                <i class="bi bi-plus-lg me-1"></i><?= __t('report.add_activity') ?>
            </button>
        </div>
        <div class="card-body" id="itemsContainer"></div>
    </div>

    <button class="btn btn-primary px-4 py-2" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
</form>

<template id="itemTemplate">
    <div class="border rounded-3 p-3 mb-3 report-item position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 btn-remove-item"></button>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label required"><?= __t('report.activity_name') ?></label>
                <input class="form-control form-control-sm" name="item_name[]" required>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('program.field') ?></label>
                <select class="form-select form-select-sm" name="item_field[]">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['fields'] as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('common.district') ?></label>
                <select class="form-select form-select-sm" name="item_district[]">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['districts'] as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('common.location') ?></label>
                <input class="form-control form-control-sm" name="item_location[]">
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('report.planned') ?> (Rp)</label>
                <input class="form-control form-control-sm" name="item_planned[]" data-money>
            </div>
            <div class="col-md-3">
                <label class="form-label required"><?= __t('report.realized') ?> (Rp)</label>
                <input class="form-control form-control-sm" name="item_realized[]" data-money required>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('commitment.funding_source') ?></label>
                <select class="form-select form-select-sm" name="item_source[]">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['funding_sources'] as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('commitment.contribution_type') ?></label>
                <select class="form-select form-select-sm" name="item_contribution[]">
                    <option value=""><?= __t('common.choose') ?></option>
                    <?php foreach ($refs['contribution_types'] as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __t('program.beneficiary_count') ?></label>
                <input type="number" class="form-control form-control-sm" name="item_beneficiaries[]" min="0" value="0">
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('report.beneficiary_type') ?></label>
                <input class="form-control form-control-sm" name="item_beneficiary_type[]">
            </div>
            <div class="col-md-6">
                <label class="form-label required"><?= __t('report.benefit') ?></label>
                <textarea class="form-control form-control-sm" name="item_benefit[]" rows="1" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label"><?= __t('report.obstacles') ?></label>
                <textarea class="form-control form-control-sm" name="item_obstacles[]" rows="1"></textarea>
            </div>
        </div>
    </div>
</template>

<script>
const EXISTING_ITEMS = <?= json_encode(array_map(fn($i) => [
    'name' => $i['activity_name'],
    'field' => $i['program_field_id'],
    'district' => $i['district_id'],
    'location' => $i['location_detail'],
    'planned' => (float) $i['planned_amount'],
    'realized' => (float) $i['realized_amount'],
    'source' => $i['funding_source_id'],
    'contribution' => $i['contribution_type_id'],
    'beneficiaries' => (int) $i['beneficiary_count'],
    'beneficiary_type' => $i['beneficiary_type'],
    'benefit' => $i['benefit'],
    'obstacles' => $i['obstacles'],
], $items), JSON_UNESCAPED_UNICODE) ?>;

$(function () {
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemTemplate');

    function addItem(data) {
        const node = template.content.cloneNode(true);
        const root = node.querySelector('.report-item');
        if (data) {
            root.querySelector('[name="item_name[]"]').value = data.name || '';
            root.querySelector('[name="item_field[]"]').value = data.field || '';
            root.querySelector('[name="item_district[]"]').value = data.district || '';
            root.querySelector('[name="item_location[]"]').value = data.location || '';
            root.querySelector('[name="item_planned[]"]').value = data.planned ? Number(data.planned).toLocaleString('id-ID') : '';
            root.querySelector('[name="item_realized[]"]').value = data.realized ? Number(data.realized).toLocaleString('id-ID') : '';
            root.querySelector('[name="item_source[]"]').value = data.source || '';
            root.querySelector('[name="item_contribution[]"]').value = data.contribution || '';
            root.querySelector('[name="item_beneficiaries[]"]').value = data.beneficiaries || 0;
            root.querySelector('[name="item_beneficiary_type[]"]').value = data.beneficiary_type || '';
            root.querySelector('[name="item_benefit[]"]').value = data.benefit || '';
            root.querySelector('[name="item_obstacles[]"]').value = data.obstacles || '';
        }
        container.appendChild(node);
    }

    document.getElementById('btnAddItem').addEventListener('click', () => addItem(null));
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-item');
        if (btn && container.querySelectorAll('.report-item').length > 1) {
            btn.closest('.report-item').remove();
        }
    });

    if (EXISTING_ITEMS.length) {
        EXISTING_ITEMS.forEach(addItem);
    } else {
        addItem(null);
    }
});
</script>

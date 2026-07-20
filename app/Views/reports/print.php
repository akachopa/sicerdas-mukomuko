<?php
$totalRealized = array_sum(array_map(fn($i) => (float) $i['realized_amount'], $items));
$totalBeneficiaries = array_sum(array_map(fn($i) => (int) $i['beneficiary_count'], $items));
?>
<div class="kop d-flex align-items-center gap-3">
    <span class="d-inline-flex align-items-center justify-content-center"
          style="width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,#c9a227,#e6c65a);color:#0f2a52;font-weight:800;font-size:1.3rem;">SC</span>
    <div>
        <h1 class="h5 fw-bold mb-0" style="color:#0f2a52">SICERDAS MUKOMUKO</h1>
        <div class="small text-muted"><?= __t('app.tagline') ?></div>
        <div class="small text-muted">Pemerintah Kabupaten Mukomuko</div>
    </div>
</div>

<h2 class="h5 text-center fw-bold mb-1"><?= __t('report.title') ?></h2>
<p class="text-center text-muted small mb-4"><?= e($report['number']) ?><?= $report['registration_number'] ? ' — ' . __t('report.reg_number') . ': ' . e($report['registration_number']) : '' ?></p>

<table class="table table-sm table-bordered mb-4">
    <tbody>
    <tr><th style="width:30%"><?= __t('report.organization') ?></th><td><?= e($report['org_name']) ?></td></tr>
    <tr><th><?= __t('common.year') ?> / <?= __t('common.period') ?></th><td><?= e($report['year'] . ' / ' . $report['period']) ?></td></tr>
    <tr><th><?= __t('report.responsible') ?></th><td><?= e($report['responsible_name'] ?? '-') ?><?= $report['responsible_position'] ? ' (' . e($report['responsible_position']) . ')' : '' ?></td></tr>
    <tr><th><?= __t('common.status') ?></th><td><?= __t('status.' . $report['status']) ?></td></tr>
    <tr><th>Dikirim</th><td><?= $report['submitted_at'] ? format_date($report['submitted_at']) : '-' ?></td></tr>
    </tbody>
</table>

<h3 class="h6 fw-bold"><?= __t('report.activities') ?></h3>
<table class="table table-sm table-bordered">
    <thead class="table-light">
    <tr>
        <th><?= __t('common.no') ?></th>
        <th><?= __t('report.activity_name') ?></th>
        <th><?= __t('program.field') ?></th>
        <th><?= __t('common.location') ?></th>
        <th class="text-end"><?= __t('report.realized') ?></th>
        <th class="text-end"><?= __t('dash.beneficiaries') ?></th>
        <th><?= __t('report.benefit') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $idx => $item): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><?= e($item['activity_name']) ?></td>
            <td><?= e($item['field_name'] ?? '-') ?></td>
            <td><?= e($item['district_name'] ?? '-') ?></td>
            <td class="text-end"><?= format_rupiah($item['realized_amount']) ?></td>
            <td class="text-end"><?= number_format((int) $item['beneficiary_count'], 0, ',', '.') ?></td>
            <td><?= e($item['benefit'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light fw-bold">
    <tr>
        <td colspan="4" class="text-end"><?= __t('common.total') ?></td>
        <td class="text-end"><?= format_rupiah($totalRealized) ?></td>
        <td class="text-end"><?= number_format($totalBeneficiaries, 0, ',', '.') ?></td>
        <td></td>
    </tr>
    </tfoot>
</table>

<div class="row mt-5 no-print-margin">
    <div class="col-6"></div>
    <div class="col-6 text-center small">
        <div>Mukomuko, <?= format_date(date('Y-m-d')) ?></div>
        <div class="mb-5"><?= __t('report.responsible') ?>,</div>
        <div class="fw-bold text-decoration-underline"><?= e($report['responsible_name'] ?? '________________') ?></div>
        <div><?= e($report['responsible_position'] ?? '') ?></div>
    </div>
</div>

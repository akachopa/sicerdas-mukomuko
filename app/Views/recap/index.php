<?php
$qs = http_build_query(array_filter([
    'type' => $filters['type'],
    'year' => $filters['year'] ?: null,
    'period' => $filters['period'] ?: null,
]));
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('recap.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.recap') ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-success" href="/rekap/excel?<?= $qs ?>"><i class="bi bi-file-earmark-excel me-1"></i><?= __t('common.export_excel') ?></a>
        <a class="btn btn-danger" href="/rekap/cetak?<?= $qs ?>" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i><?= __t('common.export_pdf') ?></a>
    </div>
</div>

<form class="card mb-3" method="get">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label"><?= __t('recap.title') ?></label>
            <select class="form-select" name="type">
                <option value="perusahaan" <?= $filters['type'] === 'perusahaan' ? 'selected' : '' ?>><?= __t('recap.by_org') ?></option>
                <option value="bidang" <?= $filters['type'] === 'bidang' ? 'selected' : '' ?>><?= __t('recap.by_field') ?></option>
                <option value="kecamatan" <?= $filters['type'] === 'kecamatan' ? 'selected' : '' ?>><?= __t('recap.by_district') ?></option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label"><?= __t('common.year') ?></label>
            <select class="form-select" name="year">
                <option value="0"><?= __t('common.all') ?></option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y['id'] ?>" <?= $filters['year'] == $y['id'] ? 'selected' : '' ?>><?= e($y['year']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label"><?= __t('common.period') ?></label>
            <select class="form-select" name="period">
                <option value="0"><?= __t('common.all') ?></option>
                <?php foreach ($periods as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filters['period'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i><?= __t('common.apply') ?></button>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header"><?= e($data['title']) ?></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <?php foreach ($data['headers'] as $h): ?><th class="<?= str_contains($h, 'Rp') || in_array($h, [__t('report.planned'), __t('report.realized')]) ? 'text-end' : '' ?>"><?= e($h) ?></th><?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($data['rows'] === []): ?>
                <tr><td colspan="<?= count($data['headers']) ?>" class="text-center text-muted py-4"><?= __t('common.none') ?></td></tr>
            <?php else: foreach ($data['rows'] as $row): ?>
                <tr>
                    <?php foreach ($row as $i => $cell): ?>
                        <td class="<?= $i >= count($row) - 3 && $i < count($row) ? 'text-end' : '' ?>"><?= e((string) $cell) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
